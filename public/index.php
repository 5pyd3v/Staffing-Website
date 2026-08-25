<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Core\Request;
use App\Core\Router;

$router = new Router();
require dirname(__DIR__) . '/config/routes.php';

$router->dispatch(new Request());
