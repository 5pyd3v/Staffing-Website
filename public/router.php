<?php

/**
 * Router script for PHP's built-in development server:
 *   php -S localhost:8000 -t public public/router.php
 * Not used in production (Apache/.htaccess handles rewriting there).
 */

declare(strict_types=1);

$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

if ($path !== '/' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}

require __DIR__ . '/index.php';
