<?php

declare(strict_types=1);

namespace Framework;

use RuntimeException;
use UnexpectedValueException;

class DotEnv
{
    /**
     * Loaded environment variables
     *
     * Values are stored privately and exposed only through typed accessors
     *
     * @var array<string, string>
     */
    private array $variables = [];

    /**
     * Loads environment variables from a file
     *
     * Reads NAME=value entries, skips empty lines and comments, and validates
     * the entire file before replacing the privately stored variables
     *
     * @param string $path Absolute path to the environment file
     *
     * @throws RuntimeException If the environment file cannot be read
     * @throws UnexpectedValueException If an entry has invalid syntax or a duplicate name
     */
    public function load(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException(
                'Environment file is not readable: ' . $path
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            throw new RuntimeException(
                'Failed to read environment file: ' . $path
            );
        }

        $variables = [];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                throw new UnexpectedValueException(
                    'Invalid .env entry on line '
                        . ($lineNumber + 1)
                        . ': missing "="'
                );
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            if (preg_match('#^[a-zA-Z_][a-zA-Z0-9_]*$#', $name) !== 1) {
                throw new UnexpectedValueException(
                    'Invalid environment variable name on line '
                        . ($lineNumber + 1)
                );
            }

            if (array_key_exists($name, $variables)) {
                throw new UnexpectedValueException(
                    'Duplicate environment variable "' . $name
                        . '" on line ' . ($lineNumber + 1)
                );
            }

            if ($value === '') {
                throw new UnexpectedValueException(
                    'Empty value is not allowed for "' . $name
                        . '" on line ' . ($lineNumber + 1)
                );
            }

            if (
                str_contains($value, '"')
                || str_contains($value, "'")
            ) {
                throw new UnexpectedValueException(
                    'Quotes are not allowed on line '
                        . ($lineNumber + 1)
                );
            }

            $variables[$name] = $value;
        }

        $this->variables = $variables;
    }

    /**
     * Returns a loaded environment variable
     *
     * Provides the raw string value used by the typed public accessors
     *
     * @param string $name Name of the environment variable
     *
     * @return string Raw environment variable value
     *
     * @throws RuntimeException If the environment variable is not defined
     */
    private function get(string $name): string
    {
        if (!array_key_exists($name, $this->variables)) {
            throw new RuntimeException(
                'Environment variable "' . $name . '" is not defined'
            );
        }

        return $this->variables[$name];
    }

    /**
     * Returns an environment variable as a string
     *
     * Preserves the value exactly as it was stored after parsing
     *
     * @param string $name Name of the environment variable
     *
     * @return string Environment variable value
     *
     * @throws RuntimeException If the environment variable is not defined
     */
    public function getString(string $name): string
    {
        return $this->get($name);
    }

    /**
     * Returns an environment variable as an integer
     *
     * Accepts only values that represent a valid integer
     *
     * @param string $name Name of the environment variable
     *
     * @return int Parsed integer value
     *
     * @throws RuntimeException If the environment variable is not defined
     * @throws UnexpectedValueException If the value is not a valid integer
     */
    public function getInt(string $name): int
    {
        $value = filter_var(
            $this->get($name),
            FILTER_VALIDATE_INT
        );

        if ($value === false) {
            throw new UnexpectedValueException(
                'Environment variable "' . $name . '" must be an integer'
            );
        }

        return $value;
    }

    /**
     * Returns an environment variable as a boolean
     *
     * Accepts only the lowercase values true and false
     *
     * @param string $name Name of the environment variable
     *
     * @return bool Parsed boolean value
     *
     * @throws RuntimeException If the environment variable is not defined
     * @throws UnexpectedValueException If the value is not a valid boolean
     */
    public function getBool(string $name): bool
    {
        return match ($this->get($name)) {
            'true' => true,
            'false' => false,
            default => throw new UnexpectedValueException(
                'Environment variable "' . $name
                    . '" must be either "true" or "false"'
            ),
        };
    }
}
