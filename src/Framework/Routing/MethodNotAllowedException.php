<?php

declare(strict_types=1);

namespace Framework\Routing;

use RuntimeException;

/**
 * Represents a route matched with an unsupported HTTP method.
 *
 * Carries the methods accepted by the matched path so the error handler
 * can include them in the HTTP Allow response header.
 */
final class MethodNotAllowedException extends RuntimeException
{
    /**
     * Creates a method-not-allowed exception.
     *
     * Builds an error message from the request details and retains the
     * allowed methods for construction of the HTTP response.
     *
     * @param string $path Matched request path
     * @param string $method Received HTTP method
     * @param list<string> $allowedMethods Methods allowed for the path
     */
    public function __construct(
        string $path,
        string $method,
        public readonly array $allowedMethods,
    ) {
        parent::__construct(
            "Method {$method} is not allowed for path {$path}"
        );
    }
}
