<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\api\SlipstreamApiClientException;
use SpatialMatchIdx\core\helpers\URLHelper;
use SpatialMatchIdx\services\AdminNoticesService;

class LeadCreateAction  extends BaseAction
{
    public function execute()
    {
        $userData = $_POST['user'];

        try {
            $result = SlipstreamApiClient::getInstance()->createUser($userData);
            AdminNoticesService::getInstance()->addMessage(sprintf('Lead has been created.'), AdminNoticesService::MESSAGE_SUCCESS);
            $url = URLHelper::getLinkWithParams(['page']);
        } catch (SlipstreamApiClientException $exception) {
            AdminNoticesService::getInstance()->addMessage($exception->getMessage(), AdminNoticesService::MESSAGE_ERROR);
            $url = $_SERVER['HTTP_REFERER'];
        }

        $this->redirect($url);
    }
}
