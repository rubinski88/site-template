<?php

declare(strict_types=1);

namespace Framework;

class View
{
    /**
     *
     * @param string $template Part of the path to template
     * @param array<string, mixed> $data Data associated with template
     *
     * @return string Template code
     */
    public function render(string $template, array $data = []): string
    {
        $templatePath = ROOT_PATH . 'templates/' . $template . '.php';

        extract($data, EXTR_SKIP);

        ob_start();

        require ROOT_PATH . 'templates/layout.php';

        return ob_get_clean();
    }
}
