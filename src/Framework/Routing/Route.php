<?php

declare(strict_types=1);

namespace Framework\Routing;

use Attribute;
use Framework\MiddlewareInterface;

/**
 * Defines a controller route.
 *
 * Attaches a request path and ordered middleware list to a controller action.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * Creates route metadata.
     *
     * Stores the path and middleware assigned to the controller action.
     *
     * @param string $path Route path
     * @param list<class-string<MiddlewareInterface>> $middleware Middleware classes
     */
    public function __construct(
        public string $path,
        public array $middleware = [],
    ) {}
}
