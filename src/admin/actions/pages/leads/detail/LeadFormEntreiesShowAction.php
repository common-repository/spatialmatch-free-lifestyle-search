<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use GuzzleHttp\Exception\ServerException;
use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\LeadPageView;
use SpatialMatchIdx\admin\PluginAdmin;
use SpatialMatchIdx\admin\sections\LeadTabsContainer;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\helpers\DateTimeHelper;
use SpatialMatchIdx\core\helpers\URLHelper;
use SpatialMatchIdx\core\render\wpListTable\LeadFormEntriesWPListTable;
use SpatialMatchIdx\services\AdminNoticesService;


class LeadFormEntreiesShowAction  extends BaseAction
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
            'timestamp' => DateTimeHelper::getTimeframe(),
            'parameters' => $_REQUEST['s'] ?? null,
        ];


        try {
            $leadActionsData = SlipstreamApiClient::getInstance()->getUserFormEntries($_GET['lead_id'], [], $pagination);
        } catch (ServerException $exception) {
            AdminNoticesService::getInstance()->addMessage('An unexpected API error has occurred. Our tech team has been notified. If you need immediate assistance, contact support.', AdminNoticesService::MESSAGE_ERROR);

            $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $data['actions'] = $leadActionsData['actions'] ?? [];

        $listTable = LeadFormEntriesWPListTable::getInstance();
        $listTable->setItemsPerPage($pagination['pageSize']);
        $listTable->setPageNumber($pagination['pageNumber']);
        $listTable->setItems($data['actions'] ?? []);

        if (empty($leadActionsData['total'])) {
            $leadActionsDataTotal = 0;
        } else {
            $leadActionsDataTotal = $leadActionsData['total'];
        }

        $listTable->setTotalItems($leadActionsDataTotal);
        $listTable->prepare_items();

        ob_start();
        echo '<form action="" method="get">';
        echo URLHelper::urlGetParamsToSearchFormFields();
        $listTable->search_box('search', 'search_id');
        $listTable->views();
        echo '</form>';
        $listTable->display();
        $data['listTable']  = ob_get_clean();

        $data['filterTitle'] = DateTimeHelper::getTimeframe(true);

        $pluginPageUrl = menu_page_url(PluginAdmin::LEADS_PAGE_SLUG, false);
        (new LeadPageView())->getTabPage($pluginPageUrl, $tabs, $data, 'lead-form-entries', [
            'lead-favorites' => $data['lead']['summary']['favorites']['total'],
            'lead-searches' => $data['lead']['summary']['searches']['total'],
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
