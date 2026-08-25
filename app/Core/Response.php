<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $path);
        exit;
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function abort(int $status, string $message = ''): never
    {
        http_response_code($status);
        $file = VIEW_PATH . "/errors/{$status}.php";
        if (is_file($file)) {
            require $file;
        } else {
            echo htmlspecialchars($message ?: 'Error', ENT_QUOTES);
        }
        exit;
    }
}
