<?php

namespace SpatialMatchIdx\models;

class ColorSettingsModel extends SpatialMatchSettingsModel
{
    /**
     * @var string
     */
    protected $parentOptionName = '';

    public $primary_color = '#2374ad';
    public $primary_link = '#3674ad';
    public $primary_hover = '#bfb300';
    public $primary_font = '#ffffff';
    public $primary_contrast = '#3674ad';

    public $secondary_font = '#ffffff';
    public $secondary_link = '#ffffff';
    public $secondary_color = '#428bca';

    public $menu_bg = '#EFEFEF';
    public $menu_font = '#000000';
    public $menu_font_color = '#262626';
    public $menu = '#fcfcfc';


    public $hover_font = '#000000';
    public $hover_bg = '#629ace';

    public $marker_active = '#1aad00';
    public $marker_other = '#d68d20';
}
