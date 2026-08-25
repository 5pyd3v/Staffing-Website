<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Industry extends Model
{
    protected static string $table = 'industries';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function findBySlug(string $slug): ?array
    {
        return self::findBy('slug', $slug);
    }

    public static function inUseCount(int $id): int
    {
        $db = self::db();
        $stmt = $db->prepare(
            '(SELECT id FROM jobs WHERE industry_id = :id1 AND deleted_at IS NULL LIMIT 1)
             UNION ALL
             (SELECT id FROM companies WHERE industry_id = :id2 AND deleted_at IS NULL LIMIT 1)
             UNION ALL
             (SELECT id FROM staffing_requests WHERE industry_id = :id3 AND deleted_at IS NULL LIMIT 1)'
        );
        $stmt->execute(['id1' => $id, 'id2' => $id, 'id3' => $id]);

        return count($stmt->fetchAll());
    }
}
