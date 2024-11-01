<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\api\SlipstreamApiClientException;
use SpatialMatchIdx\services\AdminNoticesService;

class LeadUpdateAction  extends BaseAction
{
    public function execute()
    {
        $userData = $_POST['user'];
        $userData['email'] = !empty($userData['new-email']) ? $userData['new-email'] : $userData['email'];

        try {
            SlipstreamApiClient::getInstance()->updateUser($userData);
            AdminNoticesService::getInstance()->addMessage(sprintf('Lead has been updated.'), AdminNoticesService::MESSAGE_SUCCESS);
            $url = $_POST['_referer'];
        } catch (SlipstreamApiClientException $exception) {
            AdminNoticesService::getInstance()->addMessage($exception->getMessage(), AdminNoticesService::MESSAGE_ERROR);
            $url = $_SERVER['HTTP_REFERER'];

            if (isset($_POST['_referer'])) {
                $url .=  '&_referer=' . $_REQUEST['_referer'];
            }
        }

        $this->redirect($url);
    }
}
