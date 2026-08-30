<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use Throwable;

/**
 * Minimal dependency-free SMTP client (RFC 5321/5322). No PHPMailer/Symfony
 * Mailer — the project intentionally ships with zero Composer dependencies
 * (see ARCHITECTURE.md) and a single outbound EHLO/STARTTLS/AUTH LOGIN/
 * MAIL/RCPT/DATA exchange is small enough to hand-roll rather than pull in
 * a library just for that. Used by NotificationService when MAIL_DRIVER=smtp.
 */
final class SmtpMailer
{
    /**
     * @param array{host:string,port:int,encryption:string,username:string,password:string,timeout:int,from_address:string,from_name:string} $config
     * @return array{ok:bool,error?:string}
     */
    public static function send(array $config, string $toEmail, string $subject, string $bodyHtml): array
    {
        if ($config['host'] === '') {
            return ['ok' => false, 'error' => 'MAIL_HOST is not configured.'];
        }

        $encryption = strtolower($config['encryption']);
        $timeout = max(1, $config['timeout']);
        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $config['host'] . ':' . $config['port'];

        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

        if ($socket === false) {
            return ['ok' => false, 'error' => "Could not connect to {$config['host']}:{$config['port']} ({$errstr})"];
        }

        stream_set_timeout($socket, $timeout);

        try {
            self::readResponse($socket, [220]);
            self::command($socket, 'EHLO ' . self::heloHost(), [250]);

            if ($encryption === 'tls') {
                self::command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('STARTTLS negotiation failed.');
                }
                self::command($socket, 'EHLO ' . self::heloHost(), [250]);
            }

            if ($config['username'] !== '') {
                self::command($socket, 'AUTH LOGIN', [334]);
                self::command($socket, base64_encode($config['username']), [334]);
                self::command($socket, base64_encode($config['password']), [235]);
            }

            self::command($socket, 'MAIL FROM:<' . $config['from_address'] . '>', [250]);
            self::command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
            self::command($socket, 'DATA', [354]);

            $message = self::buildMessage($config['from_name'], $config['from_address'], $toEmail, $subject, $bodyHtml);
            fwrite($socket, $message . "\r\n.\r\n");
            self::readResponse($socket, [250]);

            self::command($socket, 'QUIT', [221]);
        } catch (Throwable $e) {
            fclose($socket);

            return ['ok' => false, 'error' => $e->getMessage()];
        }

        fclose($socket);

        return ['ok' => true];
    }

    private static function heloHost(): string
    {
        $host = gethostname();

        return $host !== false ? $host : 'localhost';
    }

    /** @param resource $socket @param int[] $expectedCodes */
    private static function command($socket, string $line, array $expectedCodes): string
    {
        fwrite($socket, $line . "\r\n");

        return self::readResponse($socket, $expectedCodes);
    }

    /**
     * Reads one full SMTP response (following multi-line "250-...\r\n250 ...\r\n"
     * continuation format) and throws unless the status code is one of $expectedCodes.
     *
     * @param resource $socket @param int[] $expectedCodes
     */
    private static function readResponse($socket, array $expectedCodes): string
    {
        $response = '';

        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code === 0 || !in_array($code, $expectedCodes, true)) {
            $detail = trim($response) !== '' ? trim($response) : 'no response from mail server';
            throw new RuntimeException("SMTP error (expected " . implode('/', $expectedCodes) . "): {$detail}");
        }

        return $response;
    }

    private static function buildMessage(string $fromName, string $fromAddress, string $toEmail, string $subject, string $bodyHtml): string
    {
        $headers = [
            'Date: ' . gmdate('D, d M Y H:i:s O'),
            'From: ' . self::encodeHeaderAddress($fromName, $fromAddress),
            'To: <' . $toEmail . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ];

        // Dot-stuff any body line starting with a lone "." per RFC 5321 §4.5.2,
        // so it isn't mistaken for the end-of-DATA terminator.
        $body = preg_replace('/^\./m', '..', $bodyHtml);

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private static function encodeHeaderAddress(string $name, string $address): string
    {
        if ($name === '') {
            return '<' . $address . '>';
        }

        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $address . '>';
    }
}
