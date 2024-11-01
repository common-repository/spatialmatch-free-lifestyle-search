<?php

namespace SpatialMatchIdx\core\virtualPages\front;

class VirtualPagesManager
{
    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @var array
     */
    private $virtualPages = [];

    private function __construct()
    {
    }

    /**
     * @return VirtualPagesManager
     */
    public static function getInstance(): VirtualPagesManager
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function addPage(VirtualPageInterface $virtualPage)
    {
        $this->virtualPages[get_class($virtualPage)] = $virtualPage;
    }

    /**
     * @param string $slug
     * @return false
     */
    public function showVirtualPage(string $slug)
    {
        $slug = trim($slug, '/');

        foreach ($this->virtualPages as $page) {
            if ($page->getSlug() === $slug) {
                $page->execute();
            }
        }

        return false;
    }
}
