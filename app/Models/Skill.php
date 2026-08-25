<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Skill extends Model
{
    protected static string $table = 'skills';
    protected static bool $softDeletes = false;
    protected static bool $timestamps = false;

    public static function findOrCreateByName(string $name): array
    {
        $name = trim($name);
        $existing = self::findBy('name', $name);
        if ($existing !== null) {
            return $existing;
        }

        $id = self::create(['name' => $name]);
        return self::find((int) $id);
    }
}
