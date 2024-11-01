<?php

namespace SpatialMatchIdx\admin;

use SpatialMatchIdx\admin\sections\TabInterface;
use SpatialMatchIdx\core\render\View;

class LeadPageView
{
    /**
     * @param string $pluginPageUrl
     * @param array $tabs
     * @param string $activeTabSlug
     * @param array $counters
     * @return string
     */
    private function getTabList(string $pluginPageUrl, array $tabs, string $activeTabSlug, array $counters = []): string
    {
        /** @var $tab TabInterface */
        foreach ($tabs as $tab) {
            $class= '';
            if ($tab->getSlug() === $activeTabSlug) {
                $class = 'nav-tab-active';
            }

            $tabsData[] = [
                'slug' => $tab->getSlug(),
                'title' => $tab->getTitle(),
                'class' => $class,
            ];
        }

        if (isset($_GET['lead_id'])) {
            $pluginPageUrl .= '&lead_id=' . $_GET['lead_id'];
        }

        return View::renderString('admin/partial/tab-list.phtml', ['tabs' => $tabsData, 'pluginPageUrl' => $pluginPageUrl, 'counters' => $counters]);
    }

    /**
     * @param TabInterface $tab
     * @param array $data
     * @param string $activeTabPanelClass
     * @return string
     */
    private function getTabPanel(TabInterface $tab, array $data, string $activeTabPanelClass): string
    {
        return View::renderString(
            'admin/partial/tab-panels/lead/' . $tab->getSlug() . '.phtml',
            [
                'context' => [
                    'slug' => $tab->getSlug(),
                    'data' => $data,
                    'class' => $activeTabPanelClass
        ]]);
    }

    /**
     * @param string $pluginPageUrl
     * @param array $tabs
     * @param array $data
     * @param string $activeTabSlug
     * @param array $tabsCounters
     * @return void
     */
    public function getTabPage(string $pluginPageUrl, array $tabs, array  $data, string $activeTabSlug, array $tabsCounters = [])
    {
        $tabList = $this->getTabList($pluginPageUrl, $tabs, $activeTabSlug, $tabsCounters);
        $tabPanel = '';

        /** @var $tab TabInterface */
        foreach ($tabs as $tab) {
            if ($activeTabSlug !== $tab->getSlug()) {
                continue;
            }

            $activeTabPanelClass = 'active';

            $tabPanel = $this->getTabPanel($tab, $data, $activeTabPanelClass);
        }

        View::render('admin/lead-page.phtml', ['context' => $tabList . $tabPanel, 'data' => $data]);
    }

    /**
     * @param string $template
     * @param array $options
     */
    public function getPage(string $template, array $options)
    {
        View::render($template, $options);
    }
}
