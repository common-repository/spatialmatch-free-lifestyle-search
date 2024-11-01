<?php

namespace SpatialMatchIdx\core\render;

class View
{
    /**
     * @param $template
     * @param $args
     */
    public static function render($template, $args)
    {
        extract($args, EXTR_OVERWRITE);

        require_once SPATIALMATCH_IDX_PATH . 'templates/' . $template;
    }

    /**
     * @param $template
     * @param $args
     */
    public static function renderFront($template, $args)
    {
        extract($args, EXTR_OVERWRITE);

        $filename = basename($template);

        $templateThemePath = get_theme_file_path('hji-templates/' . $filename);

        if (!file_exists($templateThemePath)) {
            $templateThemePath = SPATIALMATCH_IDX_PATH . 'templates/' . $template;
        }

        require_once $templateThemePath;
    }

    /**
     * @param $template
     * @param $args
     * @return string
     */
    public static function renderString($template, $args): string
    {
        ob_start();
        self::render($template, $args);

        return ob_get_clean();
    }

    /**
     * @param $template
     * @param $args
     * @return string
     */
    public static function renderFrontString($template, $args): string
    {
        ob_start();
        self::renderFront($template, $args);

        return ob_get_clean();
    }
}
