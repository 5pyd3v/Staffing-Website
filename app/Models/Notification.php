<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Notification extends Model
{
    protected static string $table = 'notifications';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function recent(int $limit = 50): array
    {
        $stmt = self::db()->prepare('SELECT * FROM notifications ORDER BY created_at DESC LIMIT ' . $limit);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function countSince(string $sinceExpr = '1 DAY'): int
    {
        $stmt = self::db()->prepare(
            "SELECT COUNT(*) FROM notifications WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$sinceExpr})"
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
