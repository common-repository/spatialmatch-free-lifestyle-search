<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;

class SuccessLicenseRegistrationShowAction extends BaseAction
{
    public function execute()
    {
        $options = [];
        (new AdminSettingsPageView())->getPage('admin/partial/pages/success-license-registration.phtml', $options);
    }
}
