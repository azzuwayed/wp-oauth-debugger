<?php

/**
 * Plugin Name: OAuth Debugger
 * Plugin URI: https://github.com/yourusername/wp-oauth-debugger
 * Description: A comprehensive debugging and monitoring tool for OAuth implementations in WordPress.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Abdullah Alzuwayed
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-oauth-debugger
 * Domain Path: /languages
 *
 * @package WP_OAuth_Debugger
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('WP_OAUTH_DEBUGGER_VERSION', '1.0.0');
define('WP_OAUTH_DEBUGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_OAUTH_DEBUGGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OAUTH_DEBUGGER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Composer autoloader
if (file_exists(WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    error_log('OAuth Debugger: vendor/autoload.php not found at: ' . WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php');
    add_action('admin_notices', function () {
?>
        <div class="notice notice-error">
            <p><?php _e('OAuth Debugger requires Composer dependencies to be installed. Please run "composer install" in the plugin directory.', 'wp-oauth-debugger'); ?></p>
        </div>
<?php
    });
    return;
}

/**
 * The code that runs during plugin activation.
 */
function activate_oauth_debugger() {
    WP_OAuth_Debugger\Core\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_oauth_debugger() {
    WP_OAuth_Debugger\Core\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_oauth_debugger');
register_deactivation_hook(__FILE__, 'deactivate_oauth_debugger');

/**
 * Begins execution of the plugin.
 */
function run_oauth_debugger() {
    // Load plugin text domain
    load_plugin_textdomain(
        'wp-oauth-debugger',
        false,
        dirname(WP_OAUTH_DEBUGGER_PLUGIN_BASENAME) . '/languages'
    );

    // Initialize the plugin
    $plugin = new WP_OAuth_Debugger\Core\Core();
    $plugin->run();
}

// Run the plugin
add_action('plugins_loaded', 'run_oauth_debugger');

/**
 * Helper function to get plugin instance.
 *
 * @return WP_OAuth_Debugger\Core\Core
 */
function oauth_debugger() {
    static $instance = null;
    if ($instance === null) {
        $instance = new WP_OAuth_Debugger\Core\Core();
    }
    return $instance;
}

/**
 * Helper function to get debug helper instance.
 *
 * @return WP_OAuth_Debugger\Debug\DebugHelper
 */
function oauth_debug_helper() {
    static $instance = null;
    if ($instance === null) {
        $instance = new WP_OAuth_Debugger\Debug\DebugHelper();
    }
    return $instance;
}
