<?php

namespace SpatialMatchIdx\front;

use SpatialMatchIdx\core\compliances\ComplianceManager;
use SpatialMatchIdx\core\virtualPages\front\VirtualPagesManager;
use SpatialMatchIdx\front\compliances\compliancesMarkets\NnerenCompliance;
use SpatialMatchIdx\front\compliances\compliancesMarkets\NwmlsCompliance;
use SpatialMatchIdx\front\compliances\compliancesMarkets\TexasCompliance;
use SpatialMatchIdx\front\pages\virtualPages\app\IdxAppPage;
use SpatialMatchIdx\front\pages\virtualPages\compliances\NwmlsComplianceVirtualPage;

class Front {
    public function load()
    {
        $this->registerCompliances();
        $this->registerVirtualPages();
        $this->hooks();
    }

    public function registerCompliances()
    {
        $compliancesManager = ComplianceManager::getInstance();
        $compliancesManager->addCompliance(new TexasCompliance());
        $compliancesManager->addCompliance(new NwmlsCompliance());
        $compliancesManager->addCompliance(new NnerenCompliance());
    }

    public function registerVirtualPages()
    {
        $virtualPagesManager = VirtualPagesManager::getInstance();
        $virtualPagesManager->addPage(new NwmlsComplianceVirtualPage());
        $virtualPagesManager->addPage(new IdxAppPage());
    }


    /**
     * Init hooks
     *
     * @since 3.0.0
     */
    public function hooks()
    {
        add_action('init', [$this, 'initIdxApplication']);
    }

    public function initIdxApplication()
    {
        add_filter('the_posts', [$this,'initVirtualPage'], -10);
    }


    /**
     * fly_page
     * the Money function that catches the request and returns the page as if it was retrieved from the database
     * @param  array $posts
     * @return array
     */
    public function initVirtualPage($posts)
    {
        $result = VirtualPagesManager::getInstance()->showVirtualPage($_SERVER['REQUEST_URI']);

        if (!$result) {
            return $posts;
        }
    }

}
