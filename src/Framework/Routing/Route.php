<?php

declare(strict_types=1);

namespace Framework\Routing;

use Attribute;
use Framework\MiddlewareInterface;
use InvalidArgumentException;

/**
 * Defines a controller route.
 *
 * Attaches a request path, HTTP methods and middleware list
 * to a controller action.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    /**
     * Creates route metadata.
     *
     * Stores the path, allowed HTTP methods and ordered middleware.
     * Requires a non-empty list of strings; Router validates each method
     * when the route is registered.
     *
     * @param string $path Route path
     * @param non-empty-list<string> $methods Allowed HTTP methods
     * @param list<class-string<MiddlewareInterface>> $middleware Middleware classes
     *
     * @throws InvalidArgumentException If methods is not a non-empty list of strings
     */
    public function __construct(
        public string $path,
        public array $methods = ['GET'],
        public array $middleware = [],
    ) {
        if ($methods === [] || !array_is_list($methods)) {
            throw new InvalidArgumentException(
                'HTTP methods for route ' . $path . ' must be a non-empty list.',
            );
        }

        foreach ($methods as $method) {
            if (!is_string($method)) {
                throw new InvalidArgumentException(
                    'Each HTTP method for route ' . $path . ' must be a string.',
                );
            }
        }
    }
}
