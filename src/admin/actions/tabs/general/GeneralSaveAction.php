<?php

namespace SpatialMatchIdx\admin\actions\tabs\general;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\GeneralSettingsModel;
use SpatialMatchIdx\services\AdminNoticesService;

class GeneralSaveAction extends BaseAction
{
    public function execute()
    {
        $data = $_POST['general'];

        $data['require_user_registration_phone_number'] = isset($data['require_user_registration_phone_number']);
        $data['registration_require_user_phone_number'] = isset($data['registration_require_user_phone_number']);
        $data['require_user_registration'] = isset($data['require_user_registration']);
        $data['allow_opt_out'] = isset($data['allow_opt_out']);
        $data['inquiry_require_user_phone_number'] = isset($data['inquiry_require_user_phone_number']);

        $generalSettingsModel = new GeneralSettingsModel();
        $generalSettingsModel->setAttributes($data);

        $activeForm = new ActiveForm($generalSettingsModel);
        if (!empty($generalSettingsModel->slug)) {
            $postTypes = get_post_types();

            $pageExistFlag = false;
            foreach ($postTypes as $postType) {
                $pageExist = get_page_by_path($generalSettingsModel->slug, OBJECT, $postType);

                if ($pageExist !== null) {
                    $pageExistFlag = true;
                }
            }

            if ($pageExistFlag) {
                $activeForm->setError('slug', 'exist', 'This slug already exists');
            }
        }

        if($activeForm->load($data) && $activeForm->validate()) {
            $activeForm->save();

            AdminNoticesService::getInstance()->addMessage('Settings updated.', AdminNoticesService::MESSAGE_SUCCESS);
        }

        $url = home_url($_POST['_wp_http_referer']);

        $this->redirect($url);
    }
}

