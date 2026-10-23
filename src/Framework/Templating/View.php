<?php

declare(strict_types=1);

namespace Framework\Templating;

/**
 * Renders application views using native PHP templates.
 *
 * Resolves template and layout paths, exposes supplied data as local
 * variables, captures generated output and returns it as a string.
 */
final class View implements ViewInterface
{
    /**
     * Renders a PHP template inside the selected layout.
     *
     * Resolves both files relative to the templates directory, extracts
     * the supplied data without overwriting local variables and captures
     * all output generated while executing the layout.
     *
     * @param string $template Template name without the file extension
     * @param array<string, mixed> $data Template data
     * @param string $layout Layout name without the file extension
     *
     * @return string Complete rendered content
     */
    public function render(
        string $template,
        array $data = [],
        string $layout = 'base'
    ): string {
        $templatePath = ROOT_PATH
            . 'templates/'
            . $template
            . '.php';

        $layoutPath = ROOT_PATH
            . 'templates/layouts/'
            . $layout
            . '.php';

        extract($data, EXTR_SKIP);

        ob_start();

        require $layoutPath;

        return ob_get_clean();
    }
}
