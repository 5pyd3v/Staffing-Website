<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Service extends Model
{
    protected static string $table = 'services';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }

    public static function allOrdered(): array
    {
        return self::db()->query('SELECT * FROM services ORDER BY sort_order ASC, name ASC')->fetchAll();
    }
}
