<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\DateTimeHelper;
use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadAnalyticsActionsWPListTable extends WP_List_Table
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
     * @return LeadAnalyticsActionsWPListTable
     */
    public static function getInstance(): LeadAnalyticsActionsWPListTable
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
            'user'      => __('User',       'hji-users'),
            'action'    => __('Action',     'hji-users'),
            'parameters'=> __('Parameters', 'hji-users'),
            'timestamp' => __('Date',       'hji-users'),
            'sessionId' => __('User Session','hji-users'),
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
            'timestamp' => ['timestamp', false]
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
     * @return LeadAnalyticsActionsWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadAnalyticsActionsWPListTable
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
        $siteUrl = (isset($item['site']) && !empty($item['site'])) ? $item['site'] : null;

        switch($columnName) {
            case 'user':

                $name = 'Anonymous';
                $ip   = 'N/A';

                $user = isset($item[$columnName]) ? $item[$columnName] : null;

                if (is_object($user)) {
                    $name = !empty($user->name) ? $user->name : $name;
                    $email = $user->email;
                    $url = $this->_getUserUrl($user->id);
                    $name = sprintf('<a href="%s"><strong>%s</strong></a><br />%s', $url, $name, $email);
                } else if (is_array($user)) {
                    $name   = !empty($user['name']) ? $user['name'] : $name;
                    $email  = $user['email'];
                    $url    = $this->_getUserUrl($user['id']);
                    $name   = sprintf('<a href="%s"><strong>%s</strong></a><br />%s', $url, $name, $email);
                }  else {
                    // Apparently User Object doesn't exist
                    $name = "<i>{$name}</i>";
                }

                if (isset($item['ipAddress'])) {
                    $ip = sprintf('<a href="%s">%s</a>', add_query_arg('ipAddress', $item['ipAddress']), $item['ipAddress']);
                }

                return $name . '<br />IP: ' . $ip;

            case 'action':
                if (isset($item[$columnName])) {
                    return $this->getActionTitle($item[$columnName]);
                }

                return 'N/A';

            case 'parameters':
                $parameters = json_decode($item['parameters'], true);
                unset($parameters['options']);

                $parameters = ($item['action'] === 'listings.search') ? $this->_makeFiltersUserReadable($parameters) : $parameters;


                $listingUrls = [];

                if (isset($parameters['ids']) && isset($parameters['market']))
                {
                    foreach ($parameters['ids'] as $id)
                    {
                        //$listingUrls[$id] = $siteUrl;//$this->_getListingUrl($id, $parameters['market'], $siteUrl);
                        $listingUrls[$id] = URLHelper::getListingUrlById($id, $parameters['market']);
                    }
                }

                $parameters['siteUrl'] = $siteUrl;
                $parameters['listingUrlPattern'] = 'test3';//ListingEntity::getShortUrl('%listingId%', '%market%');
                $parameters['listingUrls'] = $listingUrls;

                return $this->actionColumnTemplateView($item['action'], $parameters);

            case 'timestamp':
                $date = DateTimeHelper::toDateTime($item[$columnName]);

                return sprintf('<a href="%s">%s</a>', add_query_arg('timeframe', $item[$columnName]), $date);

            case 'sessionId':
                if (($userId = $this->_getUserId($item)) && isset($item[$columnName])) {
                    $url = URLHelper::getLinkWithParams([
                        'page',
                        'lead_id',
                        'action',
                        'item',
                    ],
                        [
                            'ssid' => $item[$columnName],
                            'item' => 'session-details',
                        ]);

                    return sprintf('<a href="%s">%s</a>', $url, 'View User Session');
                }

                return null;
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
     * @return LeadAnalyticsActionsWPListTable $this
     */
    public function setItems(array $items): LeadAnalyticsActionsWPListTable
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
            'page'      => 'hji-spatialmatch-idx-leads',
            'action'    => 'lead-detail',
            'lead_id'       => null
        ];
//https://spatialmatch-idx.gromozeka.visual/wp-admin/admin.php?page=hji-spatialmatch-idx-leads&lead_id=xu43d6c939-26f5-494b-b091-c47134fbc5cc&action=lead-detail
        if (!is_array($uid)) {
            $params['lead_id'] = $uid;
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
