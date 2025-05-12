<?php

/**
 * Plugin Name: WP OAuth Debugger
 * Plugin URI: https://github.com/azzuwayed/wp-oauth-debugger
 * Description: A comprehensive debugging and monitoring tool for OAuth implementations in WordPress.
 * Version: 1.1.1
 * Requires at least: 6.5
 * Requires PHP: 8.3
 * Author: Abdullah Alzuwayed
 * Author URI: https://github.com/azzuwayed
 * Author Email: azzuwayed@gmail.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-oauth-debugger
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/azzuwayed/wp-oauth-debugger
 * Update URI: https://github.com/azzuwayed/wp-oauth-debugger
 *
 * @package WP_OAuth_Debugger
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('WP_OAUTH_DEBUGGER_VERSION', '1.1.1');
define('WP_OAUTH_DEBUGGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_OAUTH_DEBUGGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_OAUTH_DEBUGGER_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Fallback autoloader for critical plugin classes.
 * This ensures core functionality works even if Composer's autoloader fails.
 *
 * @param string $class The fully-qualified class name.
 */
function oauth_debugger_fallback_autoloader($class) {
    // Only handle our plugin's classes
    if (strpos($class, 'WP_OAuth_Debugger\\') !== 0) {
        return;
    }

    // Convert namespace to file path
    $class_path = str_replace(
        ['WP_OAuth_Debugger\\', '\\'],
        ['', DIRECTORY_SEPARATOR],
        $class
    );

    // Convert class name to file name
    $file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR .
        strtolower($class_path) . '.php';

    // Try both case-sensitive and case-insensitive paths
    if (file_exists($file)) {
        require_once $file;
        return;
    }

    // Try alternative case variations
    $alt_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR .
        $class_path . '.php';
    if (file_exists($alt_file)) {
        require_once $alt_file;
        return;
    }
}

// Register fallback autoloader
spl_autoload_register('oauth_debugger_fallback_autoloader');

/**
 * Load the Composer autoloader with fallback mechanism.
 *
 * @return bool True if autoloader was loaded successfully, false otherwise.
 */
function oauth_debugger_load_autoloader() {
    $autoload_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';

    if (!file_exists($autoload_file)) {
        error_log('OAuth Debugger: Composer autoloader not found at ' . $autoload_file);
        return false;
    }

    try {
        require_once $autoload_file;

        // Verify autoloader is working
        if (!class_exists('Composer\Autoload\ClassLoader')) {
            error_log('OAuth Debugger: Composer autoloader loaded but ClassLoader not found');
            return false;
        }

        // Test loading a core class
        if (!class_exists('WP_OAuth_Debugger\Core\Core')) {
            error_log('OAuth Debugger: Failed to load Core class through Composer autoloader');
            return false;
        }

        return true;
    } catch (\Exception $e) {
        error_log('OAuth Debugger: Error loading Composer autoloader: ' . $e->getMessage());
        return false;
    }
}

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
                <p><strong><?php _e('WP OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong> <?php echo esc_html($error); ?></p>
            </div>
        <?php
        }
    }

    // Display warnings
    if (!empty($validation_results['warnings'])) {
        foreach ($validation_results['warnings'] as $warning) {
        ?>
            <div class="notice notice-warning">
                <p><strong><?php _e('WP OAuth Debugger Warning:', 'wp-oauth-debugger'); ?></strong> <?php echo esc_html($warning); ?></p>
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
    error_log('WP OAuth Debugger: Starting activation...');

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
        error_log('WP OAuth Debugger: Activation failed due to validation errors');
        return;
    }

    // If we get here, validation passed, proceed with activation
    error_log('WP OAuth Debugger: Validation passed, proceeding with activation');

    try {
        // Load autoloader
        if (file_exists(WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php')) {
            require_once WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';
        } else {
            throw new Exception('Composer autoloader not found');
        }

        // Run the actual activation
        WP_OAuth_Debugger\Core\Activator::activate();
        error_log('WP OAuth Debugger: Activation completed successfully');
    } catch (Exception $e) {
        error_log('WP OAuth Debugger: Activation error: ' . $e->getMessage());
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

    // Initialize update checker
    if (class_exists('WP_OAuth_Debugger\Core\UpdateChecker')) {
        WP_OAuth_Debugger\Core\UpdateChecker::init();
    }

    // Try to load Composer autoloader
    if (!oauth_debugger_load_autoloader()) {
        add_action('admin_notices', function () {
        ?>
            <div class="notice notice-error">
                <p><strong><?php _e('WP OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong>
                    <?php _e('There was a problem loading the plugin autoloader. The plugin will attempt to continue with limited functionality.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        <?php
        });
    }

    try {
        // Initialize the plugin
        if (class_exists('WP_OAuth_Debugger\Core\Core')) {
            $plugin = new WP_OAuth_Debugger\Core\Core();
            $plugin->run();
        } else {
            throw new Exception('Core class not found. Plugin initialization failed.');
        }
    } catch (Exception $e) {
        error_log('WP OAuth Debugger Error: ' . $e->getMessage());
        add_action('admin_notices', function () use ($e) {
        ?>
            <div class="notice notice-error">
                <p><strong><?php _e('WP OAuth Debugger Error:', 'wp-oauth-debugger'); ?></strong>
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
