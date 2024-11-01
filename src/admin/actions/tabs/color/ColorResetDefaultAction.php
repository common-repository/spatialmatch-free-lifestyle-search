<?php

namespace SpatialMatchIdx\admin\actions\tabs\color;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\models\ColorSettingsModel;

class ColorResetDefaultAction extends BaseAction
{
    public function execute()
    {
        $colorSettingsModel = new ColorSettingsModel();
        $colorSettingsModel->reset();

        $url = $_SERVER['HTTP_REFERER'];

        $this->redirect($url);
    }
}
