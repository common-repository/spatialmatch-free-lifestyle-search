<?php

namespace SpatialMatchIdx\admin\actions\tabs\license;

use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\models\LicenseModel;
use SpatialMatchIdx\SpatialMatchIdx;

class LicenseShowAction implements ActionInterface
{
    public function execute()
    {
        $license = LicenseModel::getData();
        $tabs = SettingsTabsContainer::getInstance()->getTabs();

        $pluginPageUrl = menu_page_url(SpatialMatchIdx::SLUG, false);
        $data['license'] = $license->getAttributes();

        if (!in_array($data['license']['license_type'], ['invalid', '', null], false) ) {
            $apiClient = SlipstreamApiClient::getInstance();
            $licenseApiStatus = $apiClient->getStatus();

            $generalSettings = GeneralSettingsModel::getData();
            $mapSearchAppUrl = site_url() . '/' . $generalSettings->slug;

            $data['customer'] = $licenseApiStatus['result']['customer'];
            $data['markets'] = $licenseApiStatus['result']['markets'];
            $data['mapSearchAppUrl'] = $mapSearchAppUrl;
        }

        (new AdminSettingsPageView())->getTabPage($pluginPageUrl, $tabs, $data, 'license');
    }
}
