<?php

namespace SpatialMatchIdx\admin\actions\tabs\color;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\models\ColorSettingsModel;
use SpatialMatchIdx\services\AdminNoticesService;

class ColorSaveAction extends BaseAction
{
    public function execute()
    {
        $colorSettingsModel = new ColorSettingsModel();
        $colorSettingsModel->setAttributes($_POST['color']);

        $colorSettingsModel->save();

        AdminNoticesService::getInstance()->addMessage('Settings updated.', AdminNoticesService::MESSAGE_SUCCESS);

        $url = home_url($_POST['_wp_http_referer']);

        $this->redirect($url);
    }
}
