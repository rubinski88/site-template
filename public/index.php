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



$router = new Framework\Router;

$router->add(
    '/',
    [
        'controller' => \App\Controller\HomeController::class,
        'action' => 'index'
    ]
);

$router->add(
    '/products',
    [
        'controller' => \App\Controller\ProductController::class,
        'action' => 'index'
    ]
);

$router->add(
    '/products/{id:\d+}',
    [
        'controller' => \App\Controller\ProductController::class,
        'action' => 'show'
    ]
);

$params = $router->match($path);

if ($params === false) {
    http_response_code(404);
    exit('404 not found');
}

$controllerClass = $params['controller'];
$action = $params['action'];

$controller = new $controllerClass();
$controller->$action();

#[\NoDiscard('the escaped value must be used')]
function e(string|\Stringable|int|float|null $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        'UTF-8',
    );
}
