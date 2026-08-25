<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Helpers\RoleHelper;

final class GuestMiddleware
{
    public function handle(Request $request, ?string $arg): void
    {
        if (Auth::check()) {
            Response::redirect(RoleHelper::dashboardPath(Auth::role()));
        }
    }
}
