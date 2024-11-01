<?php

namespace SpatialMatchIdx\admin\actions\pages\leads\detail;

use SpatialMatchIdx\admin\actions\BaseAction;
use SpatialMatchIdx\admin\LeadPageView;
use SpatialMatchIdx\admin\PluginAdmin;
use SpatialMatchIdx\admin\sections\LeadTabsContainer;
use SpatialMatchIdx\core\api\SlipstreamApiClient;
use SpatialMatchIdx\core\helpers\DateTimeHelper;
use SpatialMatchIdx\core\helpers\URLHelper;
use SpatialMatchIdx\core\render\View;
use SpatialMatchIdx\core\render\wpListTable\LeadAnalyticsActionsWPListTable;
use SpatialMatchIdx\core\render\wpListTable\LeadAnalyticsSessionsWPListTable;
use SpatialMatchIdx\services\AdminNoticesService;

class LeadAnalyticsShowAction  extends BaseAction
{
    public function execute()
    {
        if (empty($_GET['lead_id'])) {
            AdminNoticesService::getInstance()->addMessage('lead_id are required', AdminNoticesService::MESSAGE_ERROR);

            $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $data['lead'] = SlipstreamApiClient::getInstance()->getUserById($_GET['lead_id'], true);
        $tabs = LeadTabsContainer::getInstance()->getTabs();
        $subTab = '';

        $pagination = [
            'pageSize' => 10,
            'pageNumber' => $this->getPageNumber(),
            'sortField' => $_GET['orderby'] ?? null,
            'sortOrder' => $_GET['order'] ?? null,
            'timestamp' => DateTimeHelper::getTimeframe(),
            'parameters' => $_REQUEST['s'] ?? null,
        ];


        if (empty($_GET['item']) || (isset($_GET['item']) && 'actions' === $_GET['item'])) {
            $leadActionsData = SlipstreamApiClient::getInstance()->getUserAnalyticsSearches($_GET['lead_id'], [], $pagination);
            $leadActionsData = $this->normalizeActions($leadActionsData);
            $data['list'] = $leadActionsData['actions'] ?? [];
            $listTable = LeadAnalyticsActionsWPListTable::getInstance();
            $subTab = 'actions';
        } elseif (isset($_GET['item']) && 'sessions' === $_GET['item']) {
            $pagination = array_merge($pagination, DateTimeHelper::getTimeframeFromToFormat());
            $leadActionsData = SlipstreamApiClient::getInstance()->getUserAnalyticsSessions($_GET['lead_id'], [], $pagination);
            $data['list'] = $leadActionsData['sessions'] ?? [];
            $listTable = LeadAnalyticsSessionsWPListTable::getInstance();
            $subTab = 'sessions';
        } elseif (isset($_GET['item']) && 'session-details' === $_GET['item']) {
            $leadSessionsData = SlipstreamApiClient::getInstance()->getUserAnalyticsSession($_GET['ssid']);

            $session = $leadSessionsData['sessions'][0] ?? null;

            $sessionDetailsContent = $this->getSessionDetailsContent($session);

            $data['sessionDetails'] = View::renderString(
                'admin/partial/tab-panels/lead/lead-analytics-session-details.phtml',
                [
                    'session' => $session,
                    'sessionDetailsContent' => $sessionDetailsContent
                ]
            );

            $pluginPageUrl = menu_page_url(PluginAdmin::LEADS_PAGE_SLUG, false);
            (new LeadPageView())->getTabPage(
                $pluginPageUrl,
                $tabs,
                $data,
                'lead-analytics',
                [
                    'lead-favorites' => $data['lead']['summary']['favorites']['total'] ?? 0,
                    'lead-searches' => $data['lead']['summary']['searches']['total'] ?? 0,
                ]
            );

            die();
        }


        $listTable->setItemsPerPage($pagination['pageSize']);
        $listTable->setPageNumber($pagination['pageNumber']);
        $listTable->setItems($data['list'] ?? []);

        if (empty($leadFavoritesData['total'])) {
            $leadFavoritesDataTotal = 0;
        } else {
            $leadFavoritesDataTotal = $leadFavoritesData['total'];
        }

        $listTable->setTotalItems($leadFavoritesDataTotal);
        $listTable->prepare_items();


        $data['listTable'] = $subTab === 'actions'
            ? $this->generateActionsListTable($listTable)
            : $this->generateSessionsListTable($listTable)
        ;

        $data['filterTitle'] = DateTimeHelper::getTimeframe(true);

        $pluginPageUrl = menu_page_url(PluginAdmin::LEADS_PAGE_SLUG, false);
        (new LeadPageView())->getTabPage(
            $pluginPageUrl,
            $tabs,
            $data,
            'lead-analytics',
            [
                'lead-favorites' => $data['lead']['summary']['favorites']['total'] ?? 0,
                'lead-searches' => $data['lead']['summary']['searches']['total'] ?? 0,
            ]
        );
    }

    private function generateActionsListTable($listTable)
    {
        ob_start();
        echo '<form action="" method="get">';
        echo URLHelper::urlGetParamsToSearchFormFields();
        $listTable->search_box('search', 'search_id');
        $listTable->views();
        echo '</form>';
        $listTable->display();
        return  ob_get_clean();
    }


    private function generateSessionsListTable($listTable)
    {
        ob_start();
        $listTable->views();
        $listTable->display();
        return ob_get_clean();
    }

    /**
     * @return int
     */
    private function getPageNumber(): int
    {
        return isset($_GET['paged']) ? (int)$_GET['paged'] : 1;
    }

    private function normalizeUsers($users = [])
    {
        if(empty($users)) {
            return [];
        }

        foreach ($users as $user) {
            $users[$user['id']] = $user;
        }

        return $users;
    }

    private function normalizeActions($data)
    {
        $users = $this->normalizeUsers($data['users'] ?? []);

        if (isset($data['actions'])) {
            foreach ($data['actions'] as &$action) {
                if (isset($action['user']) && isset($users[$action['user']])) {
                    $action['user'] = $users[$action['user']];
                }
            }
        }

        return $data;
    }

    private function getSessionDetailsContent($session)
    {

        if ($session['actions']['total'] < 1) {
            return '';
        }

        // Group results by actions

        $actions = [];

        if (isset($session['actions']['items'])) {
            foreach ($session['actions']['items'] as $item) {
                $actions[$item['action']][] = $item;
            }
        }

        // Render each action items

        $count = 0;
        $totals = false;
        $details = false;

        foreach ($actions as $action => $items) {
            switch ($action) {
                case 'listings.search':
                    $count += count($items);
                    $totals .= '<li>Property Searches Performed: ' . count($items) . '</li>';

                    $details .= '<h4>Property Searches</h4>';
                    $details .= '<ol>';

                    foreach ($items as $item) {
                        $tmp = [];

                        $parameters = json_decode($item['parameters'], true);

                        foreach($parameters['filters'] as $k => $v) {
                            if (is_array($v)) {
                                $v = implode(', ', $v);
                            }

                            $tmp[] = $k . ': ' . $v;
                        }

                        $details .= '<li>' . implode('; ', $tmp) . '</li>';
                    }

                    $details .= '</ol>';

                    break;

                case 'listings.get':
                    $count += count($items);
                    $totals .= '<li>Listing Details Viewed: ' . count($items) . '</li>';

                    $details .= '<h4>Listing Details Viewed</h4>';
                    $details .= '<ol>';

                    foreach ($items as $item) {
                        $parameters = json_decode($item['parameters'], true);
                        $links = [];

                        foreach ($parameters['ids'] as $id) {
                            $url =  URLHelper::getListingUrlById($id, $parameters['market']);
                            $links[] = '<a href="' . $url . '" target="_blank">' . $id . '</a>';
                        }

                        $details .= '<li>' . implode(', ',  $links) . '</li>';
                    }

                    $details .= '</ol>';
                    break;

                case 'avm':
                    $count += count($items);
                    $totals .= '<li>Home Value Reports Created: ' . count($items) . '</li>';
                    $details .= '<h4>Home Value Reports</h4>';

                    $details .= '<ol>';

                    foreach ($items as $item) {
                        if (isset($item['parameters'])) {
                            $parameters = json_decode($item['parameters'], true);

                            $details .= '<li>' . implode(', ', (array)$parameters['address']) . '</li>';
                        }
                    }

                    $details .= '</ol>';

                    break;

                case 'users.favorites.search':
                    $count += count($items);
                    $totals .= '<li>User Favorites Page Viewed: ' . count($items) . '</li>';

                    break;

                case 'users.searches.search':
                    $count += count($items);
                    $totals .= '<li>User Searches Page Viewed: ' . count($items) . '</li>';

                    break;
            }
        }

        $content = '';

        $content .= '<h4>Total Actions: ' . $count . '</h4>';

        $content .=  '<ul>' . $totals . '</ul>';

        if ($details) {
            $content .=  '<h3>Action details:</h3>';

            $content .=  $details;
        }

        return $content;
    }
}
