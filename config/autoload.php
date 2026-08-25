<?php

declare(strict_types=1);

/**
 * Zero-dependency PSR-4-ish autoloader: App\Foo\Bar -> app/Foo/Bar.php
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = ROOT_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});
