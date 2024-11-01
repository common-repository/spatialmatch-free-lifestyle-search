<?php

namespace SpatialMatchIdx\services;

use SpatialMatchIdx\models\GeneralSettingsModel;

class GeneralSettingsService
{
    /**
     * @var GeneralSettingsModel
     */
    private $generalSettings;
    /**
     * @var array
     */
    private static $instances = [];


    public function __construct()
    {
        $this->generalSettings = GeneralSettingsModel::getData();
    }

    /**
     * @return GeneralSettingsService
     */
    public static function getInstance(): GeneralSettingsService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function refreshGeneralSettingsModel()
    {
        $this->generalSettings = GeneralSettingsModel::getData();
    }

    /**
     * @return GeneralSettingsModel
     */
    public function getSettings()
    {
        return $this->generalSettings;
    }

    public function getApplicationUrl()
    {
        return site_url($this->getSettings()->getAttribute('slug'));
    }
}
