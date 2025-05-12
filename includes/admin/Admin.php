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

        // Add AJAX handlers
        add_action('wp_ajax_oauth_debugger_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_oauth_debugger_delete_token', array($this, 'ajax_delete_token'));
        add_action('wp_ajax_oauth_debugger_get_updates', array($this, 'ajax_get_updates'));
        add_action('wp_ajax_oauth_debugger_setup_database', array($this, 'ajax_setup_database'));
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        // Main plugin styles
        $css_url = WP_OAUTH_DEBUGGER_PLUGIN_URL . 'assets/css/oauth-debug.css';
        error_log('OAuth Debugger: Attempting to load CSS from: ' . $css_url);

        wp_enqueue_style(
            $this->plugin_name,
            $css_url,
            array(),
            $this->version,
            'all'
        );

        // Add Timeline.js styles
        wp_enqueue_style(
            $this->plugin_name . '-timeline',
            'https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css',
            array(),
            '3.8.0',
            'all'
        );

        // Add Chart.js styles - using a more reliable CDN
        wp_enqueue_style(
            $this->plugin_name . '-chart',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css',
            array(),
            '3.7.0',
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        // Main plugin script
        wp_enqueue_script(
            $this->plugin_name,
            WP_OAUTH_DEBUGGER_PLUGIN_URL . 'assets/js/oauth-debug.js',
            array('jquery'),
            $this->version,
            false
        );

        // Add Timeline.js
        wp_enqueue_script(
            $this->plugin_name . '-timeline',
            'https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js',
            array(),
            '3.8.0',
            true
        );

        // Add Chart.js - using a more reliable CDN
        wp_enqueue_script(
            $this->plugin_name . '-chart',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js',
            array(),
            '3.7.0',
            true
        );

        // Add custom timeline script
        wp_enqueue_script(
            $this->plugin_name . '-timeline-custom',
            WP_OAUTH_DEBUGGER_PLUGIN_URL . 'assets/js/timeline.js',
            array($this->plugin_name . '-timeline', $this->plugin_name . '-chart'),
            $this->version,
            true
        );

        wp_localize_script($this->plugin_name, 'oauthDebug', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oauth_debug_nonce'),
            'i18n' => array(
                'confirmClearLogs' => __('Are you sure you want to clear all logs?', 'wp-oauth-debugger'),
                'confirmDeleteToken' => __('Are you sure you want to delete this token?', 'wp-oauth-debugger'),
                'logsCleared' => __('Logs cleared successfully.', 'wp-oauth-debugger'),
                'tokenDeleted' => __('Token deleted successfully.', 'wp-oauth-debugger'),
                'error' => __('An error occurred.', 'wp-oauth-debugger'),
                'timelineTitle' => __('OAuth Request Timeline', 'wp-oauth-debugger'),
                'request' => __('Request', 'wp-oauth-debugger'),
                'response' => __('Response', 'wp-oauth-debugger'),
                'noData' => __('No timeline data available', 'wp-oauth-debugger'),
                'noLogs' => __('No logs found.', 'wp-oauth-debugger'),
                'noSessions' => __('No active sessions found.', 'wp-oauth-debugger'),
                'active' => __('active', 'wp-oauth-debugger'),
                'view' => __('View', 'wp-oauth-debugger'),
                'revoke' => __('Revoke', 'wp-oauth-debugger'),
                'refresh' => __('Refresh', 'wp-oauth-debugger'),
                'autoRefresh' => __('Auto-refresh', 'wp-oauth-debugger'),
                'clearLogs' => __('Clear Logs', 'wp-oauth-debugger'),
                'logContext' => __('Log Context', 'wp-oauth-debugger'),
                'eventDetails' => __('Event Details', 'wp-oauth-debugger'),
                'time' => __('Time', 'wp-oauth-debugger'),
                'level' => __('Level', 'wp-oauth-debugger'),
                'message' => __('Message', 'wp-oauth-debugger'),
                'context' => __('Context', 'wp-oauth-debugger'),
                'created' => __('Created:', 'wp-oauth-debugger'),
                'expires' => __('Expires:', 'wp-oauth-debugger')
            )
        ));
    }

    /**
     * Register admin menu pages.
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

        add_submenu_page(
            'oauth-debugger',
            __('Help & Documentation', 'wp-oauth-debugger'),
            __('Help & Documentation', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-help',
            array($this, 'render_help_page')
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
        global $wpdb;
        $logs_table = $wpdb->prefix . 'oauth_debug_logs';
        $settings_table = $wpdb->prefix . 'oauth_debug_settings';

        // Check if tables exist
        $logs_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $logs_table)) === $logs_table;
        $settings_table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $settings_table)) === $settings_table;
        $tables_exist = $logs_table_exists && $settings_table_exists;

?>
        <div class="wrap">
            <h1><?php echo esc_html__('OAuth Debugger Live Monitor', 'wp-oauth-debugger'); ?></h1>

            <?php if (!$tables_exist): ?>
                <div class="notice notice-warning">
                    <p>
                        <?php echo esc_html__('Database tables are not set up. Click the button below to create them.', 'wp-oauth-debugger'); ?>
                    </p>
                    <p>
                        <button type="button" class="button button-primary" id="oauth-debugger-setup-db">
                            <?php echo esc_html__('Setup Database', 'wp-oauth-debugger'); ?>
                        </button>
                        <span class="spinner" style="float: none; margin-top: 4px;"></span>
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($tables_exist): ?>
                <div class="oauth-debugger-monitor-container">
                    <!-- Existing monitor content -->
                    <div class="oauth-debugger-controls">
                        <button type="button" class="button" id="oauth-debugger-refresh">
                            <?php echo esc_html__('Refresh', 'wp-oauth-debugger'); ?>
                        </button>
                        <button type="button" class="button" id="oauth-debugger-clear-logs">
                            <?php echo esc_html__('Clear Logs', 'wp-oauth-debugger'); ?>
                        </button>
                        <label>
                            <input type="checkbox" id="oauth-debugger-auto-refresh">
                            <?php echo esc_html__('Auto-refresh', 'wp-oauth-debugger'); ?>
                        </label>
                    </div>

                    <div class="oauth-debugger-content">
                        <div class="oauth-debugger-logs">
                            <h2><?php echo esc_html__('Recent Logs', 'wp-oauth-debugger'); ?></h2>
                            <div id="oauth-debugger-logs-container"></div>
                        </div>

                        <div class="oauth-debugger-sessions">
                            <h2><?php echo esc_html__('Active Sessions', 'wp-oauth-debugger'); ?></h2>
                            <div id="oauth-debugger-sessions-container"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Database setup handler
                $('#oauth-debugger-setup-db').on('click', function() {
                    var $button = $(this);
                    var $spinner = $button.next('.spinner');

                    $button.prop('disabled', true);
                    $spinner.addClass('is-active');

                    $.ajax({
                        url: oauthDebug.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'oauth_debugger_setup_database',
                            nonce: oauthDebug.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.data.message || oauthDebug.i18n.error);
                            }
                        },
                        error: function() {
                            alert(oauthDebug.i18n.error);
                        },
                        complete: function() {
                            $button.prop('disabled', false);
                            $spinner.removeClass('is-active');
                        }
                    });
                });

                // Existing monitor page JavaScript...
            });
        </script>
<?php
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
     * Render the help and documentation page.
     */
    public function render_help_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        include plugin_dir_path(dirname(__FILE__)) . 'templates/help-page.php';
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

    /**
     * Handle database setup AJAX request.
     */
    public function ajax_setup_database() {
        check_ajax_referer('oauth_debug_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to perform this action.', 'wp-oauth-debugger')
            ));
        }

        require_once plugin_dir_path(dirname(__FILE__)) . 'Core/Activator.php';
        $result = \WP_OAuth_Debugger\Core\Activator::setup_database();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
