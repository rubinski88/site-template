<?php

declare(strict_types=1);

use Framework\Dispatcher;
use Framework\DotEnv;
use Framework\Http\Request;

define('ROOT_PATH', dirname(__DIR__) . '/');

require ROOT_PATH . 'config/helpers.php';
require ROOT_PATH . 'config/autoload.php';

$dotenv = new DotEnv;
$dotenv->load(ROOT_PATH . '.env');

require ROOT_PATH . 'config/error_handler.php';

$router = require ROOT_PATH . 'config/router.php';
$container = require ROOT_PATH . 'config/container.php';

$dispatcher = new Dispatcher(
    $router,
    $container,
);

$request = Request::createFromGlobals();

$response = $dispatcher->handle($request);
$response->send();
