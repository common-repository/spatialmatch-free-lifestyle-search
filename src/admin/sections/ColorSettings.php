<?php

namespace SpatialMatchIdx\admin\sections;

class ColorSettings implements TabInterface
{
    /**
     * @var string[]
     */
    private $defaultColors = [
        'primary_color' => '#ffee00',
        'marker_active' => '#ff0000',
        'marker_other' => '#FFA500',
        'menu_bg' => '#f3f3f3',
        'primary_link' => '#ffee00',
        'primary_hover' => '#bfb300',
        'primary_font' => '#000',
        'menu_font' => '#3f3f3f',
        'secondary_font' => '#fff',
        'primary_contrast' => '#0db933',
        'hover_font' => '#FFC0CB',
        'secondary_link' => '#ff0000',
        'secondary_color'=> '#777',
        'menu_font_color' => '#262626',
        'menu' => '#efefef',
        'hover-bg' => '#fff',

    ];

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Colors';
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return 'color';
    }

    /**
     * @return string[]
     */
    public function getDefaultColors(): array
    {
        return $this->defaultColors;
    }
}
