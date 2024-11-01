<?php
namespace SpatialMatchIdx\admin\actions\pages\licenseTypes;

use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;

class LicenseTypesShowAction implements ActionInterface
{
    public function execute()
    {
        $options = [];
        (new AdminSettingsPageView())->getPage('admin/partial/pages/license-types.phtml',$options);
    }
}
