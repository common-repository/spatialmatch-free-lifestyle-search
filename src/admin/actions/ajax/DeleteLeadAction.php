<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\services\AdminNoticesService;

class DeleteLeadAction extends BaseAjaxAction
{
    public function execute()
    {
        try {
            $result = SlipstreamApiClient::getInstance()->deleteUser( $_POST['userId']);
            AdminNoticesService::getInstance()->addMessage(sprintf('Lead "%s" was successfully deleted.', $_POST['name']), AdminNoticesService::MESSAGE_SUCCESS);
            $this->success($result);
        } catch (\Exception $exception) {
            $this->error('Error. Can\'t remove lead.');
        }

    }
}
