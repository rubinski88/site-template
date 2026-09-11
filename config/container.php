<?php

declare(strict_types=1);

/** @var Framework\DotEnv $dotenv */
$container = new Framework\Container;

$container->set(
    Framework\Database::class,
    fn() => new Framework\Database(
        $dotenv->getString('DB_HOST'),
        $dotenv->getString('DB_NAME'),
        $dotenv->getString('DB_USER'),
        $dotenv->getString('DB_PASSWORD')
    )
);

return $container;
