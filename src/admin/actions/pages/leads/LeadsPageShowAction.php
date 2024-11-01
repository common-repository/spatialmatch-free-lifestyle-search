<?php
namespace SpatialMatchIdx\admin\actions\pages\leads;

use SpatialMatchIdx\admin\LeadPageView;
use SpatialMatchIdx\admin\PluginAdmin;
use SpatialMatchIdx\core\actions\interfaces\ActionInterface;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\helpers\LeadsNormalizeHelper;
use SpatialMatchIdx\core\helpers\LeadsOptionsHelper;
use SpatialMatchIdx\core\render\wpListTable\LeadsWPListTable;
use SpatialMatchIdx\services\LicenseService;

class LeadsPageShowAction implements ActionInterface
{
    /**
     * @var string[]
     */
    private $filters = [
        'all' => 'All',
        'today' => 'Today',
        '7days' => 'Last 7 days',
        '30days' => 'Last 30 days',
        'thismonth' => 'This Month',
        'custom' => 'Custom',
    ];
    public function execute()
    {
        $slipstreamApiClient = SlipstreamApiClient::getInstance();
        $licenseService = LicenseService::getInstance();

        $url = '';
        $normalizeUsers = [];
        $totalLeads = 0;
        $leadsListTable = LeadsWPListTable::getInstance();
        $leadsListTable->setItemsPerPage(25);
        $leadsListTable->setPageNumber($this->getPageNumber());

        if ($licenseService->isValid() && !$licenseService->isEmpty()) {
            $options = LeadsOptionsHelper::getLeadsOptions($_GET);
            $options['pageNumber'] = $this->getPageNumber();
            $urlQueryParamsArray = array_merge(['page' => PluginAdmin::LEADS_PAGE_SLUG], $options);
            $urlQueryParams = http_build_query($urlQueryParamsArray);
            $url = home_url('wp-admin/admin.php?' . $urlQueryParams);

            $users = $slipstreamApiClient->getLeads($options);

            $totalLeads = $users['result']['total'];
            $normalizeUsers = LeadsNormalizeHelper::normalizeUsersData($users['result']['users']);
        }

        $leadsListTable->setTotalItems($totalLeads);
        $leadsListTable->setItems($normalizeUsers);

        ob_start();
        $leadsListTable->views();
        $leadsListTable->search_box('search', 'search_id');
        $leadsListTable->prepare_items();
        $leadsListTable->display();
        $content = ob_get_clean();

        $activeFilter = 'All';
        if (isset($_GET['time_period'])) {
            if ($_GET['time_period'] === 'custom') {
                if (empty($_GET['from']) && !empty($_GET['to'])) {
                    $activeFilter = 'From - ' . (new \DateTime())->setTimestamp($_GET['to'])->format('F d, Y');
                } elseif (!empty($_GET['from']) && empty($_GET['to'])) {
                    $activeFilter = (new \DateTime())->setTimestamp($_GET['from'])->format('F d, Y') . ' - Till';
                } elseif (empty($_GET['from']) && empty($_GET['to'])) {
                    $activeFilter = 'All';
                } else {
                    $activeFilter = (new \DateTime())->setTimestamp($_GET['from'])->format('F d, Y');
                    $activeFilter .= ' - ';
                    $activeFilter .= (new \DateTime())->setTimestamp($_GET['to'])->format('F d, Y');
                }
            } else {
                $activeFilter = $this->filters[$_GET['time_period']];
            }
        }

        $data = [
            'context' => $content,
            'url' => $url,
            'search_result' => $options['keyword'] ?? null,
            'from' => $_GET['from'] ?? '0',
            'to' => $_GET['to'] ?? '0',
            'activeFilter' => $activeFilter,
            'totalLeads' => $totalLeads
        ];

        (new LeadPageView())->getPage('admin/partial/pages/leads.phtml', $data);
    }

    /**
     * @return int
     */
    private function getPageNumber()
    {
        return isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
    }
}
