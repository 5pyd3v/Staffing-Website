<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        echo View::render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        Response::redirect($path);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function validate(Request $request, array $rules): Validator
    {
        return Validator::make($request->all(), $rules);
    }

    protected function verifyCsrf(Request $request): void
    {
        if (!Csrf::verify($request->csrfToken())) {
            Response::abort(419, 'Your session has expired. Please refresh and try again.');
        }
    }

    protected function backWithErrors(string $path, Validator $validator, array $old = []): never
    {
        Session::flash('errors', $validator->errors());
        Session::setOld($old);
        $this->redirect($path);
    }
}
