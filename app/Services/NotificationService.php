<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\View;
use App\Helpers\Uuid;
use App\Models\Notification;

/**
 * Email-ready notification pipeline. Templates render to full HTML (see
 * views/emails/*.php), get queued in the `notifications` table, and are
 * "delivered" by the configured driver:
 *
 *   - 'log'  (default, dev): appended to storage/logs/mail.log and marked
 *     'sent' immediately — there is no SMTP server in this environment, so
 *     this is the honest stand-in. Swapping in a real transport (PHPMailer,
 *     Symfony Mailer, an API-based provider) only requires implementing
 *     `deliver()` differently; every call site and template is unaffected.
 *   - anything else: left 'pending' for a real worker/cron to pick up.
 */
final class NotificationService
{
    public static function render(string $template, array $data): string
    {
        return View::render('emails/' . $template, $data, 'emails/layout');
    }

    public static function queueUserNotification(
        int $userId,
        string $type,
        string $subject,
        string $bodyHtml,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): void {
        $stmt = Database::connection()->prepare('SELECT email FROM users WHERE id = :id');
        $stmt->execute(['id' => $userId]);
        $email = $stmt->fetchColumn();

        self::queue($userId, $email !== false ? $email : null, $type, $subject, $bodyHtml, $relatedType, $relatedId);
    }

    /**
     * Fans a notification out to every active admin/super_admin — this is
     * the "admin alerts" half of the architecture (new candidate, new
     * staffing request, new application, etc.).
     */
    public static function queueAdminAlert(
        string $type,
        string $subject,
        string $bodyHtml,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): void {
        $stmt = Database::connection()->prepare(
            "SELECT u.id, u.email FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE r.slug IN ('admin', 'super_admin') AND u.status = 'active' AND u.deleted_at IS NULL"
        );
        $stmt->execute();

        foreach ($stmt->fetchAll() as $admin) {
            self::queue((int) $admin['id'], $admin['email'], $type, $subject, $bodyHtml, $relatedType, $relatedId);
        }
    }

    private static function queue(
        ?int $userId,
        ?string $recipientEmail,
        string $type,
        string $subject,
        string $bodyHtml,
        ?string $relatedType,
        ?int $relatedId
    ): void {
        $id = Notification::create([
            'uuid' => Uuid::v4(),
            'user_id' => $userId,
            'recipient_email' => $recipientEmail,
            'channel' => 'email',
            'type' => $type,
            'subject' => $subject,
            'body' => $bodyHtml,
            'status' => 'pending',
            'related_entity_type' => $relatedType,
            'related_entity_id' => $relatedId,
        ]);

        self::deliver((int) $id, $recipientEmail, $subject, $bodyHtml);
    }

    private static function deliver(int $notificationId, ?string $recipientEmail, string $subject, string $bodyHtml): void
    {
        $config = require ROOT_PATH . '/config/app.php';
        $driver = $config['mail']['driver'] ?? 'log';

        if ($driver !== 'log' || $recipientEmail === null) {
            return;
        }

        $logPath = STORAGE_PATH . '/logs/mail.log';
        $entry = sprintf(
            "[%s] TO: %s | SUBJECT: %s\n%s\n%s\n\n",
            date('Y-m-d H:i:s'),
            $recipientEmail,
            $subject,
            str_repeat('-', 60),
            trim(html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $bodyHtml)), ENT_QUOTES))
        );

        file_put_contents($logPath, $entry, FILE_APPEND | LOCK_EX);

        Notification::update($notificationId, ['status' => 'sent', 'sent_at' => date('Y-m-d H:i:s')]);
    }
}
