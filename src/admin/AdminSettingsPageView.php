<?php

namespace SpatialMatchIdx\admin;

use SpatialMatchIdx\admin\sections\TabInterface;
use SpatialMatchIdx\core\render\View;

class AdminSettingsPageView
{
    private function getTabList(string $pluginPageUrl, array $tabs, string $activeTabSlug): string
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

        return View::renderString('admin/partial/tab-list.phtml', ['tabs' => $tabsData, 'pluginPageUrl' => $pluginPageUrl]);
    }

    private function getTabPanel(TabInterface $tab, array $data, string $activeTabPanelClass): string
    {
        return View::renderString(
            'admin/partial/tab-panels/settings/' . $tab->getSlug() . '.phtml',
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
     * @return void
     */
    public function getTabPage(string $pluginPageUrl, array $tabs, array  $data, string $activeTabSlug)
    {
        $tabList = $this->getTabList($pluginPageUrl, $tabs, $activeTabSlug);
        $tabPanel = '';

        /** @var $tab TabInterface */
        foreach ($tabs as $tab) {
            if ($activeTabSlug !== $tab->getSlug()) {
                continue;
            }

            $activeTabPanelClass = 'active';

            $tabPanel = $this->getTabPanel($tab, $data, $activeTabPanelClass);
        }

        View::render('admin/setting-page.phtml', ['context' => $tabList . $tabPanel]);
    }

    public function getPage(string $template, array $options)
    {
        View::render($template, $options);
    }
}
