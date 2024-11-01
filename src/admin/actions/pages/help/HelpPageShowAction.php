<?php

namespace SpatialMatchIdx\admin\actions\pages\help;

use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;

class HelpPageShowAction implements ActionInterface
{
    public function execute()
    {
        (new AdminSettingsPageView())->getPage('admin/partial/pages/help.phtml',[]);
    }
}
