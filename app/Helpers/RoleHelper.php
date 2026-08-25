<?php

declare(strict_types=1);

namespace App\Helpers;

final class RoleHelper
{
    public static function dashboardPath(?string $role): string
    {
        return match ($role) {
            'super_admin', 'admin' => '/admin/dashboard',
            'employer' => '/employer/dashboard',
            'candidate' => '/candidate/dashboard',
            default => '/',
        };
    }
}
