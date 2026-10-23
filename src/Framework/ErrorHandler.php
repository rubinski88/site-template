<?php

declare(strict_types=1);

namespace Framework;

use ErrorException;
use Framework\Routing\RouteNotFoundException;
use Throwable;
use UnexpectedValueException;
use Framework\Routing\MethodNotAllowedException;

/**
 * Handles PHP errors and uncaught exceptions.
 *
 * Converts PHP errors into exceptions and either displays or logs
 * uncaught failures according to the configured error display mode.
 */
final class ErrorHandler
{
    /**
     * Creates an error handler for the selected display mode.
     *
     * Detailed exception output is enabled only when the supplied value is true.
     *
     * @param bool $showErrors Whether detailed exception information should be displayed
     */
    public function __construct(
        private bool $showErrors
    ) {}

    /**
     * Converts a PHP error into an ErrorException.
     *
     * Receives callback arguments from set_error_handler() and preserves
     * the original severity, file path and line number in the exception.
     *
     * @param int $errno PHP error severity
     * @param string $errstr PHP error message
     * @param string $errfile Absolute path to the file where the error occurred
     * @param int $errline Line number where the error occurred
     *
     * @throws ErrorException For every PHP error received by the handler
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        throw new ErrorException(
            $errstr,
            0,
            $errno,
            $errfile,
            $errline
        );
    }

    /**
     * Handles an uncaught exception.
     *
     * Clears active output buffers, selects the HTTP status
     * and renders the corresponding error page.
     *
     * @param Throwable $exception Uncaught exception
     *
     * @throws Throwable If detailed exception output is enabled
     */
    public function handleException(Throwable $exception): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $statusCode = match (true) {
            $exception instanceof RouteNotFoundException => 404,
            $exception instanceof NotFoundException => 404,
            $exception instanceof MethodNotAllowedException => 405,
            $exception instanceof UnexpectedValueException => 400,
            default => 500,
        };

        http_response_code($statusCode);

        if ($exception instanceof MethodNotAllowedException) {
            header(
                'Allow: '
                    . implode(
                        ', ',
                        $exception->allowedMethods,
                    )
            );
        }

        if ($this->showErrors) {
            ini_set('display_errors', '1');

            throw $exception;
        }

        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set(
            'error_log',
            ROOT_PATH . 'var/log/php-errors.log'
        );

        error_log((string) $exception);

        require ROOT_PATH
            . 'templates/errors/'
            . $statusCode
            . '.php';
    }
}
