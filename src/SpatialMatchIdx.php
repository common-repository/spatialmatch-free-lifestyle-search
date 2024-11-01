<?php

namespace SpatialMatchIdx;

use SpatialMatchIdx\front\Front;
use SpatialMatchIdx\admin\PluginAdmin;

class SpatialMatchIdx {

    /**
     * Plugin slug
     *
     * @since 3.0.0
     */
    const SLUG = 'hji-spatialmatch-idx';

    /**
     * Plugin version
     *
     * @since 3.0.0
     */
    const VERSION = '3.0.0';

    public function run()
    {
        if ($this->checkIfRIDXIsActive()) {
            add_action('admin_notices',  function () {
                $message = '<strong>SpatialMatch IDX</strong> plugin cannot be used along with <strong>Responsive IDX</strong>.
                 Latest version of <strong>Responsove IDX</strong> comes with SpatialMatch already included.';

                echo '<div class="notice notice-error is-dismissible"> <p>'. $message .'</p></div>';
            });

            return;
        }

        is_admin()
            ? $this->runAdmin()
            : $this->runFront();
    }

    public function runAdmin()
    {
        PluginAdmin::getInstance()->load();
    }

    private function runFront()
    {
        (new Front())->load();
    }

    private function checkIfRIDXIsActive()
    {
        include_once(ABSPATH.'wp-admin/includes/plugin.php');

        if (is_plugin_active('hji-responsive-idx/responsive-idx.php')) {
            return true;
        }

        return false;
    }

    public function registerActivationHook()
    {
        PluginAdmin::getInstance()->activatePlugin();
    }
}
