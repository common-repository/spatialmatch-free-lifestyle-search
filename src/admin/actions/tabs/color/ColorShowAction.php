<?php

namespace SpatialMatchIdx\admin\actions\tabs\color;

use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\models\ColorSettingsModel;
use SpatialMatchIdx\SpatialMatchIdx;

class ColorShowAction implements ActionInterface
{
    public function execute()
    {
        $colorSettings = ColorSettingsModel::getData();
        $tabs = SettingsTabsContainer::getInstance()->getTabs();

        $pluginPageUrl = menu_page_url(SpatialMatchIdx::SLUG, false);
        (new AdminSettingsPageView())->getTabPage($pluginPageUrl, $tabs, $colorSettings->getAttributes(), 'color');
    }
}
