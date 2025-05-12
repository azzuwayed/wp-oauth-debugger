<?php

namespace WP_OAuth_Debugger\Core;

/**
 * Handles pre-activation validation checks for the plugin.
 */
class Validator {
    /**
     * Validation errors array.
     *
     * @var array
     */
    private $errors = [];

    /**
     * Validation warnings array.
     *
     * @var array
     */
    private $warnings = [];

    /**
     * Run all validation checks.
     *
     * @return bool True if all checks pass, false otherwise.
     */
    public function validate() {
        $this->check_php_version()
            ->check_wordpress_version()
            ->check_php_extensions()
            ->check_file_permissions()
            ->check_directory_permissions()
            ->check_database_capabilities()
            ->check_server_environment()
            ->check_composer_dependencies();

        return empty($this->errors);
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function get_errors() {
        return $this->errors;
    }

    /**
     * Get validation warnings.
     *
     * @return array
     */
    public function get_warnings() {
        return $this->warnings;
    }

    /**
     * Check PHP version requirement.
     *
     * @return self
     */
    private function check_php_version() {
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            $this->errors[] = sprintf(
                __('PHP version %s or higher is required. Current version is %s.', 'wp-oauth-debugger'),
                '7.4',
                PHP_VERSION
            );
        }
        return $this;
    }

    /**
     * Check WordPress version requirement.
     *
     * @return self
     */
    private function check_wordpress_version() {
        if (version_compare(get_bloginfo('version'), '6.5', '<')) {
            $this->errors[] = sprintf(
                __('WordPress version %s or higher is required. Current version is %s.', 'wp-oauth-debugger'),
                '6.5',
                get_bloginfo('version')
            );
        }
        return $this;
    }

    /**
     * Check required PHP extensions.
     *
     * @return self
     */
    private function check_php_extensions() {
        $required_extensions = ['json', 'mbstring', 'openssl', 'pdo', 'pdo_mysql'];
        $missing_extensions = [];

        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missing_extensions[] = $ext;
            }
        }

        if (!empty($missing_extensions)) {
            $this->errors[] = sprintf(
                __('Required PHP extensions are missing: %s', 'wp-oauth-debugger'),
                implode(', ', $missing_extensions)
            );
        }
        return $this;
    }

    /**
     * Check file permissions.
     *
     * @return self
     */
    private function check_file_permissions() {
        $plugin_dir = WP_OAUTH_DEBUGGER_PLUGIN_DIR;
        $critical_files = [
            'wp-oauth-debugger.php',
            'includes/Core/Activator.php',
            'includes/Core/Deactivator.php',
            'vendor/autoload.php'
        ];

        foreach ($critical_files as $file) {
            $file_path = $plugin_dir . $file;
            if (!file_exists($file_path)) {
                $this->errors[] = sprintf(
                    __('Critical file missing: %s', 'wp-oauth-debugger'),
                    $file
                );
                continue;
            }

            if (!is_readable($file_path)) {
                $this->errors[] = sprintf(
                    __('File is not readable: %s', 'wp-oauth-debugger'),
                    $file
                );
            }
        }
        return $this;
    }

    /**
     * Check directory permissions.
     *
     * @return self
     */
    private function check_directory_permissions() {
        $log_dir = WP_CONTENT_DIR . '/oauth-debug-logs';

        // Check if we can create the log directory
        if (!file_exists($log_dir)) {
            if (!wp_mkdir_p($log_dir)) {
                $this->errors[] = sprintf(
                    __('Cannot create log directory at: %s', 'wp-oauth-debugger'),
                    $log_dir
                );
            }
        } else {
            // Check if existing log directory is writable
            if (!is_writable($log_dir)) {
                $this->errors[] = sprintf(
                    __('Log directory is not writable: %s', 'wp-oauth-debugger'),
                    $log_dir
                );
            }
        }

        // Check plugin directory permissions
        $plugin_dir = WP_OAUTH_DEBUGGER_PLUGIN_DIR;
        if (!is_readable($plugin_dir)) {
            $this->errors[] = sprintf(
                __('Plugin directory is not readable: %s', 'wp-oauth-debugger'),
                $plugin_dir
            );
        }
        return $this;
    }

    /**
     * Check database capabilities.
     *
     * @return self
     */
    private function check_database_capabilities() {
        global $wpdb;

        // Check if we can create tables
        $test_table = $wpdb->prefix . 'oauth_debug_test';
        $result = $wpdb->query("CREATE TABLE IF NOT EXISTS $test_table (id INT)");

        if ($result === false) {
            $this->errors[] = sprintf(
                __('Database error: %s', 'wp-oauth-debugger'),
                $wpdb->last_error
            );
        } else {
            // Clean up test table
            $wpdb->query("DROP TABLE IF EXISTS $test_table");
        }

        // Check if we have required privileges
        $privileges = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER()");
        $has_required_privileges = false;

        foreach ($privileges as $privilege) {
            $grant_string = reset($privilege); // Get the first (and only) property value
            if (
                strpos($grant_string, 'ALL PRIVILEGES') !== false ||
                (strpos($grant_string, 'CREATE') !== false &&
                    strpos($grant_string, 'INSERT') !== false &&
                    strpos($grant_string, 'SELECT') !== false)
            ) {
                $has_required_privileges = true;
                break;
            }
        }

        if (!$has_required_privileges) {
            $this->warnings[] = __('Database user may not have all required privileges.', 'wp-oauth-debugger');
        }
        return $this;
    }

    /**
     * Check server environment.
     *
     * @return self
     */
    private function check_server_environment() {
        // Check memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $min_memory = 64 * 1024 * 1024; // 64MB

        if ($memory_limit < $min_memory) {
            $this->warnings[] = sprintf(
                __('Memory limit is set to %s. Recommended minimum is 64MB.', 'wp-oauth-debugger'),
                ini_get('memory_limit')
            );
        }

        // Check max execution time
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time > 0 && $max_execution_time < 30) {
            $this->warnings[] = sprintf(
                __('Max execution time is set to %s seconds. Recommended minimum is 30 seconds.', 'wp-oauth-debugger'),
                $max_execution_time
            );
        }

        // Check if mod_rewrite is available (for pretty permalinks)
        if (function_exists('apache_get_modules')) {
            if (!in_array('mod_rewrite', apache_get_modules())) {
                $this->warnings[] = __('Apache mod_rewrite module is not enabled. Pretty permalinks may not work.', 'wp-oauth-debugger');
            }
        }
        return $this;
    }

    /**
     * Check Composer dependencies.
     *
     * @return self
     */
    private function check_composer_dependencies() {
        $autoload_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'vendor/autoload.php';

        if (!file_exists($autoload_file)) {
            $this->errors[] = __('Composer dependencies are not installed. Please run "composer install" in the plugin directory.', 'wp-oauth-debugger');
            return $this;
        }

        // Try to load the autoloader
        try {
            require_once $autoload_file;
            if (!class_exists('Composer\Autoload\ClassLoader')) {
                $this->errors[] = __('Composer autoloader is not working correctly.', 'wp-oauth-debugger');
            }
        } catch (\Exception $e) {
            $this->errors[] = sprintf(
                __('Error loading Composer autoloader: %s', 'wp-oauth-debugger'),
                $e->getMessage()
            );
        }
        return $this;
    }
}
