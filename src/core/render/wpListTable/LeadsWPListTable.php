<?php
namespace SpatialMatchIdx\core\render\wpListTable;

use SpatialMatchIdx\core\helpers\ArrayHelper;
use SpatialMatchIdx\core\helpers\DateTimeHelper;
use SpatialMatchIdx\core\helpers\URLHelper;
use WP_List_Table;

class LeadsWPListTable extends WP_List_Table
{
    /**
     * @var int
     */
    private $itemsPerPage = 25;

    private $pageNumber = 1;

    private static $instances;

    private $time_zone;

    /**
     * @var int;
     */
    private $totalItems;

    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->time_zone = DateTimeHelper::getTimezoneString();
    }

    public static function getInstance(): LeadsWPListTable
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
            'id' => 'id',
            'name' => 'Name',
            'email'    => 'Email',
            'phone'      => 'Phone',
            'favorites' => 'Favorites',
            'offMarket' => 'Off Market',
            'searches' => 'Saved Searches',
            'createdDate' => 'Registered',
            'lastActive' => 'Last Active'
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
     * @return LeadsWPListTable $this
     */
    public function setItemsPerPage(int $value): LeadsWPListTable
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

    protected function column_default( $item, $column_name )
    {
        switch ( $column_name ) {
            case 'name':
                $avatarHtml = '';
                if (!empty($item['avatar'])) {
                    $avatarHtml = '<div class="hji-spm-idx__lead-avatar" style="background-image: url(' . $item['avatar'] . ')"></div>';
                }
                $value = empty($item[$column_name]) ? 'Anonymous' : $item[$column_name];
                $link =  URLHelper::getLinkWithParams([
                    'page',
                ], [
                    'action' => 'lead-detail',
                    'lead_id' => $item['id']
                ]);

                $actions = [
                    'edit' => sprintf('<a href="%s">Edit</a>', URLHelper::getLinkWithParams(['page'], ['action' => 'lead-detail', 'type' => 'edit', 'lead_id' => $item['id']])),
                    'delete' => sprintf('<a href="javascript:;" onclick="LeadsCRUD.removeLead(\'%s\', \'%s\', \'%s\', \'%s\');">Delete</a>', $item['id'], $item['name'], $item['email'], URLHelper::getLinkWithParams(['page'])),
                ];

                return '<a href="' . $link . '" class="hji-spm-idx__lead-name">' . $avatarHtml . $value . '</a>' . $this->row_actions($actions);
            case 'email':
                $link =  URLHelper::getLinkWithParams([
                    'page',
                ], [
                    'action' => 'lead-detail',
                    'lead_id' => $item['id']
                ]);

                return '<a href="' . $link . '">' . $item[$column_name] . '</a>';
            case 'phone':
            case 'favorites':
            case 'offMarket':
            case 'searches':
                return $item[$column_name];
            case 'createdDate':
            case 'lastActive':
                if ($item[$column_name] !== null) {
                    $date = new \DateTime('now', new \DateTimeZone('UTC'));
                    return $date->setTimestamp($item[$column_name])->setTimezone(new \DateTimeZone($this->time_zone))->format('F d, Y g:ia');
                }
                return '';
            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes.
        }
    }

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = ['id', 'avatar'];
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
     * @return LeadsWPListTable $this
     */
    public function setItems(array $items): LeadsWPListTable
    {
        $this->items = $items;

        return $this;
    }

    public function get_sortable_columns()
    {
        return [
            'name'  => ['name',false],
            'email' => ['email',false],
            'createdDate'   => ['createdDate',false],
            'lastActive' => ['lastActive', false]
        ];
    }

    public function get_views()
    {
        $filters = [
            'all' => [
                'title' => 'All',
                'class' => 'hji-spm-idx__filter',
                'input_name' => 'time_period'
            ],
            'today' => [
                'title' => 'Today',
                'class' => 'hji-spm-idx__filter',
                'input_name' => 'time_period'
            ],
            '7days' => [
                'title' => 'Last 7 days',
                'class' => 'hji-spm-idx__filter',
                'input_name' => 'time_period'
            ],
            '30days' => [
                'title' => 'Last 30 days',
                'class' => 'hji-spm-idx__filter',
                'input_name' => 'time_period'
            ],
            'thismonth' => [
                'title' => 'This Month',
                'class' => 'hji-spm-idx__filter',
                'input_name' => 'time_period'
            ],
            'custom' => [
                'title' => 'Custom',
                'class' => 'hji-spm-idx__filter custom',
                'input_name' => 'time_period'
            ],
        ];

        $timePeriod = $_GET['time_period'] ?? '';

        if (empty($timePeriod) && $this->totalItems === 0) {
            $filters = ['all' => $filters['all']];
            $activeFieldKey = 'all';
            $lastKey = 'all';
        } else {
            $lastKey = ArrayHelper::getArrayKeyLast($filters);
            $activeFieldKey = ArrayHelper::getArrayKeyFirst($filters);
            foreach ($filters as $key => $value) {
                if ($timePeriod === $key) :
                    $activeFieldKey = $key;
                endif;
            }
        }

        if ($this->totalItems > 0) {
            foreach ($filters as $key => $value) : ?>
                <?php
                $checked = '';
                $active = '';
                if ($activeFieldKey === $key) :
                    $checked = 'checked="checked"';
                    $active = ' active';
                endif;
                ?>
                <label class="<?php echo $value['class']; echo $active; ?>">
                    <input type="radio" name="<?php echo $value['input_name']; ?>" value="<?php echo $key; ?>" <?php echo $checked; ?>>
                    <?php echo $value['title']; ?>
                </label>
                <?php
                if ($lastKey !== $key) {
                    echo '&nbsp|&nbsp;';
                }
            endforeach;
        }
    }
}
