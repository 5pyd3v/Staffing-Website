<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('VIEW_PATH', ROOT_PATH . '/views');

require_once __DIR__ . '/env.php';
load_env(ROOT_PATH . '/.env');

require_once __DIR__ . '/autoload.php';
require_once ROOT_PATH . '/app/helpers.php';

$appConfig = require __DIR__ . '/app.php';

date_default_timezone_set($appConfig['timezone']);

error_reporting(E_ALL);
ini_set('display_errors', $appConfig['debug'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');

App\Core\Session::start($appConfig['session']);

set_exception_handler(function (Throwable $e) use ($appConfig): void {
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(500);
    if (!empty($appConfig['debug'])) {
        echo '<pre style="padding:2rem;font:14px/1.5 monospace;color:#b91c1c;">'
            . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES)
            . '</pre>';
    } else {
        require VIEW_PATH . '/errors/500.php';
    }
});
