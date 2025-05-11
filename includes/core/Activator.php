<?php
namespace WP_OAuth_Debugger\Core;

/**
 * Fired during plugin activation.
 */
class Activator {
    /**
     * Activate the plugin.
     */
    public static function activate() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('OAuth Debugger requires PHP 7.2 or higher.', 'wp-oauth-debugger'));
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('OAuth Debugger requires WordPress 5.0 or higher.', 'wp-oauth-debugger'));
        }

        // Get current version
        $current_version = get_option('oauth_debug_version', '0.0.0');
        
        // Create or upgrade database tables
        self::create_tables();
        
        // Create or update options
        self::create_options();
        
        // Create log directory
        self::create_log_directory();
        
        // Schedule events
        self::schedule_events();

        // Update version
        update_option('oauth_debug_version', WP_OAUTH_DEBUGGER_VERSION);

        // Run version-specific upgrades
        self::upgrade_version($current_version);

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create necessary database tables.
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // OAuth Debug Logs table
        $table_name = $wpdb->prefix . 'oauth_debug_logs';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext,
            PRIMARY KEY  (id),
            KEY level (level),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        // OAuth Debug Settings table
        $settings_table = $wpdb->prefix . 'oauth_debug_settings';
        $sql .= "CREATE TABLE IF NOT EXISTS $settings_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(50) NOT NULL,
            setting_value longtext NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create default plugin options.
     */
    private static function create_options() {
        $default_options = array(
            'oauth_debug_version' => WP_OAUTH_DEBUGGER_VERSION,
            'oauth_debug_log_level' => 'info',
            'oauth_debug_log_retention' => 7, // days
            'oauth_debug_auto_cleanup' => true,
            'oauth_debug_security_scan_interval' => 24, // hours
            'oauth_debug_api_key' => wp_generate_password(32, false),
            'oauth_debug_enable_public_panel' => false,
            'oauth_debug_allowed_roles' => array('administrator'),
            'oauth_debug_rate_limit' => 60, // requests per minute
            'oauth_debug_rate_limit_window' => 60, // seconds
        );

        foreach ($default_options as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }

    /**
     * Create log directory and protection files.
     */
    private static function create_log_directory() {
        $log_dir = WP_CONTENT_DIR . '/oauth-debug-logs';

        if (!file_exists($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                error_log('OAuth Debugger: Failed to create log directory at ' . $log_dir);
                return;
            }
        }

        // Create .htaccess to protect logs
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all";
            if (file_put_contents($htaccess_file, $htaccess_content) === false) {
                error_log('OAuth Debugger: Failed to create .htaccess file at ' . $htaccess_file);
            }
        }

        // Create index.php to prevent directory listing
        $index_file = $log_dir . '/index.php';
        if (!file_exists($index_file)) {
            if (file_put_contents($index_file, '<?php // Silence is golden') === false) {
                error_log('OAuth Debugger: Failed to create index.php at ' . $index_file);
            }
        }
    }

    /**
     * Schedule plugin events.
     */
    private static function schedule_events() {
        if (!wp_next_scheduled('oauth_debug_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'oauth_debug_cleanup_logs');
        }

        if (!wp_next_scheduled('oauth_debug_security_scan')) {
            wp_schedule_event(time(), 'daily', 'oauth_debug_security_scan');
        }
    }

    /**
     * Handle version-specific upgrades.
     *
     * @param string $current_version The current version being upgraded from.
     */
    private static function upgrade_version($current_version) {
        // Example version upgrade logic
        if (version_compare($current_version, '1.1.0', '<')) {
            // Add new options for version 1.1.0
            add_option('oauth_debug_new_feature', 'default_value');
        }

        if (version_compare($current_version, '1.2.0', '<')) {
            // Modify database structure for version 1.2.0
            global $wpdb;
            $table_name = $wpdb->prefix . 'oauth_debug_logs';
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN IF NOT EXISTS source varchar(50) AFTER level");
        }
    }
} 