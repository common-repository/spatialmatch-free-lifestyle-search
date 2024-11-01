<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\TrialLicenseFormModel;
use SpatialMatchIdx\services\StaticMarketService;

class TrialLicenseRegistrationShowAction extends BaseAction
{
    public function execute()
    {
        $form = new ActiveForm(TrialLicenseFormModel::getData());

        $options = [
            'marketsList' => StaticMarketService::getInstance()->getMarkets(),
            'form' => $form,
        ];

        (new AdminSettingsPageView())->getPage('admin/partial/pages/trial-license-registration.phtml',$options);
    }
}
