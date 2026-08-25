<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $data['csrfField'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();
        $data['currentUser'] = Auth::user();

        $content = self::renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;
        return self::renderFile($layout, $data);
    }

    private static function renderFile(string $view, array $data): string
    {
        $path = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($path)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
