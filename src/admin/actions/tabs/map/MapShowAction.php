<?php

namespace SpatialMatchIdx\admin\actions\tabs\map;

use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\MapSettingsModel;
use SpatialMatchIdx\services\LicenseService;
use SpatialMatchIdx\SpatialMatchIdx;

class MapShowAction implements ActionInterface
{
    public function execute()
    {
        $mapSettings = MapSettingsModel::getData();
        $data = $mapSettings->getAttributes();
        $data['markets'] = LicenseService::getInstance()->getMarkets();

        $form = new ActiveForm($mapSettings);

        $data['form'] = $form;

        $tabs = SettingsTabsContainer::getInstance()->getTabs();

        $pluginPageUrl = menu_page_url(SpatialMatchIdx::SLUG, false);
        (new AdminSettingsPageView())->getTabPage($pluginPageUrl, $tabs, $data, 'map');
    }
}
