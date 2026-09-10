<?php

declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__) . '/');

require ROOT_PATH . 'config/autoload.php';

$requestUri = $_SERVER['REQUEST_URI']
    ?? throw new \RuntimeException('REQUEST_URI is unavailable');

$uri = \Uri\Rfc3986\Uri::parse($requestUri);

if ($uri === null || $uri->getRawPath() === '') {
    http_response_code(400);
    exit;
}

$path = $uri->getPath();

$router = new Framework\Routing\Router;

$routeLoader = new Framework\Routing\AttributeLoader($router);
$routeLoader->load(App\Controller\HomeController::class);
$routeLoader->load(App\Controller\ProductController::class);

$container = require ROOT_PATH . 'config/container.php';

$dispatcher = new Framework\Dispatcher($router, $container);
$dispatcher->handle($path);

#[\NoDiscard('the escaped value must be used')]
function e(string|\Stringable|int|float|null $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        'UTF-8',
    );
}
