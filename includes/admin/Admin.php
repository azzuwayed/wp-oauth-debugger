<?php

namespace WP_OAuth_Debugger\Admin;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * The admin-specific functionality of the plugin.
 */
class Admin {
    /**
     * The ID of this plugin.
     *
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @var string
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/oauth-debug.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/oauth-debug.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'oauthDebug', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oauth_debug_nonce'),
            'i18n' => array(
                'confirmClearLogs' => __('Are you sure you want to clear all logs?', 'wp-oauth-debugger'),
                'confirmDeleteToken' => __('Are you sure you want to delete this token?', 'wp-oauth-debugger'),
                'logsCleared' => __('Logs cleared successfully.', 'wp-oauth-debugger'),
                'tokenDeleted' => __('Token deleted successfully.', 'wp-oauth-debugger'),
                'error' => __('An error occurred.', 'wp-oauth-debugger')
            )
        ));
    }

    /**
     * Add menu items to the admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('OAuth Debugger', 'wp-oauth-debugger'),
            __('OAuth Debugger', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger',
            array($this, 'render_debug_page'),
            'dashicons-search',
            30
        );

        add_submenu_page(
            'oauth-debugger',
            __('Live Monitor', 'wp-oauth-debugger'),
            __('Live Monitor', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-monitor',
            array($this, 'render_monitor_page')
        );

        add_submenu_page(
            'oauth-debugger',
            __('Security Analysis', 'wp-oauth-debugger'),
            __('Security Analysis', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-security',
            array($this, 'render_security_page')
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        register_setting('oauth_debugger_settings', 'oauth_debug_log_level');
        register_setting('oauth_debugger_settings', 'oauth_debug_log_retention');
        register_setting('oauth_debugger_settings', 'oauth_debug_auto_cleanup');
        register_setting('oauth_debugger_settings', 'oauth_debug_security_scan_interval');
        register_setting('oauth_debugger_settings', 'oauth_debug_enable_public_panel');
        register_setting('oauth_debugger_settings', 'oauth_debug_allowed_roles');
        register_setting('oauth_debugger_settings', 'oauth_debug_rate_limit');
        register_setting('oauth_debugger_settings', 'oauth_debug_rate_limit_window');
        register_setting('oauth_debugger_settings', 'oauth_debug_clear_logs_on_deactivate');
    }

    /**
     * Render the main debug page.
     */
    public function render_debug_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $debug_helper = new DebugHelper();
        $active_tokens = $debug_helper->get_active_tokens();
        $server_info = $debug_helper->get_server_info();
        $security_status = $debug_helper->get_security_status();

        include plugin_dir_path(dirname(__FILE__)) . 'templates/debug-page.php';
    }

    /**
     * Render the live monitor page.
     */
    public function render_monitor_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $debug_helper = new DebugHelper();
        $recent_logs = $debug_helper->get_recent_logs();
        $active_tokens = $debug_helper->get_active_tokens();

        include plugin_dir_path(dirname(__FILE__)) . 'templates/monitor-page.php';
    }

    /**
     * Render the security analysis page.
     */
    public function render_security_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $debug_helper = new DebugHelper();
        $security_status = $debug_helper->get_security_status();
        $server_info = $debug_helper->get_server_info();

        include plugin_dir_path(dirname(__FILE__)) . 'templates/security-page.php';
    }

    /**
     * Handle AJAX requests for clearing logs.
     */
    public function ajax_clear_logs() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $debug_helper = new DebugHelper();
        $result = $debug_helper->clear_logs();

        if ($result) {
            wp_send_json_success(__('Logs cleared successfully.', 'wp-oauth-debugger'));
        } else {
            wp_send_json_error(__('Failed to clear logs.', 'wp-oauth-debugger'));
        }
    }

    /**
     * Handle AJAX requests for deleting tokens.
     */
    public function ajax_delete_token() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $token_id = isset($_POST['token_id']) ? sanitize_text_field($_POST['token_id']) : '';
        if (empty($token_id)) {
            wp_send_json_error(__('Token ID is required.', 'wp-oauth-debugger'));
        }

        $debug_helper = new DebugHelper();
        $result = $debug_helper->delete_token($token_id);

        if ($result) {
            wp_send_json_success(__('Token deleted successfully.', 'wp-oauth-debugger'));
        } else {
            wp_send_json_error(__('Failed to delete token.', 'wp-oauth-debugger'));
        }
    }

    /**
     * Handle AJAX requests for getting real-time updates.
     */
    public function ajax_get_updates() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'wp-oauth-debugger'));
        }

        $debug_helper = new DebugHelper();
        $data = array(
            'logs' => $debug_helper->get_recent_logs(),
            'tokens' => $debug_helper->get_active_tokens(),
            'security' => $debug_helper->get_security_status()
        );

        wp_send_json_success($data);
    }
}
