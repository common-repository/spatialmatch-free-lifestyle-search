<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadFavoritesWPListTable extends WP_List_Table
{
    /**
     * @var int
     */
    private $items_per_page = 10;

    private $page_number = 1;

    private static $instances;

    /**
     * @var int;
     */
    private $total_items;

    public function __construct($args = [])
    {
        parent::__construct($args);
    }

    public static function getInstance(): LeadFavoritesWPListTable
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
            'images' => __('Photo', 'hji-users'),
            'id'  => __('ID', 'hji-users'),
            'listPrice' => __('Price', 'hji-users'),
            'market' => __('Market', 'hji-users'),
            'status'  => __('Status', 'hji-users'),
            'listingType'  => __('Listing Type', 'hji-users'),
            'propertyType'  => __('Property Type', 'hji-users'),
            'address'  => __('Address', 'hji-users'),
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'listPrice'  => ['listPrice', false],
        ];
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->items_per_page;
    }

    /**
     * @param int $value
     * @return LeadFavoritesWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadFavoritesWPListTable
    {
        $this->items_per_page = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->total_items;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function set_total_items(int $value)
    {
        $this->total_items = $value;

        return $this;
    }

    /**
     * @return int
     */
    public function get_page_number(): int
    {
        return $this->page_number;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function set_page_number(int $value)
    {
        $this->page_number = $value;

        return $this;
    }

    function column_default($item, $columnName)
    {
        $listing = $item['listing'];
        $url = URLHelper::getListingUrlById($listing['id'], $listing['market']);

        if(isset($item['offMarket'])) {
            $listingPageLink = '<span>%s</span>';
        } else {
            $listingPageLink = '<a href="' . $url . '" target="_blank">%s</a>';
        }

        switch($columnName) {
            case 'images':
                return isset($listing[$columnName][0]) ? sprintf($listingPageLink, '<img src="' . $listing[$columnName][0] .'?width=150" style="width: 150px;max-width: 100%;" />') : '- N/A -';

            case 'id':
            case 'listingType':
            case 'propertyType':
                return $listing[$columnName] ?? '- N/A -';

            case 'listPrice':
                return isset($listing[$columnName]) ? '$' . number_format($listing[$columnName]) : '- N/A -';

            case 'market':
                return isset($listing[$columnName]) ? esc_attr($listing[$columnName]) : '- N/A -';

            case 'status':
                if(isset($item['offMarket'])) {
                    return '<span style="color: red">OFF MARKET</span>';
                }

                return $listing[$columnName] ?? '- N/A -';

            case 'address':
                if (isset($listing[$columnName])) {
                    if (!isset($item->offMarket)) {
                        return sprintf($listingPageLink, implode(', ', (array)$listing[$columnName]));
                    }

                    return implode(', ', (array)$listing[$columnName]);

                }

                return '- N/A -';

            default:
                return print_r($item, true) ; //Show the whole array for troubleshooting purposes
        }
    }

    public function column_id($item)
    {
        $actions = [
            'delete'      => sprintf('<a href="javascript:;" onclick="LeadsCRUD.removeFavorite(\'%s\', \'%s\', \'%s\', \'%s\');">Delete</a>', $item['id'], $item['listing']['id'], esc_attr(addslashes($item['listing']['market'])), $_GET['lead_id'])
        ];

        return sprintf('%1$s %2$s', $item['listing']['id'], $this->row_actions($actions) );
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = [];
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = [$columns, $hidden, $sortable];

        if ($this->total_items > 0) {
            $this->set_pagination_args([
                'total_items' => $this->getTotalItems(),                  //WE have to calculate the total number of items
                'per_page'    => $this->getItemsPerPage()               //WE have to determine how many items to show on a page
            ]);
        }
    }

    /**
     * @param array $items
     * @return LeadFavoritesWPListTable $this
     */
    public function set_items(array $items): LeadFavoritesWPListTable
    {
        $this->items = $items;

        return $this;
    }

    public function get_items_per_page($option, $default = 50)
    {
        // Changing fro 20 to 50

        return parent::get_items_per_page($option, $default);
    }
}
