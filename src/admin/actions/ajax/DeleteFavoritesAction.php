<?php

namespace SpatialMatchIdx\admin\actions\ajax;

use SpatialMatchIdx\admin\actions\BaseAjaxAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\services\AdminNoticesService;

class DeleteFavoritesAction extends BaseAjaxAction
{
    public function execute()
    {
        try {
            $result = SlipstreamApiClient::getInstance()->deleteFavorites($_POST['id'], $_POST['userId']);
            AdminNoticesService::getInstance()->addMessage(sprintf('Favorite listing was successfully deleted.'), AdminNoticesService::MESSAGE_SUCCESS);
            $this->success($result);
        } catch (\Exception $exception) {
            $this->error('Error. Can\'t remove favorite item.');
        }

    }
}
