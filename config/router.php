<?php

declare(strict_types=1);

$router = new Framework\Routing\Router;

$routeLoader = new Framework\Routing\AttributeLoader($router);
$routeLoader->load(App\Controller\HomeController::class);
$routeLoader->load(App\Controller\ProductController::class);

return $router;
