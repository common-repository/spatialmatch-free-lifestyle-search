<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\LeadPageView;
use SpatialMatchIdx\admin\PluginAdmin;
use SpatialMatchIdx\admin\sections\LeadTabsContainer;
use SpatialMatchIdx\core\api\SlipstreamApiClient;

class LeadDetailShowAction  extends BaseAction
{
    public function execute()
    {
        $data['lead'] = SlipstreamApiClient::getInstance()->getUserById($_GET['lead_id'], true);
        $tabs = LeadTabsContainer::getInstance()->getTabs();

        $data['type'] = $_GET['type'] ?? 'show';

        $pluginPageUrl = menu_page_url(PluginAdmin::LEADS_PAGE_SLUG, false);
        (new LeadPageView())->getTabPage($pluginPageUrl, $tabs, $data, 'lead-detail', [
            'lead-favorites' => $data['lead']['summary']['favorites']['total'] ?? 0,
            'lead-searches' => $data['lead']['summary']['searches']['total'] ?? 0,
        ]);
    }
}
