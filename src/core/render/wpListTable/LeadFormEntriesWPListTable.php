<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadFormEntriesWPListTable extends WP_List_Table
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

    public function __construct($args = [])
    {
        parent::__construct($args);
    }

    /**
     * @return LeadFormEntriesWPListTable
     */
    public static function getInstance(): LeadFormEntriesWPListTable
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
        return [
            'form_title'     => __('Form', 'hji-users'),
            'parameters'     => __('Content', 'hji-users'),
            'timestamp'      => __('Date/Time', 'hji-users'),
        ];
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
     * @return LeadFormEntriesWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadFormEntriesWPListTable
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
        $params = $this->getParams($item);

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        switch($columnName)
        {
            case 'timestamp':
                return date("{$dateFormat} {$timeFormat}", $item[$columnName]);

            case 'parameters':
                $content = null;

                // All user entered values are arrays
                // For Gravity Form entries

                if (isset($params['fields'])) {
                    foreach ($params['fields'] as $field) {
                        if (!empty($field['label']) && !empty($field['value'])) {
                            $content .= "{$field['label']}: {$field['value']}<br />";
                        }
                    }
                } else {
                    foreach ($params as $label => $value) {
                        $content .= "{$label}: {$value}<br />";
                    }
                }


                if (isset($item['site']) && !empty($item['site'])) {
                    $content .= sprintf('<p>Site: <a href="%s">%s</a></p>', add_query_arg(['site' => $item['site']]), $item['site']);
                }

                return $content;
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
     * @return LeadFormEntriesWPListTable $this
     */
    public function setItems(array $items): LeadFormEntriesWPListTable
    {
        $this->items = $items;

        return $this;
    }

    public function get_items_per_page( $option, $default = 50 )
    {
        // Changing fro 20 to 50

        return parent::get_items_per_page($option, $default);
    }

    public function column_form_title($item)
    {
        $params = $this->getParams($item);

        return (isset($params['entry']['form_title'])) ? '<strong>' . $params['entry']['form_title'] . '</strong>' : '<strong>Custom Form</strong>';
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

        $today = ($timeframe === 'today') ? 'current' : 'today';
        $last7days = ($timeframe === '7days') ? 'current' : 'last7days';
        $last30days = ($timeframe === '30days') ? 'current' : 'last30days';
        $thisMonth = ($timeframe === 'thismonth') ? 'current' : 'thisMonth';
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
}
