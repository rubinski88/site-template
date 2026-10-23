<?php

declare(strict_types=1);

namespace Framework;

use RuntimeException;

/**
 * Represents an application resource that could not be found.
 *
 * Allows application code to signal that a requested domain resource
 * is missing independently of route matching failures.
 */
class NotFoundException extends RuntimeException {}
