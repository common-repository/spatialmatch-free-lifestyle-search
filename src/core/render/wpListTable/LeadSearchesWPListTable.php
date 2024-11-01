<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadSearchesWPListTable extends WP_List_Table
{
    /**
     * @var int
     */
    private $itemsPerPage = 10;

    private $pageNumber = 1;

    private static $instances;

    /**
     * @var int;
     */
    private $totalItems;

    public function __construct($args = [])
    {
        parent::__construct($args);
    }

    public static function getInstance(): LeadSearchesWPListTable
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

     public function get_columns(): array
    {
        return [
            'name'      => __('Name', 'hji-users'),
            'market'    => __('Market', 'hji-users'),
            'summary'   => __('Search Criteria', 'hji-users'),
            'alerts'    => __('Alerts', 'hji-users'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'name'  => ['name', false],
            'market'  => ['market', false],
            'alerts'  => ['alerts', false],
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
     * @return LeadSearchesWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadSearchesWPListTable
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

    function column_default($item, $columnName)
    {
        switch($columnName)
        {
            case 'name':
                return $item[$columnName];

            case 'market':
                return strtoupper($item[$columnName]);

            case 'summary':
                $output = '- N/A -';
                if (!empty($item[$columnName]))
                {
                    $output = '';
                    foreach($item[$columnName] as $filter)
                    {
                        $label = $filter['label'] ?? null;
                        $value = $filter['value'] ?? null;

                        if ($label && $value)
                            $output .= '<div>' . esc_attr($label) . ': ' . esc_attr($value) . '</div>';
                    }
                }

                return $output;

            case 'alerts':
                return '<input class="alert-switch" 
                    value="' . $item['id'] . '" 
                    type="checkbox" 
                    data-frequency="' . ($item['frequency'] ?? null) . '" 
                    ' . checked(($item[$columnName] ?? null), true, false) . ' >'
                    ;

            default:
                return print_r($item, true) ; //Show the whole array for troubleshooting purposes
        }
    }

    function column_name($item)
    {
        $searchURL = !empty($item['searchUrl']) ? esc_attr($item['searchUrl']) : null;

        $urlSplited = explode('#', $searchURL);

        $searchURL = (isset($urlSplited[1])) ? URLHelper::getMapsearchUrl() . '#' . $urlSplited[1] : $searchURL;

        $link = !is_null($searchURL) ? sprintf('<a href="%s" target="_blank">%s</a>', $searchURL, esc_attr($item['name'])) : esc_attr($item['name']);

        $actions = [
            'delete' => sprintf('<a href="javascript:;" onclick="LeadsCRUD.removeSearch(\'%s\', \'%s\', \'%s\');">Delete</a>', $item['id'], esc_attr(addslashes($item['name'])), $_GET['lead_id'])
        ];

        return sprintf('%1$s %2$s', $link, $this->row_actions($actions));
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
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
     * @return LeadSearchesWPListTable $this
     */
    public function set_items(array $items): LeadSearchesWPListTable
    {
        $this->items = $items;

        return $this;
    }

    public function get_items_per_page( $option, $default = 50 )
    {
        // Changing fro 20 to 50

        return parent::get_items_per_page($option, $default);
    }
}
