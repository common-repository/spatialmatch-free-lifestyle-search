<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\LeadPageView;
use SpatialMatchIdx\admin\PluginAdmin;
use SpatialMatchIdx\admin\sections\LeadTabsContainer;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\render\wpListTable\LeadFavoritesWPListTable;


class LeadFavoritesShowAction  extends BaseAction
{
    public function execute()
    {
        $data['lead'] = SlipstreamApiClient::getInstance()->getUserById($_GET['lead_id'], true);
        $tabs = LeadTabsContainer::getInstance()->getTabs();

        $pagination = [
            'pageSize' => 25,
            'pageNumber' => $this->getPageNumber(),
            'sortField' => $_GET['orderby'] ?? null,
            'sortOrder' => $_GET['order'] ?? null,
        ];


        $leadFavoritesData = SlipstreamApiClient::getInstance()->getUserFavorites($_GET['lead_id'], [], $pagination);

        $data['favorites'] = $leadFavoritesData['favorites'] ?? [];

        $listTable = LeadFavoritesWPListTable::getInstance();
        $listTable->setItemsPerPage($pagination['pageSize']);
        $listTable->set_page_number($pagination['pageNumber']);
        $listTable->set_items($data['favorites']);

        if (empty($leadFavoritesData['total'])) {
            $leadFavoritesDataTotal = 0;
        } else {
            $leadFavoritesDataTotal = $leadFavoritesData['total'];
        }

        $listTable->set_total_items($leadFavoritesDataTotal);
        $listTable->prepare_items();

        ob_start();
        $listTable->display();
        $data['listTable']  = ob_get_clean();

        $pluginPageUrl = menu_page_url(PluginAdmin::LEADS_PAGE_SLUG, false);
        (new LeadPageView())->getTabPage($pluginPageUrl, $tabs, $data, 'lead-favorites', [
            'lead-favorites' => $data['lead']['summary']['favorites']['total'] ?? 0,
            'lead-searches' => $data['lead']['summary']['searches']['total'] ?? 0,
        ]);
    }

    /**
     * @return int
     */
    private function getPageNumber(): int
    {
        return isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
    }
}
