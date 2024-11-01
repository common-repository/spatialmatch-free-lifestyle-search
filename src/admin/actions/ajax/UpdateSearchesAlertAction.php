<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;

class UpdateSearchesAlertAction extends BaseAjaxAction
{
    /**
     *
     */
    public function execute()
    {
        $result = SlipstreamApiClient::getInstance()->updateSearchesAlert($_REQUEST['data']);

        $this->success(['result' => $result,]);
    }
}
