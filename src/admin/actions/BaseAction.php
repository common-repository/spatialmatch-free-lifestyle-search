<?php

namespace SpatialMatchIdx\admin\actions;

use SpatialMatchIdx\core\actions\interfaces\ActionInterface;

abstract class BaseAction implements ActionInterface
{
    /**
     * @param string $url
     */
    protected function redirect(string $url)
    {
        if (!headers_sent()) {
            if (isset($_SERVER['HTTP_REFERER']) && ($url === $_SERVER['HTTP_REFERER'])) {
                wp_redirect($_SERVER['HTTP_REFERER'] , 303);
            } else {
                wp_redirect($url , 303);
            }
        } else {
            $string = '<script>';
            $string .= 'window.location = "' . $url . '"';
            $string .= '</script>';

            echo $string;
        }
        exit;
    }
}
