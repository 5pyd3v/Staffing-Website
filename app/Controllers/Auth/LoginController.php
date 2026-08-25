<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\RoleHelper;

final class LoginController extends Controller
{
    public function show(Request $request): void
    {
        $this->view('auth/login', [
            'title' => 'Sign In',
            'errors' => Session::getFlash('errors', []),
            'error' => Session::getFlash('error'),
        ], 'layouts/auth');
    }

    public function store(Request $request): void
    {
        $this->verifyCsrf($request);

        $validator = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            $this->backWithErrors('/login', $validator, ['email' => $request->input('email')]);
        }

        $result = Auth::attempt(
            (string) $request->input('email'),
            (string) $request->input('password'),
            $request->ip()
        );

        if ($result === 'locked') {
            Session::flash('error', 'Too many failed attempts. Please try again in 15 minutes.');
            $this->redirect('/login');
        }

        if ($result === 'invalid') {
            Session::flash('error', 'Those credentials do not match our records.');
            $this->redirect('/login');
        }

        if ($result === 'inactive') {
            Session::flash('error', 'This account has been deactivated. Contact support for assistance.');
            $this->redirect('/login');
        }

        $intended = Session::getFlash('intended_url');
        $this->redirect($intended ?: RoleHelper::dashboardPath(Auth::role()));
    }

    public function destroy(Request $request): void
    {
        $this->verifyCsrf($request);
        Auth::logout();
        $this->redirect('/login');
    }
}
