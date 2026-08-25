<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_SECONDS = 900;

    public static function attempt(string $email, string $password, string $ip): array|string
    {
        if (self::isLockedOut($email, $ip)) {
            return 'locked';
        }

        $user = User::findByEmailWithRole($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            self::recordAttempt($email, $ip, false);
            return 'invalid';
        }

        if ($user['status'] !== 'active') {
            self::recordAttempt($email, $ip, false);
            return 'inactive';
        }

        self::recordAttempt($email, $ip, true);
        self::login($user);

        return $user;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role_slug']);
        User::update((int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function user(): ?array
    {
        static $cached = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }

        $resolved = true;
        $id = self::id();
        $cached = $id ? User::findWithRole($id) : null;

        return $cached;
    }

    public static function hasRole(string|array $roles): bool
    {
        $current = self::role();
        if ($current === null) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($current, $roles, true);
    }

    private static function isLockedOut(string $email, string $ip): bool
    {
        $stmt = Database::connection()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (email = :email OR ip_address = :ip)
               AND success = 0
               AND attempted_at > DATE_SUB(NOW(), INTERVAL :seconds SECOND)'
        );
        $stmt->execute(['email' => mb_strtolower(trim($email)), 'ip' => $ip, 'seconds' => self::LOCKOUT_SECONDS]);

        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    private static function recordAttempt(string $email, string $ip, bool $success): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (:email, :ip, :success, NOW())'
        );
        $stmt->execute([
            'email' => mb_strtolower(trim($email)),
            'ip' => $ip,
            'success' => $success ? 1 : 0,
        ]);

        if ($success) {
            $clear = Database::connection()->prepare(
                'DELETE FROM login_attempts WHERE email = :email AND success = 0'
            );
            $clear->execute(['email' => mb_strtolower(trim($email))]);
        }
    }
}
