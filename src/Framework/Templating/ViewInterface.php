<?php

declare(strict_types=1);

namespace Framework\Templating;

/**
 * Defines the contract for rendering application templates.
 *
 * Implementations combine a named template with supplied data and
 * a selected layout, then return the complete rendered content.
 */
interface ViewInterface
{
    /**
     * Renders a template inside the selected layout.
     *
     * Resolves the template and layout names according to the
     * implementation and makes the supplied data available to the template.
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
    ): string;
}
