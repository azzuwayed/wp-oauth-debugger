<?php

namespace WP_OAuth_Debugger\Admin;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * The admin-specific functionality of the plugin.
 */
class Admin {
    /**
     * GitHub repository information.
     */
    const GITHUB_USERNAME = 'azzuwayed';
    const GITHUB_REPO = 'wp-oauth-debugger';

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
            __('Settings', 'wp-oauth-debugger'),
            __('Settings', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-settings',
            array($this, 'render_settings_page')
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
        // General settings
        register_setting('oauth_debugger_general_settings', 'oauth_debug_log_level');
        register_setting('oauth_debugger_general_settings', 'oauth_debug_log_retention');
        register_setting('oauth_debugger_general_settings', 'oauth_debug_auto_cleanup');
        register_setting('oauth_debugger_general_settings', 'oauth_debug_clear_logs_on_deactivate');

        // Security settings
        register_setting('oauth_debugger_security_settings', 'oauth_debug_security_scan_interval');
        register_setting('oauth_debugger_security_settings', 'oauth_debug_enable_public_panel');
        register_setting('oauth_debugger_security_settings', 'oauth_debug_allowed_roles');

        // Notification settings
        register_setting('oauth_debugger_notification_settings', 'oauth_debug_email_notifications');
        register_setting('oauth_debugger_notification_settings', 'oauth_debug_notification_email');
        register_setting('oauth_debugger_notification_settings', 'oauth_debug_notification_security_events');
        register_setting('oauth_debugger_notification_settings', 'oauth_debug_notification_auth_failures');

        // Updates settings
        register_setting('oauth_debugger_updates_settings', 'oauth_debug_auto_updates');
        register_setting('oauth_debugger_updates_settings', 'oauth_debug_beta_updates');
        register_setting('oauth_debugger_updates_settings', 'oauth_debug_update_check_interval');

        // Rate limiting settings
        register_setting('oauth_debugger_security_settings', 'oauth_debug_rate_limit');
        register_setting('oauth_debugger_security_settings', 'oauth_debug_rate_limit_window');
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
     * Render the settings page with tabbed navigation.
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current tab from URL or default to general
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

        // Define tabs
        $tabs = array(
            'general' => __('General', 'wp-oauth-debugger'),
            'security' => __('Security', 'wp-oauth-debugger'),
            'notifications' => __('Notifications', 'wp-oauth-debugger'),
            'updates' => __('Updates', 'wp-oauth-debugger'),
        );

        // Include the settings template
        include plugin_dir_path(dirname(__FILE__)) . 'templates/settings-page.php';
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

    /**
     * Get dashicon for a settings tab.
     *
     * @param string $tab Tab ID.
     * @return string Dashicon name.
     */
    private function get_tab_icon($tab) {
        $icons = [
            'general' => 'admin-settings',
            'security' => 'shield',
            'notifications' => 'email',
            'updates' => 'update',
        ];

        return $icons[$tab] ?? 'admin-generic';
    }

    /**
     * Render general settings fields.
     */
    private function render_general_settings_fields() {
        $log_level = get_option('oauth_debug_log_level', 'info');
        $log_retention = get_option('oauth_debug_log_retention', 7);
        $auto_cleanup = get_option('oauth_debug_auto_cleanup', true);
        $clear_on_deactivate = get_option('oauth_debug_clear_logs_on_deactivate', false);
    ?>
        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Logging Configuration', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_log_level">
                    <?php _e('Log Level', 'wp-oauth-debugger'); ?>
                </label>
                <select name="oauth_debug_log_level" id="oauth_debug_log_level">
                    <option value="debug" <?php selected($log_level, 'debug'); ?>>
                        <?php _e('Debug (Most Verbose)', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="info" <?php selected($log_level, 'info'); ?>>
                        <?php _e('Info (Recommended)', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="warning" <?php selected($log_level, 'warning'); ?>>
                        <?php _e('Warning', 'wp-oauth-debugger'); ?>
                    </option>
                    <option value="error" <?php selected($log_level, 'error'); ?>>
                        <?php _e('Error (Least Verbose)', 'wp-oauth-debugger'); ?>
                    </option>
                </select>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select how detailed the logs should be. More verbose levels will generate more logs.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_log_retention">
                    <?php _e('Log Retention Period (Days)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_log_retention" id="oauth_debug_log_retention"
                    value="<?php echo esc_attr($log_retention); ?>" min="1" max="90" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Number of days to keep logs before automatic deletion. Recommended: 7-30 days.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_auto_cleanup" id="oauth_debug_auto_cleanup"
                        value="1" <?php checked($auto_cleanup); ?> />
                    <label for="oauth_debug_auto_cleanup">
                        <?php _e('Enable automatic log cleanup', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Automatically delete logs older than the retention period.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_clear_logs_on_deactivate" id="oauth_debug_clear_logs_on_deactivate"
                        value="1" <?php checked($clear_on_deactivate); ?> />
                    <label for="oauth_debug_clear_logs_on_deactivate">
                        <?php _e('Clear logs on plugin deactivation', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Remove all logs when the plugin is deactivated.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Debug Console', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <?php _e('The debug console provides real-time insight into OAuth flows and requests.', 'wp-oauth-debugger'); ?>
                </p>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=oauth-debugger-monitor'); ?>" class="button button-secondary">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('Open Live Monitor', 'wp-oauth-debugger'); ?>
                    </a>
                </p>
            </div>
        </div>
    <?php
    }

    /**
     * Render security settings fields.
     */
    private function render_security_settings_fields() {
        $scan_interval = get_option('oauth_debug_security_scan_interval', 24);
        $public_panel = get_option('oauth_debug_enable_public_panel', false);
        $allowed_roles = get_option('oauth_debug_allowed_roles', ['administrator']);
        $rate_limit = get_option('oauth_debug_rate_limit', 60);
        $rate_limit_window = get_option('oauth_debug_rate_limit_window', 60);

        // Get all roles
        $roles = get_editable_roles();
    ?>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Security Scan Settings', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_security_scan_interval">
                    <?php _e('Security Scan Interval (Hours)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_security_scan_interval" id="oauth_debug_security_scan_interval"
                    value="<?php echo esc_attr($scan_interval); ?>" min="1" max="168" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('How often to run automated security scans. Recommended: 24 hours.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Access Control', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_enable_public_panel" id="oauth_debug_enable_public_panel"
                        value="1" <?php checked($public_panel); ?> />
                    <label for="oauth_debug_enable_public_panel">
                        <?php _e('Enable public debugging panel', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('WARNING: Only enable during development. This will allow accessing debug information without authentication.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label">
                    <?php _e('Allowed User Roles', 'wp-oauth-debugger'); ?>
                </label>
                <div class="oauth-debugger-checkbox-group">
                    <?php foreach ($roles as $role_key => $role) : ?>
                        <div class="oauth-debugger-checkbox-item">
                            <input type="checkbox" name="oauth_debug_allowed_roles[]"
                                id="role_<?php echo esc_attr($role_key); ?>"
                                value="<?php echo esc_attr($role_key); ?>"
                                <?php checked(in_array($role_key, (array)$allowed_roles)); ?>
                                <?php disabled($role_key === 'administrator'); ?> />
                            <label for="role_<?php echo esc_attr($role_key); ?>">
                                <?php echo esc_html($role['name']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select which user roles can access the OAuth Debugger. Administrators always have access.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Rate Limiting', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_rate_limit">
                    <?php _e('Rate Limit (Requests)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_rate_limit" id="oauth_debug_rate_limit"
                    value="<?php echo esc_attr($rate_limit); ?>" min="1" max="1000" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Maximum number of requests allowed within the time window.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_rate_limit_window">
                    <?php _e('Rate Limit Window (Seconds)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_rate_limit_window" id="oauth_debug_rate_limit_window"
                    value="<?php echo esc_attr($rate_limit_window); ?>" min="1" max="3600" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Time window for rate limiting in seconds. Default: 60 seconds (1 minute).', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>
    <?php
    }

    /**
     * Render notification settings fields.
     */
    private function render_notification_settings_fields() {
        $email_notifications = get_option('oauth_debug_email_notifications', false);
        $notification_email = get_option('oauth_debug_notification_email', get_option('admin_email'));
        $notify_security = get_option('oauth_debug_notification_security_events', true);
        $notify_auth_failures = get_option('oauth_debug_notification_auth_failures', true);
    ?>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Email Notifications', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_email_notifications" id="oauth_debug_email_notifications"
                        value="1" <?php checked($email_notifications); ?> />
                    <label for="oauth_debug_email_notifications">
                        <?php _e('Enable email notifications', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Send email notifications for important OAuth events.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_notification_email">
                    <?php _e('Notification Email', 'wp-oauth-debugger'); ?>
                </label>
                <input type="email" name="oauth_debug_notification_email" id="oauth_debug_notification_email"
                    value="<?php echo esc_attr($notification_email); ?>" />
                <p class="oauth-debugger-field-description">
                    <?php _e('Email address to receive notifications. Default is the admin email.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Notification Events', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-group">
                    <div class="oauth-debugger-checkbox-item">
                        <input type="checkbox" name="oauth_debug_notification_security_events" id="oauth_debug_notification_security_events"
                            value="1" <?php checked($notify_security); ?> />
                        <label for="oauth_debug_notification_security_events">
                            <?php _e('Security Events', 'wp-oauth-debugger'); ?>
                        </label>
                    </div>
                    <div class="oauth-debugger-checkbox-item">
                        <input type="checkbox" name="oauth_debug_notification_auth_failures" id="oauth_debug_notification_auth_failures"
                            value="1" <?php checked($notify_auth_failures); ?> />
                        <label for="oauth_debug_notification_auth_failures">
                            <?php _e('Authentication Failures', 'wp-oauth-debugger'); ?>
                        </label>
                    </div>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Select which events should trigger email notifications.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Notification Templates', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <?php _e('Notification templates are currently in development. Custom email templates will be available in a future update.', 'wp-oauth-debugger'); ?>
                </p>
                <div class="oauth-debugger-badge info">
                    <span class="dashicons dashicons-info"></span>
                    <?php _e('Coming Soon', 'wp-oauth-debugger'); ?>
                </div>
            </div>
        </div>
    <?php
    }

    /**
     * Render updates settings fields.
     */
    private function render_updates_settings_fields() {
        $auto_updates = get_option('oauth_debug_auto_updates', false);
        $beta_updates = get_option('oauth_debug_beta_updates', false);
        $update_interval = get_option('oauth_debug_update_check_interval', 12);
    ?>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Update Settings', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_auto_updates" id="oauth_debug_auto_updates"
                        value="1" <?php checked($auto_updates); ?> />
                    <label for="oauth_debug_auto_updates">
                        <?php _e('Enable automatic updates', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Automatically update to the latest stable release when available.', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <div class="oauth-debugger-checkbox-item">
                    <input type="checkbox" name="oauth_debug_beta_updates" id="oauth_debug_beta_updates"
                        value="1" <?php checked($beta_updates); ?> />
                    <label for="oauth_debug_beta_updates">
                        <?php _e('Include beta releases', 'wp-oauth-debugger'); ?>
                    </label>
                </div>
                <p class="oauth-debugger-field-description">
                    <?php _e('Receive updates for beta versions (not recommended for production sites).', 'wp-oauth-debugger'); ?>
                </p>
            </div>

            <div class="oauth-debugger-field-row">
                <label class="oauth-debugger-field-label" for="oauth_debug_update_check_interval">
                    <?php _e('Update Check Interval (Hours)', 'wp-oauth-debugger'); ?>
                </label>
                <input type="number" name="oauth_debug_update_check_interval" id="oauth_debug_update_check_interval"
                    value="<?php echo esc_attr($update_interval); ?>" min="1" max="168" step="1" />
                <p class="oauth-debugger-field-description">
                    <?php _e('How often to check for updates. Default: 12 hours.', 'wp-oauth-debugger'); ?>
                </p>
            </div>
        </div>

        <div class="oauth-debugger-settings-section">
            <h3><?php _e('Current Version', 'wp-oauth-debugger'); ?></h3>

            <div class="oauth-debugger-field-row">
                <p>
                    <strong><?php _e('Installed Version:', 'wp-oauth-debugger'); ?></strong>
                    <?php echo esc_html(WP_OAUTH_DEBUGGER_VERSION); ?>
                </p>
                <p>
                    <a href="https://github.com/<?php echo esc_attr(self::GITHUB_USERNAME); ?>/<?php echo esc_attr(self::GITHUB_REPO); ?>/releases"
                        target="_blank" class="button button-secondary">
                        <span class="dashicons dashicons-external"></span>
                        <?php _e('View Release Notes', 'wp-oauth-debugger'); ?>
                    </a>
                </p>
            </div>
        </div>
<?php
    }
}
