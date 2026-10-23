<?php

declare(strict_types=1);

#[NoDiscard('the escaped value must be used')]
function e(
    string|Stringable|int|float|null $value
): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5,
        'UTF-8',
    );
}
