<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\DateTimeHelper;
use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadAnalyticsSessionsWPListTable extends WP_List_Table
{
    /**
     * @var int
     */
    private $itemsPerPage = 10;

    /**
     * @var int
     */
    private $pageNumber = 1;

    /**
     * @var array
     */
    private static $instances;

    /**
     * @var int;
     */
    private $totalItems;

    /**
     * Container for json_decoded params
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    protected $actions = [
        'avm'                   => 'Home Valuation',
        'form_entry'            => 'Form Entry',
        'users.favorites.search'=> 'Favorites View'
    ];

    public function __construct($args = [])
    {
        parent::__construct($args);
    }

    /**
     * @return LeadAnalyticsSessionsWPListTable
     */
    public static function getInstance(): LeadAnalyticsSessionsWPListTable
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    /**
     * @return array
     */
    public function get_columns(): array
    {
        $columns = [
            'ipAddress'     => __('IP', 'hji-users'),
            'startTime'     => __('Start', 'hji-users'),
            'endTime'       => __('End', 'hji-users'),
            'duration'      => __('Duration', 'hji-users'),
            'sessionDetails'=> __('Session Details', 'hji-users'),
        ];

        if (isset($_GET['tab']) && $_GET['tab'] != 'all') {
            unset($columns['action']);
        }

        return $columns;
    }

    /**
     * @return array
     */
    public function getSortableColumns(): array
    {
        return [
            'startTime' => ['startTime', false],
            'endTime' => ['endTime', false],
        ];
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $value
     * @return LeadAnalyticsSessionsWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadAnalyticsSessionsWPListTable
    {
        $this->itemsPerPage = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setTotalItems(int $value)
    {
        $this->totalItems = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setPageNumber(int $value)
    {
        $this->pageNumber = $value;

        return $this;
    }

    public function column_default($item, $columnName)
    {
        switch($columnName)
        {
            case 'ipAddress':

                return isset($item[$columnName]) ? $item[$columnName] : 'N/A';

            case 'startTime':
            case 'endTime':
                if (isset($item[$columnName]))
                {
                    return DateTimeHelper::toDateTime($item[$columnName]);
                }
                else
                {
                    return 'N/A';
                }

            case 'duration':
                if (isset($item[$columnName]))
                {
                    return gmdate("H:i:s", $item[$columnName]);
                }
                else
                {
                    return 'N/A';
                }

            case 'sessionDetails':
                $url = URLHelper::getLinkWithParams([
                    'page',
                    'lead_id',
                    'action',
                    'item',
                ],
                [
                    'ssid' => $item['id'],
                    'item' => 'session-details',
                ]);

                return '<a href="'. $url .'">View Details</a>';
        }
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->getSortableColumns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        if ($this->totalItems > 0) {
            $this->set_pagination_args([
                'total_items' => $this->getTotalItems(),                  //WE have to calculate the total number of items
                'per_page'    => $this->getItemsPerPage()               //WE have to determine how many items to show on a page
            ]);
        }
    }

    /**
     * @param array $items
     * @return LeadAnalyticsSessionsWPListTable $this
     */
    public function setItems(array $items): LeadAnalyticsSessionsWPListTable
    {
        $this->items = $items;

        return $this;
    }

    public function get_items_per_page( $option, $default = 50 )
    {
        // Changing fro 20 to 50

        return parent::get_items_per_page($option, $default);
    }

    public function get_views()
    {
        $urlRequiredParams = [
            'page',
            'lead_id',
            'action',
            'item',
        ];

        $all = (!isset($_GET['timeframe'])) ? 'current' : 'all';

        $timeframe = (isset($_GET['timeframe'])) ? $_GET['timeframe'] : false;

        $today = ($timeframe == 'today') ? 'current' : 'today';
        $last7days = ($timeframe == '7days') ? 'current' : 'last7days';
        $last30days = ($timeframe == '30days') ? 'current' : 'last30days';
        $thisMonth = ($timeframe == 'thismonth') ? 'current' : 'thisMonth';
        $custom = ($timeframe && !in_array($timeframe, ['today', '7days', '30days', 'thismonth']))
            ? 'current'
            : 'custom';


        $views = [
            $all => sprintf('<a class="%s" href="%s">All</a>', $all, URLHelper::getLinkWithParams($urlRequiredParams)),
            $today => sprintf('<a class="%s" href="%s">Today</a>', $today, URLHelper::getLinkWithParams($urlRequiredParams, ['timeframe' => 'today'])),
            $last7days => sprintf('<a class="%s" href="%s">Last 7 days</a>', $last7days, URLHelper::getLinkWithParams($urlRequiredParams, ['timeframe' => '7days'])),
            $last30days => sprintf('<a class="%s" href="%s">Last 30 days</a>', $last30days, URLHelper::getLinkWithParams($urlRequiredParams, ['timeframe' => '30days'])),
            $thisMonth => sprintf('<a class="%s" href="%s">This Month</a>', $thisMonth, URLHelper::getLinkWithParams($urlRequiredParams, ['timeframe' => 'thismonth'])),
            $custom => sprintf('<a class="%s customtimeframe custom hji-spm-idx__filter" href="#">Custom</a>', $custom),
        ];

        return $views;
    }

    private function getParams($item)
    {
        $hash = hash('md5', json_encode($item));
        $params = null;

        if (isset($this->params[$hash])) {
            $params = $this->params[$hash];
        } else {
            if (isset($item['parameters'])) {
                $params = json_decode($item['parameters'], true);

            }
        }

        $this->params[$hash] = $params;

        return (array)$params;
    }

    function getActionTitle($action)
    {
        if (isset($this->actions[$action])) {
            return $this->actions[$action];
        } else {
            $str = str_replace('.', ' ', $action);
            $str = str_replace('listings get', 'listing details', $str);
            $title = str_replace('get', 'lookup', $str);

            return ucwords($title);
        }
    }

    private function _getUserId($action)
    {
        if (!isset($action['user'])) {
            return null;
        }

        return (is_array($action['user'])) ? $action['user']['id'] : $action['user'];
    }


    private function _getUserUrl($uid)
    {
        $params = [];

        $default = [
            'page'      => 'hji-users',
            'action'    => 'view',
            'tab'       => 'user',
            'uid'       => null
        ];

        if (!is_array($uid)) {
            $params['uid'] = $uid;
        } else if (is_array($uid)) {
            $params = $uid;
        }

        $params = array_merge($default, $params);

        return admin_url('admin.php?' . http_build_query($params));
    }


    private function _getUserSession($userId, $sessionId)
    {
        $params['uid']   = $userId;
        $params['tab']   = 'analytics';
        $params['item']  = 'sessions';
        $params['ssid']  = $sessionId;

        return $this->_getUserUrl($params);
    }

    private function _makeFiltersUserReadable(array $filters)
    {
        $result = [];

        if (!empty($filters)) {
            foreach ($filters as $filter => $value) {
                if (is_array($value) && $this->_isAssocArray($value)) {
                    $result = array_merge($result, $this->_makeFiltersUserReadable($value));
                } else {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }

                    $value = ($filter == 'market') ? strtoupper($value) : ((ctype_lower($value)) ? ucwords($value) : $value);

                    $result[] = [
                        'label' => $filter,
                        'value' => $value
                    ];
                }
            }
        }

        return $result;
    }


    private function _isAssocArray($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }

    private function actionColumnTemplateView($action, $parameters)
    {
        ob_start();
        switch($action) {
            case 'avm':

                echo implode(', ', $parameters['address']) . '<br />';

                if (isset($parameters['valuations']['general'])) {
                    echo '$' . number_format($parameters['valuations']['general']['low']) .
                        ' - ' . '$' . number_format($parameters['valuations']['general']['high']);
                } else if (isset($parameters['valuations']['default'])) {
                    echo '$' . number_format($parameters['valuations']['default']['EMV']);
                }

                break;

            case 'listings.get':

                $links = [];

                foreach ($parameters['listingUrls'] as $id => $url) {
                    $links[] = '<a href="' . $url . '" target="_blank">' . $id . '</a>';
                }

                echo 'Listing IDs: ' . implode(', ', $links) . '<br />';
                echo 'Market: ' . strtoupper($parameters['market']);

                break;

            case 'form_entry':
                if (isset($parameters['fields'])) {
                    foreach ($parameters['fields'] as $field) {
                        echo "{$field['label']}: {$field['value']}<br />";
                    }
                } else if (!empty($parameters) && is_array($parameters)) {
                    foreach ($parameters as $label => $value) {
                        $v = (is_string($value)) ? $value : json_encode($value);
                        echo "{$label}: {$v}<br />";
                    }
                }

                break;

            default:
                unset($parameters['options']);

                if (!empty($parameters)) {
                    foreach ($parameters as $item) {
                        if (!empty($item) && (isset($item['label']) && isset($item['value']))) {
                            echo ucwords($item['label'])  . ': ' . $item['value'] . '<br />';
                        }
                    }
                }

                break;
        }

        if (isset($parameters['siteUrl']) && !empty($parameters['siteUrl'])) {
            printf('<p>Site: <a href="%s">%s</a></p>', add_query_arg(['site' => $parameters['siteUrl']]), $parameters['siteUrl']);
        }

        return ob_get_clean();
    }
}
