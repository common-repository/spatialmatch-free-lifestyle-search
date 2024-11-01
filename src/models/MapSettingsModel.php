<?php

namespace SpatialMatchIdx\models;

class MapSettingsModel extends SpatialMatchSettingsModel
{
    public $google_api_key;

    public $area_id;

    public $latitude;

    public $longitude;

    public $market;

    public $defaultZoom = 10;

    public $zoom = 10;

    protected $validateRules = [];

    public function getGoogleApiKeyOrDefault()
    {
        if (empty($this->google_api_key)) {
           return 'AIzaSyCiN8vm6uQT9AJn-hO1Gly29CEblSwB4oI';
        }

        return $this->google_api_key;
    }
}
