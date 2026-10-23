<?php

declare(strict_types=1);

namespace Framework\Routing;

use RuntimeException;

/**
 * Represents a request path that does not match a registered route.
 *
 * Distinguishes a missing route from a matched route that rejects the
 * incoming HTTP method.
 */
final class RouteNotFoundException extends RuntimeException {}
