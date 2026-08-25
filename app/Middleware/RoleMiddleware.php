<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class RoleMiddleware
{
    public function handle(Request $request, ?string $arg): void
    {
        $allowed = $arg ? explode(',', $arg) : [];

        if (!Auth::hasRole($allowed)) {
            Response::abort(403, 'You do not have permission to access this page.');
        }
    }
}
