<?php

namespace SpatialMatchIdx\services;

class StaticMarketService
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $markets = [];

    public function __construct()
    {
        $this->loadServicesFromJsonFile();
    }

    /**
     * @return StaticMarketService
     */
    public static function getInstance(): StaticMarketService
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function getMarkets(): array
    {
        return $this->markets;
    }

    private function loadServicesFromJsonFile()
    {
        $marketJSONConfig = file_get_contents(SPATIALMATCH_IDX_PATH . '/config/market-list-config.json');

        $this->markets = json_decode($marketJSONConfig);
    }
}


