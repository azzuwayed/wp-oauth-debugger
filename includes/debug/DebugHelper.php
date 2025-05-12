<?php

namespace WP_OAuth_Debugger\Debug;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use WP_OAuth_Debugger\Core\Loader;
use WP_OAuth_Debugger\Security\SecurityScanner;

/**
 * The core debugging functionality of the plugin.
 */
class DebugHelper {
    /**
     * The log file path.
     *
     * @var string
     */
    private $log_file;

    /**
     * The log directory path.
     *
     * @var string
     */
    private $log_dir;

    /**
     * The security scanner instance.
     *
     * @var SecurityScanner|null
     */
    private $security_scanner = null;

    /**
     * Initialize the class and set up logging.
     */
    public function __construct() {
        $this->log_dir = \WP_CONTENT_DIR . '/oauth-debug-logs';
        $this->log_file = $this->log_dir . '/oauth-debug.log';
        $this->ensure_log_directory();
    }

    /**
     * Ensure the log directory exists and is writable.
     */
    private function ensure_log_directory() {
        if (!file_exists($this->log_dir)) {
            \wp_mkdir_p($this->log_dir);
        }

        // Create .htaccess to protect logs
        $htaccess_file = $this->log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all";
            file_put_contents($htaccess_file, $htaccess_content);
        }

        // Create index.php to prevent directory listing
        $index_file = $this->log_dir . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
    }

    /**
     * Get the security scanner instance.
     *
     * @return SecurityScanner
     */
    private function get_security_scanner() {
        if ($this->security_scanner === null) {
            if (!class_exists('WP_OAuth_Debugger\Security\SecurityScanner')) {
                // Try to load the class file directly if autoloader hasn't loaded it yet
                $scanner_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'includes/Security/SecurityScanner.php';
                if (file_exists($scanner_file)) {
                    require_once $scanner_file;
                }

                if (!class_exists('WP_OAuth_Debugger\Security\SecurityScanner')) {
                    throw new \RuntimeException('SecurityScanner class could not be loaded. Please ensure the autoloader is working correctly.');
                }
            }
            $this->security_scanner = new \WP_OAuth_Debugger\Security\SecurityScanner($this);
        }
        return $this->security_scanner;
    }

    /**
     * Log a message with the specified level.
     *
     * @param string $message The message to log.
     * @param string $level   The log level (debug, info, warning, error).
     * @param array  $context Additional context data.
     */
    public function log($message, $level = 'info', $context = array()) {
        if (!defined('OAUTH_DEBUG') || !\OAUTH_DEBUG) {
            return;
        }

        try {
            $log_levels = array('debug', 'info', 'warning', 'error');
            $min_level = defined('OAUTH_DEBUG_LOG_LEVEL') ? \OAUTH_DEBUG_LOG_LEVEL : 'info';

            if (array_search($level, $log_levels) < array_search($min_level, $log_levels)) {
                return;
            }

            $timestamp = \current_time('mysql');
            $log_entry = array(
                'timestamp' => $timestamp,
                'level' => $level,
                'message' => $message,
                'context' => $this->sanitize_context($context),
                'user_id' => \get_current_user_id(),
                'ip' => $this->get_client_ip()
            );

            $log_line = json_encode($log_entry) . "\n";

            if (file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX) === false) {
                error_log('OAuth Debugger: Failed to write to log file');
            }

            // Clean up old logs if needed
            $this->cleanup_old_logs();
        } catch (\Exception $e) {
            error_log('OAuth Debugger: Error logging message - ' . $e->getMessage());
        }
    }

    /**
     * Sanitize context data before logging.
     *
     * @param array $context The context data to sanitize.
     * @return array
     */
    private function sanitize_context($context) {
        $sanitized = array();
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                // Mask sensitive data
                if (in_array($key, array('client_secret', 'access_token', 'refresh_token'))) {
                    $sanitized[$key] = $this->mask_sensitive_data($value);
                } else {
                    $sanitized[$key] = \sanitize_text_field($value);
                }
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize_context($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Mask sensitive data in logs.
     *
     * @param string $data The data to mask.
     * @return string
     */
    private function mask_sensitive_data($data) {
        if (strlen($data) <= 8) {
            return '****';
        }
        return substr($data, 0, 4) . '****' . substr($data, -4);
    }

    /**
     * Clean up old log files based on retention period.
     */
    private function cleanup_old_logs() {
        $retention_days = defined('OAUTH_DEBUG_LOG_RETENTION') ? \OAUTH_DEBUG_LOG_RETENTION : 7;
        $cutoff_time = strtotime("-{$retention_days} days");

        if (file_exists($this->log_file)) {
            $logs = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $new_logs = array();

            foreach ($logs as $log) {
                $entry = json_decode($log, true);
                if ($entry && isset($entry['timestamp'])) {
                    $log_time = strtotime($entry['timestamp']);
                    if ($log_time >= $cutoff_time) {
                        $new_logs[] = $log;
                    }
                }
            }

            file_put_contents($this->log_file, implode("\n", $new_logs) . "\n");
        }
    }

    /**
     * Get recent logs.
     *
     * @param int $limit The number of logs to retrieve.
     * @return array
     */
    public function get_recent_logs($limit = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }

        $logs = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_reverse($logs);
        $logs = array_slice($logs, 0, $limit);

        $parsed_logs = array();
        foreach ($logs as $log) {
            $entry = json_decode($log, true);
            if ($entry) {
                $parsed_logs[] = $entry;
            }
        }

        return $parsed_logs;
    }

    /**
     * Clear all logs.
     *
     * @return bool
     */
    public function clear_logs() {
        if (file_exists($this->log_file)) {
            return file_put_contents($this->log_file, '') !== false;
        }
        return true;
    }

    /**
     * Get active OAuth tokens.
     *
     * @return array
     */
    public function get_active_tokens() {
        global $wpdb;

        $tokens = array();
        $table_name = $wpdb->prefix . 'oauth_access_tokens';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            // Check if created_at column exists
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'created_at'");
            $order_by = !empty($column_exists) ? 'created_at' : 'id';

            $results = $wpdb->get_results(
                "SELECT * FROM $table_name WHERE expires > NOW() ORDER BY $order_by DESC",
                \ARRAY_A
            );

            foreach ($results as $token) {
                $user = \get_user_by('id', $token['user_id']);
                $tokens[] = array(
                    'id' => $token['id'],
                    'user_id' => $token['user_id'],
                    'user_login' => $user ? $user->user_login : 'Unknown',
                    'client_id' => $token['client_id'],
                    'scopes' => maybe_unserialize($token['scopes']),
                    'created_at' => isset($token['created_at']) ? $token['created_at'] : $token['id'],
                    'expires' => $token['expires'],
                    'access_token' => $this->mask_sensitive_data($token['access_token'])
                );
            }
        }

        return $tokens;
    }

    /**
     * Delete a specific token.
     *
     * @param string $token_id The token ID to delete.
     * @return bool
     */
    public function delete_token($token_id) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'oauth_access_tokens';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return $wpdb->delete(
                $table_name,
                array('id' => $token_id),
                array('%s')
            ) !== false;
        }

        return false;
    }

    /**
     * Get server information.
     *
     * @return array
     */
    public function get_server_info() {
        global $wp_version;

        return array(
            'wordpress_version' => $wp_version,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'oauth_server_version' => $this->get_oauth_server_version(),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'ssl_enabled' => is_ssl(),
            'server_time' => current_time('mysql'),
            'timezone' => wp_timezone_string(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        );
    }

    /**
     * Get the OAuth server plugin version.
     *
     * @return string
     */
    private function get_oauth_server_version() {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (strpos($plugin_file, 'wp-oauth-server') !== false) {
                return $plugin_data['Version'];
            }
        }

        return 'Not installed';
    }

    /**
     * Get security scan results.
     *
     * @return array
     */
    public function get_security_scan() {
        return $this->get_security_scanner()->run_security_scan();
    }

    /**
     * Get enhanced security status.
     *
     * @return array
     */
    public function get_security_status() {
        $basic_status = array(
            'environment' => $this->determine_environment(),
            'ssl_enabled' => is_ssl(),
            'secure_cookies' => defined('COOKIEPATH') && COOKIEPATH === '/',
            'token_lifetime' => $this->get_token_lifetime(),
            'pkce_support' => $this->check_pkce_support(),
            'cors_enabled' => $this->check_cors_configuration(),
            'rate_limiting' => $this->check_rate_limiting(),
            'security_headers' => $this->check_security_headers()
        );

        // Get detailed security scan results
        $scan_results = $this->get_security_scan();

        return array_merge($basic_status, array(
            'security_score' => $scan_results['security_score'],
            'vulnerabilities' => $scan_results['vulnerabilities'],
            'jwt_analysis' => $scan_results['jwt_analysis'],
            'oauth_config' => $scan_results['oauth_config'],
            'recommendations' => $scan_results['recommendations']
        ));
    }

    /**
     * Determine the current environment.
     *
     * @return string 'development' or 'production'
     */
    private function determine_environment() {
        // Check for common development environment indicators
        $is_dev = false;

        // Check if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $is_dev = true;
        }

        // Check if we're on localhost
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        if (in_array($host, array('localhost', '127.0.0.1', '::1'))) {
            $is_dev = true;
        }

        // Check for common development TLDs
        if (preg_match('/\.(local|test|dev|localhost)$/', $host)) {
            $is_dev = true;
        }

        // Check for common development environment variables
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'development') {
            $is_dev = true;
        }

        return $is_dev ? 'development' : 'production';
    }

    /**
     * Get token lifetime settings.
     *
     * @return array
     */
    private function get_token_lifetime() {
        return array(
            'access_token' => defined('OAUTH_ACCESS_TOKEN_LIFETIME') ? OAUTH_ACCESS_TOKEN_LIFETIME : 3600,
            'refresh_token' => defined('OAUTH_REFRESH_TOKEN_LIFETIME') ? OAUTH_REFRESH_TOKEN_LIFETIME : 1209600
        );
    }

    /**
     * Check if PKCE is supported.
     *
     * @return bool
     */
    private function check_pkce_support() {
        return defined('OAUTH_PKCE_ENABLED') && OAUTH_PKCE_ENABLED;
    }

    /**
     * Check CORS configuration.
     *
     * @return array
     */
    private function check_cors_configuration() {
        $headers = headers_list();
        $cors_headers = array_filter($headers, function ($header) {
            return stripos($header, 'Access-Control-') === 0;
        });

        return array(
            'enabled' => !empty($cors_headers),
            'headers' => array_values($cors_headers)
        );
    }

    /**
     * Check rate limiting status.
     *
     * @return array
     */
    private function check_rate_limiting() {
        return array(
            'enabled' => defined('OAUTH_RATE_LIMIT_ENABLED') && OAUTH_RATE_LIMIT_ENABLED,
            'requests_per_minute' => defined('OAUTH_RATE_LIMIT_REQUESTS') ? OAUTH_RATE_LIMIT_REQUESTS : 60
        );
    }

    /**
     * Check security headers.
     *
     * @return array
     */
    private function check_security_headers() {
        $headers = headers_list();
        $security_headers = array(
            'X-Frame-Options' => false,
            'X-Content-Type-Options' => false,
            'X-XSS-Protection' => false,
            'Strict-Transport-Security' => false,
            'Content-Security-Policy' => false
        );

        foreach ($headers as $header) {
            foreach ($security_headers as $key => $value) {
                if (stripos($header, $key) === 0) {
                    $security_headers[$key] = true;
                }
            }
        }

        return $security_headers;
    }

    /**
     * Verify nonce for admin operations.
     *
     * @param string $nonce The nonce to verify.
     * @param string $action The action name.
     * @return bool
     */
    private function verify_admin_nonce($nonce, $action) {
        if (!current_user_can('manage_options')) {
            return false;
        }
        return wp_verify_nonce($nonce, $action);
    }

    /**
     * Check if user has required capabilities.
     *
     * @param string $capability The capability to check.
     * @return bool
     */
    private function check_capability($capability = 'manage_options') {
        return current_user_can($capability);
    }

    /**
     * Rate limit check for API endpoints.
     *
     * @param string $endpoint The endpoint being accessed.
     * @param int $user_id The user ID.
     * @return bool
     */
    private function check_rate_limit($endpoint, $user_id) {
        $rate_limit = get_option('oauth_debug_rate_limit', 60);
        $rate_window = get_option('oauth_debug_rate_limit_window', 60);

        $transient_key = "oauth_debug_rate_{$endpoint}_{$user_id}";
        $requests = get_transient($transient_key);

        if ($requests === false) {
            set_transient($transient_key, 1, $rate_window);
            return true;
        }

        if ($requests >= $rate_limit) {
            return false;
        }

        set_transient($transient_key, $requests + 1, $rate_window);
        return true;
    }

    /**
     * Register REST API routes with security measures.
     */
    public function register_rest_routes() {
        register_rest_route('oauth-debugger/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_logs_endpoint'),
            'permission_callback' => function () {
                return $this->check_capability() &&
                    $this->check_rate_limit('get_logs', get_current_user_id());
            }
        ));

        register_rest_route('oauth-debugger/v1', '/tokens', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_tokens_endpoint'),
            'permission_callback' => function () {
                return $this->check_capability() &&
                    $this->check_rate_limit('get_tokens', get_current_user_id());
            }
        ));

        register_rest_route('oauth-debugger/v1', '/tokens/(?P<id>[a-zA-Z0-9-]+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_token_endpoint'),
            'permission_callback' => function () {
                return $this->check_capability() &&
                    $this->check_rate_limit('delete_token', get_current_user_id());
            },
            'args' => array(
                'id' => array(
                    'required' => true,
                    'validate_callback' => function ($param) {
                        return is_string($param) && preg_match('/^[a-zA-Z0-9-]+$/', $param);
                    }
                )
            )
        ));
    }

    /**
     * REST API endpoint for getting logs.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_logs_endpoint($request) {
        if (!$this->verify_admin_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new \WP_Error('rest_forbidden', 'Invalid nonce.', array('status' => 403));
        }

        $limit = absint($request->get_param('limit')) ?: 100;
        $logs = $this->get_recent_logs($limit);

        return rest_ensure_response($logs);
    }

    /**
     * REST API endpoint for getting tokens.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_tokens_endpoint($request) {
        if (!$this->verify_admin_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new \WP_Error('rest_forbidden', 'Invalid nonce.', array('status' => 403));
        }

        $tokens = $this->get_active_tokens();
        return rest_ensure_response($tokens);
    }

    /**
     * REST API endpoint for deleting a token.
     *
     * @param \WP_REST_Request $request The request object.
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_token_endpoint($request) {
        if (!$this->verify_admin_nonce($request->get_header('X-WP-Nonce'), 'wp_rest')) {
            return new \WP_Error('rest_forbidden', 'Invalid nonce.', array('status' => 403));
        }

        $token_id = $request->get_param('id');
        $result = $this->delete_token($token_id);

        if ($result) {
            return rest_ensure_response(array('success' => true));
        }

        return new \WP_Error('delete_failed', 'Failed to delete token.', array('status' => 500));
    }

    /**
     * Get client IP address.
     *
     * @return string
     */
    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }
        return $ip;
    }

    /**
     * Stub method for hook callback (log_request) so that call_user_func_array() does not fail.
     * (You can later implement the actual logging logic.)
     */
    public function log_request() {
        // (Stub implementation.)
    }
}
