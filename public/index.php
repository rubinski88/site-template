<?php

declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__) . '/');

#[\NoDiscard('the escaped value must be used')]
function e(string|\Stringable|int|float|null $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        'UTF-8',
    );
}

require ROOT_PATH . '/src/Controller/ProductController.php';

(new \ProductController)->index();
