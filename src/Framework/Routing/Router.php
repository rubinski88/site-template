<?php

declare(strict_types=1);

namespace Framework\Routing;

use InvalidArgumentException;

/**
 * Registers routes and matches incoming requests.
 *
 * Preserves route registration order, converts route definitions into
 * regular expressions and resolves request paths with their HTTP methods.
 */
final class Router
{
    /**
     * Registered routes and their associated parameters.
     *
     * Routes are stored and checked in registration order.
     *
     * @var list<array{
     *     path: string,
     *     pattern: string,
     *     methods: non-empty-list<string>,
     *     params: array<string, mixed>
     * }>
     */
    private array $routes = [];

    /**
     * Registers a route and its associated parameters.
     *
     * Validates and normalizes HTTP methods, rejects duplicate methods
     * and conflicting path declarations, and checks the generated pattern.
     * Appends one route entry only after every check succeeds. Custom method
     * tokens are accepted; whitespace is rejected rather than trimmed.
     *
     * @param string $path Route path
     * @param non-empty-list<string> $methods Allowed HTTP methods
     * @param array<string, mixed> $params Parameters returned when the route matches
     *
     * @throws InvalidArgumentException If methods are invalid or duplicated, the path conflicts or the pattern is invalid
     */
    public function add(
        string $path,
        array $methods,
        array $params,
    ): void {
        if ($methods === [] || !array_is_list($methods)) {
            throw new InvalidArgumentException(
                'HTTP methods for route ' . $path . ' must be a non-empty list.',
            );
        }

        $normalizedMethods = [];

        foreach ($methods as $method) {
            if (
                !is_string($method)
                || preg_match('/\A[!#$%&\'*+.^_`|~0-9A-Za-z-]+\z/', $method) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Each HTTP method for route ' . $path
                        . ' must be a string containing a valid HTTP method token.',
                );
            }

            $normalizedMethod = strtoupper($method);

            if (in_array($normalizedMethod, $normalizedMethods, true)) {
                throw new InvalidArgumentException(
                    'Duplicate HTTP method ' . $normalizedMethod
                        . ' for route ' . $path . '.',
                );
            }

            $normalizedMethods[] = $normalizedMethod;
        }

        $pattern = $this->buildRoutePattern($path);
        $this->validateRoutePattern($path, $pattern);

        foreach ($this->routes as $route) {
            $conflictingMethods = array_values(
                array_intersect($route['methods'], $normalizedMethods),
            );

            if (
                $conflictingMethods !== []
                && $this->haveSameRoutePath($route['path'], $path)
            ) {
                throw new InvalidArgumentException(
                    'Route ' . var_export($path, true)
                        . ' [' . implode(', ', $normalizedMethods) . ']'
                        . ' conflicts with registered route '
                        . var_export($route['path'], true)
                        . ' [' . implode(', ', $route['methods']) . ']'
                        . ' for HTTP methods: '
                        . implode(', ', $conflictingMethods) . '.',
                );
            }
        }

        $this->routes[] = [
            'path' => $path,
            'pattern' => $pattern,
            'methods' => $normalizedMethods,
            'params' => $params,
        ];
    }

    /**
     * Matches a request path and HTTP method.
     *
     * Uses the patterns checked during registration and returns parameters
     * from the first route matching both the request path and HTTP method.
     *
     * @param string $path Request path
     * @param string $method Request HTTP method
     *
     * @return array<string, mixed>|false Matched parameters or false
     *
     * @throws MethodNotAllowedException If the path exists but the method is not allowed
     */
    public function match(string $path, string $method): array|false
    {
        $requestPath = $path;

        $path = rawurldecode($path);
        $path = trim($path, '/');

        $method = strtoupper($method);

        $allowedMethods = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $path, $matches) !== 1) {
                continue;
            }

            if (!in_array($method, $route['methods'], true)) {
                $allowedMethods = array_merge($allowedMethods, $route['methods']);

                continue;
            }

            $matches = array_filter(
                $matches,
                'is_string',
                ARRAY_FILTER_USE_KEY,
            );

            return array_merge($matches, $route['params']);
        }

        if ($allowedMethods !== []) {
            throw new MethodNotAllowedException(
                $requestPath,
                $method,
                array_values(array_unique($allowedMethods)),
            );
        }

        return false;
    }

    /**
     * Compares route declarations without changing parameter constraints.
     *
     * Ignores leading and trailing slashes and compares static segments
     * using the same Unicode case-insensitive mode as route matching.
     * Parameter names and constraints must match literally; arbitrary
     * intersections between different patterns are not detected.
     *
     * @param string $firstPath First declared route path
     * @param string $secondPath Second declared route path
     *
     * @return bool Whether the declarations describe the same route path
     */
    private function haveSameRoutePath(string $firstPath, string $secondPath): bool
    {
        $firstSegments = explode('/', trim($firstPath, '/'));
        $secondSegments = explode('/', trim($secondPath, '/'));

        if (count($firstSegments) !== count($secondSegments)) {
            return false;
        }

        $parameterPattern = '#^\{[a-zA-Z_][a-zA-Z0-9_]*(?::.+)?\}$#';

        foreach ($firstSegments as $index => $firstSegment) {
            $secondSegment = $secondSegments[$index];

            if ($firstSegment === $secondSegment) {
                continue;
            }

            if (
                preg_match($parameterPattern, $firstSegment) === 1
                || preg_match($parameterPattern, $secondSegment) === 1
            ) {
                return false;
            }

            $staticPattern = '#\A' . preg_quote($firstSegment, '#') . '\z#iu';

            if (preg_match($staticPattern, $secondSegment) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks a generated route pattern before registration.
     *
     * Converts PCRE warnings into configuration errors containing the
     * original route path and diagnostic message. Always restores the
     * previous error handler, including when validation fails.
     *
     * @param string $path Original route path
     * @param string $pattern Generated regular expression
     *
     * @throws InvalidArgumentException If PCRE cannot process the pattern
     */
    private function validateRoutePattern(string $path, string $pattern): void
    {
        set_error_handler(
            static function (int $severity, string $message) use ($path): never {
                throw new InvalidArgumentException(
                    'Invalid pattern for route ' . var_export($path, true)
                        . ': ' . $message,
                );
            },
            E_WARNING,
        );

        try {
            if (preg_match($pattern, '') === false) {
                throw new InvalidArgumentException(
                    'Invalid pattern for route ' . var_export($path, true)
                        . ': ' . preg_last_error_msg(),
                );
            }
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Builds a regular expression from a declared route path.
     *
     * Converts static path segments and parameter placeholders
     * into a pattern used for request matching.
     *
     * @param string $routePath Registered route path
     *
     * @return string Route matching pattern
     */
    private function buildRoutePattern(string $routePath): string
    {
        $routePath = trim($routePath, '/');

        $segments = explode('/', $routePath);

        $segments = array_map(
            function (string $segment): string {
                if (
                    preg_match(
                        '#^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$#',
                        $segment,
                        $matches,
                    ) === 1
                ) {
                    return '(?<' . $matches[1] . '>[^/]+)';
                }

                if (
                    preg_match(
                        '#^\{([a-zA-Z_][a-zA-Z0-9_]*):(.+)\}$#',
                        $segment,
                        $matches,
                    ) === 1
                ) {
                    return '(?<'
                        . $matches[1]
                        . '>'
                        . $matches[2]
                        . ')';
                }

                return preg_quote($segment, '#');
            },
            $segments,
        );

        return '#^' . implode('/', $segments) . '$#iu';
    }
}
