<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

final class AuthMiddleware
{
    public function handle(Request $request, ?string $arg): void
    {
        if (!Auth::check()) {
            Session::flash('intended_url', $request->path());
            Session::flash('error', 'Please sign in to continue.');
            Response::redirect('/login');
        }

        // A session can outlive its account (deleted/deactivated mid-session).
        // Treat that as logged out rather than rendering a half-authenticated page.
        if (Auth::user() === null) {
            Auth::logout();
            Session::flash('error', 'Your session is no longer valid. Please sign in again.');
            Response::redirect('/login');
        }
    }
}
