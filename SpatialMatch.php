<?php
/**
 * Bootstrap file
 *
 * Plugin Name:         SpatialMatch IDX
 * Description:         SpatialMatch is a map-based lifestyle IDX search solution. Offering property search and neighborhood exploration functionality. Geospatial information with: neighborhood boundaries, demographics, schools, points of interest and more.
 * Version:             3.0.9
 * Requires at least:   5.0.0
 * Requires PHP:        7.4.0
 * Author:              Home Junction
 * Author URI:          https://www.homejunction.com/
 * License:             GPLv2
 * Text Domain:         spatialmatch-idx
 *
 * @package     SpatialMatchIdx
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

use SpatialMatchIdx\SpatialMatchIdx;

if (version_compare(phpversion(), '7.4.0', '<=')) {

    /**
     * Display the notice after deactivation.
     *
     * @since 3.0.0
     */
    function spatialMatchIdxPhpNotice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                echo wp_kses(
                    __('The minimum version of PHP is <strong>7.4.0</strong>. Please update the PHP on your server and try again.', 'plugin_name'),
                    [
                        'strong' => [],
                    ]
                );
                ?>
            </p>
        </div>

        <?php
        // In case this is on plugin activation.
        if (isset($_GET['activate'])) { //phpcs:ignore
            unset($_GET['activate']); //phpcs:ignore
        }
    }

    add_action('admin_notices', 'spatialMatchIdxPhpNotice');

    // Don't process the plugin code further.
    return;
}

if (!defined('SPATIALMATCH_IDX_DEBUG')) {
    /**
     * Enable plugin debug mod.
     */
    define('SPATIALMATCH_IDX_DEBUG', false);
}
/**
 * Path to the plugin root directory.
 */
define('SPATIALMATCH_IDX_PATH', plugin_dir_path(__FILE__));
/**
 * Url to the plugin root directory.
 */
define('SPATIALMATCH_IDX_URL', plugin_dir_url(__FILE__));

/**
 * Run plugin function.
 *
 * @since 3.0.0
 *
 * @throws Exception If something went wrong.
 */
function runSpatialMatchIdx() {
    require_once SPATIALMATCH_IDX_PATH . 'vendor/autoload.php';

    $spatialMatchIdx = new SpatialMatchIdx();
    $spatialMatchIdx->run();
}

function registerActivationHook() {
    require_once SPATIALMATCH_IDX_PATH . 'vendor/autoload.php';

    $spatialMatchIdx = new SpatialMatchIdx();
    $spatialMatchIdx->registerActivationHook();
}

add_action('plugins_loaded', 'runSpatialMatchIdx', 90);

register_activation_hook(__FILE__, 'registerActivationHook');


