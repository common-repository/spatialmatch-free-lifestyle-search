<?php

namespace SpatialMatchIdx\admin\actions\tabs\license;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\models\LicenseModel;
use SpatialMatchIdx\services\LicenseService;

class LicenseSaveAction  extends BaseAction
{
    public function execute()
    {
        $licenseModel = new LicenseModel();
        $licenseService = LicenseService::getInstance();

        $postData = !empty($_POST['license']) ? $_POST['license'] : [];

        $operationType = $licenseService->checkOperationType($postData);

        $licenseModel->setAttributes($postData);

        $licenseModel->save();

        $redirectUrl = $licenseService->afterLicenseKeySave($operationType);

        if ($redirectUrl === null) {
            $redirectUrl = home_url($_POST['_wp_http_referer']);
        }

        $this->redirect($redirectUrl);
    }
}
