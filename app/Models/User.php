<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use App\Helpers\Uuid;

final class User extends Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::findBy('email', mb_strtolower(trim($email)));
    }

    public static function findByEmailWithRole(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT users.*, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.email = :email AND users.deleted_at IS NULL'
        );
        $stmt->execute(['email' => mb_strtolower(trim($email))]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function findWithRole(int $id): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT users.*, roles.slug AS role_slug
             FROM users
             INNER JOIN roles ON roles.id = users.role_id
             WHERE users.id = :id AND users.deleted_at IS NULL'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public static function createUser(string $email, string $password, int $roleId, string $status = 'active'): string|int
    {
        return self::create([
            'uuid' => Uuid::v4(),
            'email' => mb_strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role_id' => $roleId,
            'status' => $status,
        ]);
    }

    public static function roleSlug(int $roleId): ?string
    {
        $stmt = Database::connection()->prepare('SELECT slug FROM roles WHERE id = :id');
        $stmt->execute(['id' => $roleId]);
        $slug = $stmt->fetchColumn();

        return $slug === false ? null : $slug;
    }
}
