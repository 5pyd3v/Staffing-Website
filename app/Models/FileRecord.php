<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Named FileRecord (not File) to avoid any ambiguity with PHP's SPL file
 * classes; maps to the polymorphic `files` table.
 */
final class FileRecord extends Model
{
    protected static string $table = 'files';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function attach(int $fileId, string $entityType, int $entityId): void
    {
        $stmt = self::db()->prepare(
            'UPDATE files SET entity_type = :entity_type, entity_id = :entity_id WHERE id = :id'
        );
        $stmt->execute(['entity_type' => $entityType, 'entity_id' => $entityId, 'id' => $fileId]);
    }
}
