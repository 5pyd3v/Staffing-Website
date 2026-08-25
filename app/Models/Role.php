<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Role
{
    public static function idForSlug(string $slug): ?int
    {
        static $cache = [];

        if (array_key_exists($slug, $cache)) {
            return $cache[$slug];
        }

        $stmt = Database::connection()->prepare('SELECT id FROM roles WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $id = $stmt->fetchColumn();

        return $cache[$slug] = ($id === false ? null : (int) $id);
    }
}
