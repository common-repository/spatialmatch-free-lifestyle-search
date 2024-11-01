<?php

namespace SpatialMatchIdx\admin\actions\tabs\compliance;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\ComplianceSettingsModel;
use SpatialMatchIdx\services\AdminNoticesService;

class ComplianceSaveAction extends BaseAction
{
    public function execute()
    {
        $complianceSettingsModel = new ComplianceSettingsModel();
        $activeForm = new ActiveForm($complianceSettingsModel);


        if($activeForm->load($_POST['compliance']) && $activeForm->validate()) {
            $activeForm->save();

            AdminNoticesService::getInstance()->addMessage('Settings updated.', AdminNoticesService::MESSAGE_SUCCESS);
        }

        $url = home_url($_POST['_wp_http_referer']);

        $this->redirect($url);
    }
}

