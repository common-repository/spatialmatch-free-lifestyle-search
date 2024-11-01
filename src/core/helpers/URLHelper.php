<?php

namespace SpatialMatchIdx\core\helpers;

use SpatialMatchIdx\services\GeneralSettingsService;

class URLHelper
{
    /**
     * @param string $actionSlug
     * @param string $pageSlug
     * @return string|void
     */
    public static function getLink(string $actionSlug, string $pageSlug = 'hji-spatialmatch-idx')
    {
        return home_url('wp-admin/admin.php?page=' . $pageSlug . '&action=' . $actionSlug);
    }

    /**
     * @param array $paramsList
     * @param array $paramsValues
     * @return string
     */
    public static function getLinkWithParams(array $paramsList = [], array $paramsValues = []): string
    {
        $urlParams = [];

        foreach ($paramsList as $param) {
            if (isset($paramsValues[$param])) {
                $urlParams[$param] = $paramsValues[$param];
                unset($paramsValues[$param]);

                continue;
            }

            if (isset($_REQUEST[$param])) {
                $urlParams[$param] = $_REQUEST[$param];
            }
        }

        $urlParams = array_merge($urlParams, $paramsValues);

        return home_url(sprintf('wp-admin/admin.php?%s', http_build_query($urlParams)));
    }

    /**
     * @param $listingId
     * @param null|string $market
     * @return string
     */
    public static function getListingUrlById($listingId, $market = null): string
    {
        $generalSettings  = GeneralSettingsService::getInstance()->getSettings();

        return site_url(sprintf('%s/#/search?listing=%s&market=%s', $generalSettings->slug, $listingId, $market));
    }

    public static function getMapsearchUrl()
    {
        $generalSettings  = GeneralSettingsService::getInstance()->getSettings();

        return site_url(sprintf('%s/', $generalSettings->slug));
    }

    /**
     * @return string
     */
    public static function urlGetParamsToSearchFormFields(): string
    {
        $urlGetParams = $_GET;
        unset($urlGetParams['s'], $urlGetParams['paged']);
        $formFields = '';

        foreach ($urlGetParams as $urlGetParamKey => $urlGetParamValue) {
            $formFields .= sprintf('<input type="hidden" name="%s" value="%s">', $urlGetParamKey, $urlGetParamValue) . PHP_EOL;
        }

        return $formFields;
    }
}
