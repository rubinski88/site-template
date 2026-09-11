<?php

declare(strict_types=1);

/** @var Framework\DotEnv $dotenv */
$errorHandler = new Framework\ErrorHandler(
    $dotenv->getBool('SHOW_ERRORS')
);

set_error_handler([$errorHandler, 'handleError']);
set_exception_handler([$errorHandler, 'handleException']);
