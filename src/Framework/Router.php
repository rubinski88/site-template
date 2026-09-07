<?php

declare(strict_types=1);

namespace Framework;

final class Router
{
    /**
     * Registered routes and their associated parameters
     *
     * Each element stores a route path and the parameters
     * returned when the route matches the request path
     *
     * @var list<array{path: string, params: array<string, mixed>}>
     */
    private array $routes = [];

    /**
     * Registers a route and its associated parameters
     *
     * Routes are stored in registration order
     * and checked in the same order
     *
     * @param string $path Route path
     * @param array<string, mixed> $params Parameters returned when the route matches
     */
    public function add(string $path, array $params): void
    {
        $this->routes[] = [
            'path' => $path,
            'params' => $params
        ];
    }

    /**
     * Matches the request path against the registered routes
     *
     * Returns the parameters of the first matching route
     * together with named values
     * captured from the request path
     *
     * @param string $path Request path to match
     *
     * @return array<string, mixed>|false Matched route parameters, or false if no route matches
     */
    public function match(string $path): array|false
    {
        $path = rawurldecode($path);
        $path = trim($path, '/');

        foreach ($this->routes as $route) {

            $pattern = $this->buildRoutePattern($route['path']);

            if (preg_match($pattern, $path, $matches) === 1) {

                $matches = array_filter(
                    $matches,
                    'is_string',
                    ARRAY_FILTER_USE_KEY,
                );

                return array_merge($matches, $route['params']);
            }
        }

        return false;
    }

    /**
     * Builds a regular expression from a registered route path
     *
     * Converts static segments and parameter placeholders
     * into a pattern used to match request paths
     *
     * @param string $routePath Registered route path to convert
     *
     * @return string Regular expression used for route matching
     */
    private function buildRoutePattern(string $routePath): string
    {
        $routePath = trim($routePath, '/');

        $segments = explode('/', $routePath);

        $segments = array_map(function (string $segment): string {
            if (preg_match(
                '#^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$#',
                $segment,
                $matches,
            ) === 1) {
                return '(?<' . $matches[1] . '>[^/]+)';
            }

            if (preg_match(
                '#^\{([a-zA-Z_][a-zA-Z0-9_]*):(.+)\}$#',
                $segment,
                $matches,
            ) === 1) {

                return '(?<' . $matches[1] . '>' . $matches[2] . ')';
            }

            return preg_quote($segment, '#');
        }, $segments);

        return '#^' . implode('/', $segments) . '$#iu';
    }
    /**
     * lolda
     * pizdes
     */
}
