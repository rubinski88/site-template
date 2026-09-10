<?php

declare(strict_types=1);

$container = new Framework\Container;

$container->set(
    Framework\Database::class,
    fn() => new Framework\Database('127.0.0.1', 'shop', 'shop_app', 'secret123')
);

return $container;
