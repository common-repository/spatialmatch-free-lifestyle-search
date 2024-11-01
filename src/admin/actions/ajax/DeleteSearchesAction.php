<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\services\AdminNoticesService;

class DeleteSearchesAction extends BaseAjaxAction
{
    public function execute()
    {
        try {
            $result = SlipstreamApiClient::getInstance()->deleteSearches($_POST['id'], $_POST['userId']);
            AdminNoticesService::getInstance()->addMessage(sprintf('Search "%s" was successfully deleted.', $_POST['name']), AdminNoticesService::MESSAGE_SUCCESS);
            $this->success($result);
        } catch (\Exception $exception) {
            $this->error('Error. Can\'t remove search item.');
        }

    }
}
