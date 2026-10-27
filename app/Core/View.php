<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Template renderer with layouts and partials.
 *
 * Views live in app/views/<name>.php. Layouts in app/views/layouts/.
 * All rendering is escape-by-default: templates must wrap dynamic values in e().
 */
final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'store'): string
    {
        $content = self::partial($template, $data);
        if ($layout === null) {
            return $content;
        }
        return self::partial('layouts/' . $layout, $data + ['content' => $content]);
    }

    public static function partial(string $template, array $data = []): string
    {
        $file = BASE_PATH . '/app/views/' . $template . '.php';
        if (!is_readable($file)) {
            throw new \RuntimeException("View not found: $template");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
