<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AppSetting
{
    public static function all(): array
    {
        $stmt = Database::connection()->query('SELECT setting_key, value FROM app_settings ORDER BY setting_key ASC');
        return $stmt->fetchAll();
    }

    public static function set(string $key, ?string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO app_settings (setting_key, value) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE value = VALUES(value)'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
    }
}
