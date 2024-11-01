<?php

namespace SpatialMatchIdx\admin\actions\tabs\compliance;

use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\models\ComplianceSettingsModel;
use SpatialMatchIdx\SpatialMatchIdx;

class ComplianceShowAction implements ActionInterface
{
    public function execute()
    {
        $complianceSettings = ComplianceSettingsModel::getData();
        $tabs = SettingsTabsContainer::getInstance()->getTabs();

        $pluginPageUrl = menu_page_url(SpatialMatchIdx::SLUG, false);
        (new AdminSettingsPageView())->getTabPage($pluginPageUrl, $tabs, $complianceSettings->getAttributes(), 'compliance');
    }
}
