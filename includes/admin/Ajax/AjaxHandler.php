<?php

namespace WP_OAuth_Debugger\Admin\Ajax;

use WP_OAuth_Debugger\Debug\DebugHelper;
use WP_OAuth_Debugger\Admin\Admin;

/**
 * Handles all AJAX requests for the admin area
 */
class AjaxHandler {
    /**
     * @var DebugHelper
     */
    private $debug_helper;

    /**
     * Constructor
     */
    public function __construct() {
        $this->debug_helper = new DebugHelper();
    }

    /**
     * Handle AJAX requests for clearing logs
     */
    public function clear_logs() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $result = $this->debug_helper->clear_logs();

        if ($result) {
            wp_send_json_success(__('Logs cleared successfully.', 'wp-oauth-debugger'));
        } else {
            wp_send_json_error(__('Failed to clear logs.', 'wp-oauth-debugger'));
        }
    }

    /**
     * Handle AJAX requests for deleting tokens
     */
    public function delete_token() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $token_id = isset($_POST['token_id']) ? sanitize_text_field($_POST['token_id']) : '';
        if (empty($token_id)) {
            wp_send_json_error(__('Token ID is required.', 'wp-oauth-debugger'));
        }

        $result = $this->debug_helper->delete_token($token_id);

        if ($result) {
            wp_send_json_success(__('Token deleted successfully.', 'wp-oauth-debugger'));
        } else {
            wp_send_json_error(__('Failed to delete token.', 'wp-oauth-debugger'));
        }
    }

    /**
     * Handle AJAX requests for getting real-time updates
     */
    public function get_updates() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $data = array(
            'logs' => $this->debug_helper->get_recent_logs(),
            'tokens' => $this->debug_helper->get_active_tokens(),
            'security' => $this->debug_helper->get_security_status()
        );

        wp_send_json_success($data);
    }

    /**
     * Handle manual update check AJAX request
     */
    public function manual_update_check() {
        check_ajax_referer('oauth_debugger_update_check', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        // Get version override if provided
        $version_override = isset($_POST['version_override']) ? sanitize_text_field($_POST['version_override']) : '';

        // Save API debug setting
        $api_debug = get_option('oauth_debug_update_api_debug', false);

        try {
            // Check for GitHub API access
            $api_url = sprintf(
                'https://api.github.com/repos/%s/%s/releases',
                Admin::GITHUB_USERNAME,
                Admin::GITHUB_REPO
            );

            $response = wp_remote_get($api_url, array(
                'timeout' => 10,
                'sslverify' => true,
                'headers' => array(
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
                )
            ));

            // Check for HTTP errors
            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            // Get response code
            $response_code = wp_remote_retrieve_response_code($response);

            // Handle response codes
            if ($response_code !== 200) {
                $error_message = wp_remote_retrieve_response_message($response);

                if ($response_code === 404) {
                    // No releases exist yet
                    if ($api_debug) {
                        update_option('oauth_debug_update_last_response', sprintf(
                            __('GitHub API returned 404: No releases found for %s/%s', 'wp-oauth-debugger'),
                            Admin::GITHUB_USERNAME,
                            Admin::GITHUB_REPO
                        ));
                    }

                    wp_send_json_success(array(
                        'message' => __('Repository found, but no releases have been published yet.', 'wp-oauth-debugger'),
                        'update_available' => false,
                        'api_response' => $api_debug ? json_encode(array(
                            'code' => 404,
                            'message' => 'No releases found'
                        ), JSON_PRETTY_PRINT) : ''
                    ));
                } else {
                    throw new \Exception(sprintf(
                        __('GitHub API error (HTTP %d): %s', 'wp-oauth-debugger'),
                        $response_code,
                        $error_message
                    ));
                }
            }

            // Parse response body
            $body = wp_remote_retrieve_body($response);
            $releases = json_decode($body, true);

            // Check for empty releases array
            if (empty($releases) || !is_array($releases)) {
                if ($api_debug) {
                    update_option('oauth_debug_update_last_response', __('No releases found in GitHub API response', 'wp-oauth-debugger'));
                }

                wp_send_json_success(array(
                    'message' => __('Connected to GitHub successfully, but no releases were found.', 'wp-oauth-debugger'),
                    'update_available' => false,
                    'api_response' => $api_debug ? json_encode(array(
                        'code' => 200,
                        'body' => 'Empty releases array'
                    ), JSON_PRETTY_PRINT) : ''
                ));
            }

            // Filter out pre-releases if beta updates are not enabled
            $beta_updates = get_option('oauth_debug_beta_updates', false);
            if (!$beta_updates) {
                $releases = array_filter($releases, function ($release) {
                    return empty($release['prerelease']);
                });

                // Reindex array after filtering
                $releases = array_values($releases);
            }

            // Check for empty releases array after filtering
            if (empty($releases)) {
                if ($api_debug) {
                    update_option('oauth_debug_update_last_response', __('No stable releases found (only pre-releases exist)', 'wp-oauth-debugger'));
                }

                wp_send_json_success(array(
                    'message' => __('Only pre-release versions were found. Enable beta updates to see them.', 'wp-oauth-debugger'),
                    'update_available' => false,
                    'api_response' => $api_debug ? $body : ''
                ));
            }

            // Get latest release
            $latest_release = $releases[0];

            // Get current plugin version
            $current_version = !empty($version_override) ? $version_override : (defined('WP_OAUTH_DEBUGGER_VERSION') ? WP_OAUTH_DEBUGGER_VERSION : '0.0.0');

            // Clean version tags (remove 'v' prefix if present)
            $latest_version = ltrim($latest_release['tag_name'], 'v');
            $current_version = ltrim($current_version, 'v');

            // Compare versions
            $update_available = version_compare($latest_version, $current_version, '>');

            // Prepare package URL
            $package_url = '';
            if (!empty($latest_release['assets']) && is_array($latest_release['assets'])) {
                foreach ($latest_release['assets'] as $asset) {
                    if (
                        isset($asset['browser_download_url']) &&
                        strpos($asset['browser_download_url'], '.zip') !== false
                    ) {
                        $package_url = $asset['browser_download_url'];
                        break;
                    }
                }
            }

            // Fallback to zipball_url if no package found
            if (empty($package_url) && isset($latest_release['zipball_url'])) {
                $package_url = $latest_release['zipball_url'];
            }

            // Store API response for debugging if enabled
            if ($api_debug) {
                update_option('oauth_debug_update_last_response', $body);
            }

            // Send success response
            wp_send_json_success(array(
                'message' => $update_available
                    ? sprintf(__('Update found! Version %s is available.', 'wp-oauth-debugger'), $latest_version)
                    : sprintf(__('You are running the latest version (%s).', 'wp-oauth-debugger'), $current_version),
                'update_available' => $update_available,
                'new_version' => $latest_version,
                'current_version' => $current_version,
                'package_url' => $package_url,
                'api_response' => $api_debug ? $body : ''
            ));
        } catch (\Exception $e) {
            // Store error message for debugging
            if ($api_debug) {
                update_option('oauth_debug_update_last_response', $e->getMessage());
            }

            wp_send_json_error(array(
                'message' => sprintf(__('Update check failed: %s', 'wp-oauth-debugger'), $e->getMessage())
            ));
        }
    }

    /**
     * Handle clearing of API response data
     */
    public function clear_api_response() {
        check_ajax_referer('oauth_debugger_clear_api_response', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        update_option('oauth_debug_update_last_response', '');
        wp_send_json_success();
    }

    /**
     * Handle database setup AJAX request
     */
    public function setup_database() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'Core/Activator.php';
        $result = \WP_OAuth_Debugger\Core\Activator::setup_database();

        if ($result['success']) {
            wp_send_json_success(array(
                'message' => $result['message'],
                'details' => __('Database tables created successfully.', 'wp-oauth-debugger')
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['message'],
                'details' => __('Failed to create database tables.', 'wp-oauth-debugger')
            ));
        }
    }

    /**
     * Handle emptying database AJAX request
     */
    public function empty_database() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        global $wpdb;
        $success = true;
        $errors = array();
        $tables = array(
            $wpdb->prefix . 'oauth_debug_logs',
            $wpdb->prefix . 'oauth_debug_settings'
        );

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $result = $wpdb->query("TRUNCATE TABLE $table");
                if ($result === false) {
                    $success = false;
                    $errors[] = sprintf(__('Failed to empty table: %s', 'wp-oauth-debugger'), $table);
                }
            }
        }

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Database tables emptied successfully.', 'wp-oauth-debugger')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to empty database tables.', 'wp-oauth-debugger'),
                'details' => implode(', ', $errors)
            ));
        }
    }

    /**
     * Handle removing database tables AJAX request
     */
    public function remove_database() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        global $wpdb;
        $success = true;
        $errors = array();
        $tables = array(
            $wpdb->prefix . 'oauth_debug_logs',
            $wpdb->prefix . 'oauth_debug_settings'
        );

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $result = $wpdb->query("DROP TABLE $table");
                if ($result === false) {
                    $success = false;
                    $errors[] = sprintf(__('Failed to remove table: %s', 'wp-oauth-debugger'), $table);
                }
            }
        }

        if ($success) {
            wp_send_json_success(array(
                'message' => __('Database tables removed successfully.', 'wp-oauth-debugger')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to remove database tables.', 'wp-oauth-debugger'),
                'details' => implode(', ', $errors)
            ));
        }
    }

    /**
     * Handle reset plugin data AJAX request
     */
    public function reset_plugin() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        // First remove database tables
        global $wpdb;
        $tables = array(
            $wpdb->prefix . 'oauth_debug_logs',
            $wpdb->prefix . 'oauth_debug_settings'
        );

        foreach ($tables as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $wpdb->query("DROP TABLE $table");
            }
        }

        // Delete all plugin options
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'oauth_debug_%'");

        // Clear logs directory
        $log_dir = WP_CONTENT_DIR . '/oauth-debug-logs';
        if (is_dir($log_dir)) {
            array_map('unlink', glob("$log_dir/*.log"));
        }

        wp_send_json_success(array(
            'message' => __('All plugin data has been reset successfully.', 'wp-oauth-debugger'),
            'details' => __('You may need to refresh the page to see the changes.', 'wp-oauth-debugger')
        ));
    }
}
