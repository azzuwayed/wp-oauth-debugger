<?php

namespace WP_OAuth_Debugger\Core;

/**
 * Fired during plugin activation.
 */
class Activator {
    /**
     * Activate the plugin.
     *
     * @throws \Exception If activation fails
     */
    public static function activate() {
        try {
            // Create or update options first
            self::create_options();

            // Create log directory with proper permissions
            self::create_log_directory();

            // Schedule events
            self::schedule_events();

            // Update version
            update_option('oauth_debug_version', WP_OAUTH_DEBUGGER_VERSION);

            // Flush rewrite rules
            flush_rewrite_rules();
        } catch (\Exception $e) {
            error_log('OAuth Debugger Activation Error: ' . $e->getMessage());
            throw $e; // Re-throw to be caught by the main activation function
        }
    }

    /**
     * Create database tables manually.
     * This is called from the admin interface when the user clicks the setup button.
     *
     * @return array Array containing status and message
     */
    public static function setup_database() {
        try {
            self::create_tables();
            return array(
                'success' => true,
                'message' => __('Database tables created successfully.', 'wp-oauth-debugger')
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }

    /**
     * Create necessary database tables.
     *
     * @throws \Exception If table creation fails
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // First, verify database connection and permissions
        try {
            // Test basic database connectivity
            if (!$wpdb->check_connection(false)) {
                throw new \Exception(__('Database connection failed. Please check your database credentials.', 'wp-oauth-debugger'));
            }

            // Test CREATE TABLE permission
            $test_table = $wpdb->prefix . 'oauth_debug_test_' . time();
            $create_result = $wpdb->query("CREATE TABLE IF NOT EXISTS $test_table (id INT)");
            if ($create_result === false) {
                throw new \Exception(sprintf(
                    __('Database user lacks CREATE TABLE permission. Error: %s', 'wp-oauth-debugger'),
                    $wpdb->last_error
                ));
            }

            // Test INSERT permission
            $insert_result = $wpdb->insert($test_table, array('id' => 1));
            if ($insert_result === false) {
                throw new \Exception(sprintf(
                    __('Database user lacks INSERT permission. Error: %s', 'wp-oauth-debugger'),
                    $wpdb->last_error
                ));
            }

            // Test DROP TABLE permission
            $drop_result = $wpdb->query("DROP TABLE IF EXISTS $test_table");
            if ($drop_result === false) {
                throw new \Exception(sprintf(
                    __('Database user lacks DROP TABLE permission. Error: %s', 'wp-oauth-debugger'),
                    $wpdb->last_error
                ));
            }
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                __('Database permission check failed: %s', 'wp-oauth-debugger'),
                $e->getMessage()
            ));
        }

        // OAuth Debug Logs table
        $table_name = $wpdb->prefix . 'oauth_debug_logs';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            level varchar(20) NOT NULL,
            source varchar(50) DEFAULT NULL,
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

        // Execute each table creation separately for better error reporting
        $tables_to_create = array(
            $table_name => "CREATE TABLE IF NOT EXISTS $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                timestamp datetime NOT NULL,
                level varchar(20) NOT NULL,
                source varchar(50) DEFAULT NULL,
                message text NOT NULL,
                context longtext,
                PRIMARY KEY  (id),
                KEY level (level),
                KEY timestamp (timestamp)
            ) $charset_collate;",
            $settings_table => "CREATE TABLE IF NOT EXISTS $settings_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                setting_key varchar(50) NOT NULL,
                setting_value longtext NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY setting_key (setting_key)
            ) $charset_collate;"
        );

        $errors = array();
        foreach ($tables_to_create as $table => $create_sql) {
            $result = dbDelta($create_sql);

            // Verify table was created
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table;
            if (!$table_exists) {
                $errors[] = sprintf(
                    __('Failed to create table %s. Error: %s', 'wp-oauth-debugger'),
                    $table,
                    $wpdb->last_error
                );
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }

        // Verify we can write to the tables
        try {
            // Test logs table
            $insert_result = $wpdb->insert($table_name, array(
                'timestamp' => current_time('mysql'),
                'level' => 'info',
                'source' => 'system',
                'message' => 'Table creation test',
                'context' => json_encode(array('test' => true))
            ));

            if ($insert_result === false) {
                throw new \Exception(sprintf(
                    __('Cannot write to logs table. Error: %s', 'wp-oauth-debugger'),
                    $wpdb->last_error
                ));
            }

            // Test settings table
            $insert_result = $wpdb->insert($settings_table, array(
                'setting_key' => 'test_setting',
                'setting_value' => 'test_value',
                'updated_at' => current_time('mysql')
            ));

            if ($insert_result === false) {
                throw new \Exception(sprintf(
                    __('Cannot write to settings table. Error: %s', 'wp-oauth-debugger'),
                    $wpdb->last_error
                ));
            }

            // Clean up test data
            $wpdb->delete($table_name, array('message' => 'Table creation test'));
            $wpdb->delete($settings_table, array('setting_key' => 'test_setting'));
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                __('Tables were created but are not writable: %s', 'wp-oauth-debugger'),
                $e->getMessage()
            ));
        }
    }

    /**
     * Create default plugin options.
     *
     * @throws \Exception If option creation fails
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
                $result = add_option($key, $value);
                if ($result === false) {
                    throw new \Exception(sprintf(
                        __('Failed to create option: %s', 'wp-oauth-debugger'),
                        $key
                    ));
                }
            }
        }
    }

    /**
     * Create log directory and protection files.
     *
     * @throws \Exception If directory creation or protection fails
     */
    private static function create_log_directory() {
        $log_dir = WP_CONTENT_DIR . '/oauth-debug-logs';

        // Create directory if it doesn't exist
        if (!file_exists($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                throw new \Exception(sprintf(
                    __('Failed to create log directory at: %s', 'wp-oauth-debugger'),
                    $log_dir
                ));
            }

            // Set proper permissions
            if (!chmod($log_dir, 0755)) {
                throw new \Exception(sprintf(
                    __('Failed to set permissions on log directory: %s', 'wp-oauth-debugger'),
                    $log_dir
                ));
            }
        }

        // Create .htaccess to protect logs
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all";
            if (file_put_contents($htaccess_file, $htaccess_content) === false) {
                throw new \Exception(sprintf(
                    __('Failed to create .htaccess file at: %s', 'wp-oauth-debugger'),
                    $htaccess_file
                ));
            }

            // Set proper permissions
            if (!chmod($htaccess_file, 0644)) {
                throw new \Exception(sprintf(
                    __('Failed to set permissions on .htaccess file: %s', 'wp-oauth-debugger'),
                    $htaccess_file
                ));
            }
        }

        // Create index.php to prevent directory listing
        $index_file = $log_dir . '/index.php';
        if (!file_exists($index_file)) {
            if (file_put_contents($index_file, '<?php // Silence is golden') === false) {
                throw new \Exception(sprintf(
                    __('Failed to create index.php at: %s', 'wp-oauth-debugger'),
                    $index_file
                ));
            }

            // Set proper permissions
            if (!chmod($index_file, 0644)) {
                throw new \Exception(sprintf(
                    __('Failed to set permissions on index.php: %s', 'wp-oauth-debugger'),
                    $index_file
                ));
            }
        }

        // Verify directory is writable
        if (!is_writable($log_dir)) {
            throw new \Exception(sprintf(
                __('Log directory is not writable: %s', 'wp-oauth-debugger'),
                $log_dir
            ));
        }
    }

    /**
     * Schedule plugin events.
     *
     * @throws \Exception If event scheduling fails
     */
    private static function schedule_events() {
        $events = array(
            'oauth_debug_cleanup_logs' => 'daily',
            'oauth_debug_security_scan' => 'daily'
        );

        foreach ($events as $hook => $schedule) {
            if (!wp_next_scheduled($hook)) {
                $result = wp_schedule_event(time(), $schedule, $hook);
                if ($result === false) {
                    throw new \Exception(sprintf(
                        __('Failed to schedule event: %s', 'wp-oauth-debugger'),
                        $hook
                    ));
                }
            }
        }
    }

    /**
     * Handle version-specific upgrades.
     *
     * @param string $current_version The current version being upgraded from.
     * @throws \Exception If upgrade fails
     */
    private static function upgrade_version($current_version) {
        try {
            // Only run upgrades for versions that need them
            if (version_compare($current_version, '1.2.0', '<')) {
                // Add any version-specific upgrade logic here
                // For example, adding new columns or migrating data
            }
        } catch (\Exception $e) {
            throw new \Exception(sprintf(
                __('Version upgrade failed: %s', 'wp-oauth-debugger'),
                $e->getMessage()
            ));
        }
    }
}
