<?php
namespace SpatialMatchIdx\admin\sections;


class TabsContainer
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $tabs = [];

    private function __construct()
    {
    }

    /**
     * @return TabsContainer
     */
    public static function getInstance(): TabsContainer
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function addTab(TabInterface $tab)
    {
        $this->tabs[] = $tab;
    }

    /**
     * @return TabInterface[]
     */
    public function getTabs(): array
    {
        return $this->tabs;
    }
}
