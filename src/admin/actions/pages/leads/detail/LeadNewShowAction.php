<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;

class LeadNewShowAction extends BaseAction
{
    public function execute()
    {
        $options = [];
        (new AdminSettingsPageView())->getPage('admin/partial/tab-panels/lead/lead-new.phtml', $options);
    }
}
