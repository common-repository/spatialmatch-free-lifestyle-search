<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\AdminSettingsPageView;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\DeveloperLicenseFormModel;
use SpatialMatchIdx\models\PaidLicenseFormModel;
use SpatialMatchIdx\models\TrialLicenseFormModel;
use SpatialMatchIdx\services\StaticMarketService;


class UpgradeLicenseRegistrationShowAction extends BaseAction
{
    public function execute()
    {
        if (isset($_SESSION['ActiveForm']) && is_array($_SESSION['ActiveForm']) && count($_SESSION['ActiveForm']) > 0) {
            foreach ($_SESSION['ActiveForm'] as $key => $value) {
                if ($key === 'trial_license_form') {
                    $form = new ActiveForm(TrialLicenseFormModel::getData());
                } else {
                    $form = new ActiveForm(PaidLicenseFormModel::getData());
                }
            }
        } elseif (isset($_GET['license_type'])) {
            if ($_GET['license_type'] === 'trial') {
                $form = new ActiveForm(TrialLicenseFormModel::getData());
            } else {
                $form = new ActiveForm(PaidLicenseFormModel::getData());
            }
        } else {
            $form = new ActiveForm(PaidLicenseFormModel::getData());
        }

        if (null === $form->getValue('email')) {
            $prevFormData = TrialLicenseFormModel::getData();

            if (null === $prevFormData->email) {
                $prevFormData = DeveloperLicenseFormModel::getData();
            }

            if (null !== $prevFormData) {
                $form->load($prevFormData->getAttributes());
            }
        }

        $options = [
            'marketsList' => StaticMarketService::getInstance()->getMarkets(),
            'form' => $form,
        ];

        (new AdminSettingsPageView())->getPage('admin/partial/pages/upgrade-license-type.phtml',$options);
    }
}
