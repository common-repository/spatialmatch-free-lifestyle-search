<?php

namespace SpatialMatchIdx\admin\actions\tabs\map;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\forms\ActiveForm;
use SpatialMatchIdx\models\MapSettingsModel;
use SpatialMatchIdx\services\AdminNoticesService;

class MapSaveAction extends BaseAction
{
    public function execute()
    {
        $mapSettingsModel = new MapSettingsModel();
        $activeForm = new ActiveForm($mapSettingsModel);

        if($activeForm->load($_POST['map']) && $activeForm->validate()) {
            $activeForm->save();
            AdminNoticesService::getInstance()->addMessage('Settings updated.', AdminNoticesService::MESSAGE_SUCCESS);
        }

        $url = home_url($_POST['_wp_http_referer']);

        $this->redirect($url);
    }
}
