<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Load WordPress database functions if not already loaded
if (!function_exists('get_option')) {
    require_once(ABSPATH . 'wp-includes/option.php');
}

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
        return @unlink($dir);
    }

    $items = @scandir($dir);
    if ($items === false) {
        return false;
    }

    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            if (!oauth_debug_remove_directory($path)) {
                return false;
            }
        } else {
            if (!@unlink($path)) {
                return false;
            }
        }
    }

    return @rmdir($dir);
}

/**
 * Safely delete a database table.
 *
 * @param wpdb $wpdb WordPress database object
 * @param string $table Table name
 * @return bool
 */
function oauth_debug_safe_drop_table($wpdb, $table) {
    // Verify table exists before attempting to drop
    $table_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = %s",
        $table
    ));

    if (!$table_exists) {
        return true;
    }

    // Attempt to drop the table
    $result = $wpdb->query("DROP TABLE IF EXISTS `" . esc_sql($table) . "`");
    return $result !== false;
}

/**
 * Log an uninstall error.
 *
 * @param string $message Error message
 */
function oauth_debug_log_uninstall_error($message) {
    if (function_exists('error_log')) {
        error_log('OAuth Debugger uninstall error: ' . $message);
    }
}

try {
    global $wpdb;

    // Start transaction if supported
    $wpdb->query('START TRANSACTION');

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
        'oauth_debug_temporary_log_level',
        'oauth_debugger_validation_results' // Add any missing options
    );

    $failed_options = array();
    foreach ($options as $option) {
        if (get_option($option) !== false) {
            if (!delete_option($option)) {
                $failed_options[] = $option;
            }
        }
    }

    if (!empty($failed_options)) {
        throw new Exception(sprintf(
            'Failed to delete options: %s',
            implode(', ', $failed_options)
        ));
    }

    // Delete plugin tables
    $tables = array(
        $wpdb->prefix . 'oauth_debug_logs',
        $wpdb->prefix . 'oauth_debug_settings',
        $wpdb->prefix . 'oauth_debug_tokens' // Add any missing tables
    );

    $failed_tables = array();
    foreach ($tables as $table) {
        if (!oauth_debug_safe_drop_table($wpdb, $table)) {
            $failed_tables[] = $table;
        }
    }

    if (!empty($failed_tables)) {
        throw new Exception(sprintf(
            'Failed to drop tables: %s',
            implode(', ', $failed_tables)
        ));
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
        if (!oauth_debug_remove_directory($log_dir)) {
            throw new Exception('Failed to remove log directory: ' . $log_dir);
        }
    }

    // Clear any scheduled events
    $events = array(
        'oauth_debug_cleanup_logs',
        'oauth_debug_security_scan',
        'oauth_debug_token_cleanup' // Add any missing events
    );

    foreach ($events as $event) {
        wp_clear_scheduled_hook($event);
    }

    // Remove any custom capabilities
    $roles = array('administrator', 'editor');
    $capabilities = array(
        'manage_oauth_debugger',
        'view_oauth_debugger',
        'manage_oauth_debugger_settings'
    );

    foreach ($roles as $role) {
        $role_obj = get_role($role);
        if ($role_obj) {
            foreach ($capabilities as $cap) {
                $role_obj->remove_cap($cap);
            }
        }
    }

    // Flush rewrite rules
    flush_rewrite_rules();

    // Commit transaction if supported
    $wpdb->query('COMMIT');
} catch (Exception $e) {
    // Rollback transaction if supported
    $wpdb->query('ROLLBACK');

    // Log the error
    oauth_debug_log_uninstall_error($e->getMessage());

    // If we're in admin, display the error
    if (is_admin()) {
        add_action('admin_notices', function () use ($e) {
?>
            <div class="notice notice-error">
                <p><strong><?php _e('OAuth Debugger Uninstall Error:', 'wp-oauth-debugger'); ?></strong>
                    <?php echo esc_html($e->getMessage()); ?>
                </p>
            </div>
<?php
        });
    }
}
