<?php

namespace SpatialMatchIdx\admin\actions\tabs\general;

use SpatialMatchIdx\admin\sections\NavigationContainer;
use SpatialMatchIdx\admin\sections\SettingsTabsContainer;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\SpatialMatchIdx;

class GeneralShowAction implements ActionInterface
{
    public function execute()
    {
        $generalSettings = GeneralSettingsModel::getData();
        $tabs = SettingsTabsContainer::getInstance()->getTabs();

        $navigationContainer = new NavigationContainer();
        $allMenus = $navigationContainer->getAllActiveRegisteredMenu();

        $primaryMenu = NavigationContainer::getPrimaryMenu();

        $pluginPageUrl = menu_page_url(SpatialMatchIdx::SLUG, false);

        if (!is_array($allMenus)) {
            $allMenus = [];
        }

        $form = new ActiveForm($generalSettings);

        $options['form'] = $form;

        $options['menus'] = $allMenus;

        if (empty($options['menu']) && $primaryMenu instanceof \WP_Term) {
            $options['menu'] = $primaryMenu->slug;
        }

        (new AdminSettingsPageView())->getTabPage($pluginPageUrl, $tabs, $options, 'general');
    }
}
