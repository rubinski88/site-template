<?php

declare(strict_types=1);

/**
 * Loads a project class from its namespace-based file path
 *
 * Handles only App and Framework namespaces and leaves other class
 * names available to other registered autoloaders
 *
 * @param string $className Fully qualified class name to load
 */

function autoload(string $className): void
{
    if (
        !str_starts_with($className, 'App\\')
        && !str_starts_with($className, 'Framework\\')
    ) {
        return;
    }

    $path = str_replace('\\', '/', $className) . '.php';
    $file = ROOT_PATH . 'src/' . $path;

    if (!is_file($file)) {
        return;
    }

    require $file;
}

spl_autoload_register('autoload');
