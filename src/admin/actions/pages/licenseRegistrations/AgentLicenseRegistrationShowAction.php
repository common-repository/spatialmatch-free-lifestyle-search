<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\PaidLicenseFormModel;
use SpatialMatchIdx\services\StaticMarketService;


class AgentLicenseRegistrationShowAction extends BaseAction
{
    public function execute()
    {
        $form = new ActiveForm(PaidLicenseFormModel::getData());

        $options = [
            'marketsList' => StaticMarketService::getInstance()->getMarkets(),
            'form' => $form,
        ];

        (new AdminSettingsPageView())->getPage('admin/partial/pages/agent-license-registration.phtml',$options);
    }
}
