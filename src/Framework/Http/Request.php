<?php

declare(strict_types=1);

namespace Framework\Http;


use UnexpectedValueException;

use RuntimeException;

/**
 * Represents an incoming HTTP request.
 *
 * Stores the request URI, effective HTTP method and snapshots of the
 * PHP input and server arrays associated with the request.
 */
final class Request
{

    /**
     * Creates a request from explicitly supplied HTTP data.
     *
     * Stores the supplied values without normalization, allowing requests
     * to be created independently of PHP superglobals when required.
     *
     * @param string $uri Request URI
     * @param string $method Effective HTTP method
     * @param array<string, mixed> $get Query parameters
     * @param array<string, mixed> $post Request body parameters
     * @param array<string, mixed> $files Uploaded file data
     * @param array<string, mixed> $cookie Cookie values
     * @param array<string, mixed> $server Server and execution environment data
     */
    public function __construct(
        public string $uri,
        public string $method,
        public array $get,
        public array $post,
        public array $files,
        public array $cookie,
        public array $server
    ) {}

    /**
     * Creates a request from the current PHP superglobals.
     *
     * Requires the request URI and method, normalizes the method to uppercase
     * and applies a PUT, PATCH or DELETE override supplied by a POST request.
     *
     * @return self Request populated from the current HTTP environment
     *
     * @throws RuntimeException If the request URI or method is unavailable
     * @throws UnexpectedValueException If the method override is invalid
     */
    public static function createFromGlobals(): self
    {
        $requestUri = $_SERVER['REQUEST_URI']
            ?? throw new RuntimeException(
                'REQUEST_URI is unavailable'
            );

        $requestMethod = $_SERVER['REQUEST_METHOD']
            ?? throw new RuntimeException(
                'REQUEST_METHOD is unavailable'
            );

        $requestMethod = strtoupper($requestMethod);

        if (
            $requestMethod === 'POST'
            && array_key_exists('_method', $_POST)
        ) {
            $overriddenMethod = $_POST['_method'];

            if (!is_string($overriddenMethod)) {
                throw new UnexpectedValueException(
                    'The _method field must be a string'
                );
            }
            $overriddenMethod = $overriddenMethod
            |> trim(...)
            |> strtoupper(...);

            $requestMethod = match ($overriddenMethod) {
                'PUT' => 'PUT',
                'PATCH' => 'PATCH',
                'DELETE' => 'DELETE',
                default => throw new UnexpectedValueException(
                    'Unsupported method override'
                ),
            };
        }

        return new self(
            $requestUri,
            $requestMethod,
            $_GET,
            $_POST,
            $_FILES,
            $_COOKIE,
            $_SERVER
        );
    }
}
