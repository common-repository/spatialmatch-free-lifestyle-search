<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\DeveloperLicenseFormModel;

class DeveloperLicenseRegistrationShowAction extends BaseAction
{
    public function execute()
    {
        $form = new ActiveForm(DeveloperLicenseFormModel::getData());

        $options = [
            'form' => $form,
        ];

        (new AdminSettingsPageView())->getPage('admin/partial/pages/developer-license-registration.phtml', $options);
    }
}
