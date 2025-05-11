<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Require the main plugin file to get access to constants and functions
require_once plugin_dir_path(__FILE__) . 'includes/class-debug-helper.php';

/**
 * Recursively remove a directory and its contents.
 *
 * @param string $dir Directory path
 * @return bool
 */
function oauth_debug_remove_directory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!oauth_debug_remove_directory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }

    return rmdir($dir);
}

try {
    // Delete plugin options
    $options = array(
        'oauth_debug_version',
        'oauth_debug_log_level',
        'oauth_debug_log_retention',
        'oauth_debug_auto_cleanup',
        'oauth_debug_security_scan_interval',
        'oauth_debug_api_key',
        'oauth_debug_enable_public_panel',
        'oauth_debug_allowed_roles',
        'oauth_debug_rate_limit',
        'oauth_debug_rate_limit_window',
        'oauth_debug_deactivated_at',
        'oauth_debug_temporary_debug_mode',
        'oauth_debug_temporary_log_level'
    );

    foreach ($options as $option) {
        delete_option($option);
    }

    // Delete plugin tables
    global $wpdb;
    $tables = array(
        $wpdb->prefix . 'oauth_debug_logs',
        $wpdb->prefix . 'oauth_debug_settings'
    );

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }

    // Delete transients
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->options WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_oauth_debug_') . '%',
            $wpdb->esc_like('_transient_timeout_oauth_debug_') . '%'
        )
    );

    // Delete log files
    $log_dir = WP_CONTENT_DIR . '/oauth-debug-logs';
    if (file_exists($log_dir)) {
        oauth_debug_remove_directory($log_dir);
    }

    // Clear any scheduled events
    wp_clear_scheduled_hook('oauth_debug_cleanup_logs');
    wp_clear_scheduled_hook('oauth_debug_security_scan');

    // Flush rewrite rules
    flush_rewrite_rules();

} catch (Exception $e) {
    // Log the error if possible
    if (function_exists('error_log')) {
        error_log('OAuth Debugger uninstall error: ' . $e->getMessage());
    }
} 