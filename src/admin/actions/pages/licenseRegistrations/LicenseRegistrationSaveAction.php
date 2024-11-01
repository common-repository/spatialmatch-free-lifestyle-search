<?php

namespace SpatialMatchIdx\admin\actions\pages\licenseRegistrations;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\DeveloperLicenseFormModel;
use SpatialMatchIdx\models\PaidLicenseFormModel;
use SpatialMatchIdx\models\TrialLicenseFormModel;

class LicenseRegistrationSaveAction extends BaseAction
{
    public function execute()
    {
        /* TODO: Save and send data to license registration endpoint */

        if (isset($_POST['license_type']) && isset($_POST['form_name'])) {
            switch ($_POST['license_type']) {
                case 'developer':
                    $form = new ActiveForm(new DeveloperLicenseFormModel());
                    break;
                case 'trial':
                    $form = new ActiveForm(new TrialLicenseFormModel());

                    if ('other_market' === $_POST[$_POST['form_name']]['market']) {
                        if (empty(trim($_POST[$_POST['form_name']]['other_market']))) {
                            $form->setError('other_market', 'required', 'Other MLS/Market is required');
                        }
                    }
                    break;
                case 'subscription':
                    $form = new ActiveForm(new PaidLicenseFormModel());

                    if ('other_market' === $_POST[$_POST['form_name']]['market']) {
                        if (empty(trim($_POST[$_POST['form_name']]['other_market']))) {
                            $form->setError('other_market', 'required',     'The Other MLS/Market field is required');
                        }
                    }
                    break;
            }

            if(isset($form) && $form->load($_POST['form_name']) && $form->validate()) {
                $form->save();

                $data = [
                    'time' => time(),
                    'license_type' => $_POST['license_type'],
                    'customer' => $form->getData(),
                ];

                if (!defined('SPM_ZAPIER_TRANSFER_BLOCK') || (defined('SPM_ZAPIER_TRANSFER_BLOCK') && SPM_ZAPIER_TRANSFER_BLOCK === false)) {
                    wp_remote_post('https://hooks.zapier.com/hooks/catch/5545867/opd7k19', [
                        'method' => 'POST',
                        'headers' => [
                            'Content-type' => 'application/json'
                        ],
                        'body'   => json_encode($data)
                    ]);
                }

                $url = home_url('/wp-admin/admin.php?page=hji-spatialmatch-idx&action=success-license-registration');
            } else {
                $url = home_url($_POST['_wp_http_referer']);
            }
        }

        $this->redirect($url);
    }
}
