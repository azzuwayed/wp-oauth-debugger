<?php

/**
 * Plugin Name: OAuth Debugger
 * Plugin URI:
 * Description: A comprehensive debugging and monitoring tool for OAuth implementations in WordPress.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Author: Abdullah Alzuwayed
 * Author URI:
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

/**
 * Display admin notices for plugin validation.
 */
function oauth_debugger_admin_notices() {
    $validation_results = get_option('oauth_debugger_validation_results', array());
    if (empty($validation_results)) {
        return;
    }

    // Display errors
    if (!empty($validation_results['errors'])) {
        foreach ($validation_results['errors'] as $error) {
?>
            <div class="notice notice-error">
                <p><strong><?php _e('OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong> <?php echo esc_html($error); ?></p>
            </div>
        <?php
        }
    }

    // Display warnings
    if (!empty($validation_results['warnings'])) {
        foreach ($validation_results['warnings'] as $warning) {
        ?>
            <div class="notice notice-warning">
                <p><strong><?php _e('OAuth Debugger Warning:', 'wp-oauth-debugger'); ?></strong> <?php echo esc_html($warning); ?></p>
            </div>
        <?php
        }
    }

    // Clear validation results after displaying
    delete_option('oauth_debugger_validation_results');
}
add_action('admin_notices', 'oauth_debugger_admin_notices');

/**
 * The code that runs during plugin activation.
 */
function activate_oauth_debugger() {
    error_log('OAuth Debugger: Starting activation...');

    // Load the validator class directly since autoloader might not be available yet
    require_once WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'includes/Core/Validator.php';

    // Run validation
    $validator = new WP_OAuth_Debugger\Core\Validator();
    $is_valid = $validator->validate();

    // Store validation results
    update_option('oauth_debugger_validation_results', array(
        'errors' => $validator->get_errors(),
        'warnings' => $validator->get_warnings()
    ));

    // If validation failed, deactivate the plugin
    if (!$is_valid) {
        deactivate_plugins(plugin_basename(__FILE__));
        error_log('OAuth Debugger: Activation failed due to validation errors');
        return;
    }

    // If we get here, validation passed, proceed with activation
    error_log('OAuth Debugger: Validation passed, proceeding with activation');

    try {
        // Load autoloader
        if (file_exists(WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';
        } else {
            throw new Exception('Composer autoloader not found');
        }

        // Run the actual activation
        WP_OAuth_Debugger\Core\Activator::activate();
        error_log('OAuth Debugger: Activation completed successfully');
    } catch (Exception $e) {
        error_log('OAuth Debugger: Activation error: ' . $e->getMessage());
        deactivate_plugins(plugin_basename(__FILE__));
        update_option('oauth_debugger_validation_results', array(
            'errors' => array(sprintf(
                __('Activation failed: %s', 'wp-oauth-debugger'),
                $e->getMessage()
            )),
            'warnings' => array()
        ));
    }
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

    // Ensure autoloader is loaded
    $autoload_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';
    if (!file_exists($autoload_file)) {
        add_action('admin_notices', function () {
        ?>
            <div class="notice notice-error">
                <p><strong><?php _e('OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong>
                    <?php _e('Composer dependencies are not installed. Please run "composer install" in the plugin directory.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        <?php
        });
        return;
    }

    try {
        require_once $autoload_file;

        // Verify autoloader is working
        if (!class_exists('Composer\Autoload\ClassLoader')) {
            throw new Exception('Composer autoloader is not working correctly');
        }

        // Initialize the plugin
        if (class_exists('WP_OAuth_Debugger\Core\Core')) {
            $plugin = new WP_OAuth_Debugger\Core\Core();
            $plugin->run();
        } else {
            throw new Exception('Core class not found. Autoloader may not be working correctly.');
        }
    } catch (Exception $e) {
        error_log('OAuth Debugger Error: ' . $e->getMessage());
        add_action('admin_notices', function () use ($e) {
        ?>
            <div class="notice notice-error">
                <p><strong><?php _e('OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong>
                    <?php echo esc_html($e->getMessage()); ?>
                </p>
            </div>
<?php
        });
    }
}

// Run the plugin
add_action('plugins_loaded', 'run_oauth_debugger', 20); // Increased priority to ensure dependencies are loaded

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
