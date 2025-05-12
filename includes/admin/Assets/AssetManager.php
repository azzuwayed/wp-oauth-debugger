<?php

namespace WP_OAuth_Debugger\Admin\Assets;

/**
 * Manages all admin assets (styles and scripts)
 */
class AssetManager {
    /**
     * @var string
     */
    private $plugin_name;

    /**
     * @var string
     */
    private $version;

    /**
     * Constructor
     *
     * @param string $plugin_name The name of this plugin
     * @param string $version    The version of this plugin
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        // Main plugin styles
        $css_file = 'assets/css/oauth-debug.css';
        $css_path = WP_OAUTH_DEBUGGER_PLUGIN_DIR . $css_file;
        $css_url = WP_OAUTH_DEBUGGER_PLUGIN_URL . $css_file;

        // Check if file exists before enqueuing
        if (file_exists($css_path)) {
            wp_enqueue_style(
                $this->plugin_name,
                $css_url,
                array(),
                $this->version,
                'all'
            );
        } else {
            error_log('OAuth Debugger: CSS file not found at: ' . $css_path);
        }

        // Add Timeline.js styles
        wp_enqueue_style(
            $this->plugin_name . '-timeline',
            'https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css',
            array(),
            '3.8.0',
            'all'
        );

        // Add Chart.js styles
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
        $js_file = 'assets/js/oauth-debug.js';
        $js_path = WP_OAUTH_DEBUGGER_PLUGIN_DIR . $js_file;
        $js_url = WP_OAUTH_DEBUGGER_PLUGIN_URL . $js_file;

        // Check if file exists before enqueuing
        if (file_exists($js_path)) {
            wp_enqueue_script(
                $this->plugin_name,
                $js_url,
                array('jquery'),
                $this->version,
                false
            );
        } else {
            error_log('OAuth Debugger: JS file not found at: ' . $js_path);
        }

        // Add Timeline.js
        wp_enqueue_script(
            $this->plugin_name . '-timeline',
            'https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js',
            array(),
            '3.8.0',
            true
        );

        // Add Chart.js
        wp_enqueue_script(
            $this->plugin_name . '-chart',
            'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js',
            array(),
            '3.7.0',
            true
        );

        // Add custom timeline script
        $timeline_js_file = 'assets/js/timeline.js';
        $timeline_js_path = WP_OAUTH_DEBUGGER_PLUGIN_DIR . $timeline_js_file;
        $timeline_js_url = WP_OAUTH_DEBUGGER_PLUGIN_URL . $timeline_js_file;

        if (file_exists($timeline_js_path)) {
            wp_enqueue_script(
                $this->plugin_name . '-timeline-custom',
                $timeline_js_url,
                array($this->plugin_name . '-timeline', $this->plugin_name . '-chart'),
                $this->version,
                true
            );
        } else {
            error_log('OAuth Debugger: Custom timeline JS file not found at: ' . $timeline_js_path);
        }

        // Localize script with translations and settings
        wp_localize_script($this->plugin_name, 'oauthDebug', $this->get_localization_data());
    }

    /**
     * Get data to be localized for JavaScript
     *
     * @return array
     */
    private function get_localization_data() {
        return array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('oauth_debug_nonce'),
            'i18n' => array(
                'confirmClearLogs' => __('Are you sure you want to clear all logs?', 'wp-oauth-debugger'),
                'confirmDeleteToken' => __('Are you sure you want to delete this token?', 'wp-oauth-debugger'),
                'confirmDatabaseSetup' => __('Are you sure you want to set up the database tables?', 'wp-oauth-debugger'),
                'confirmEmptyDatabase' => __('Are you sure you want to empty all database tables? This will remove all log data.', 'wp-oauth-debugger'),
                'confirmRemoveDatabase' => __('Are you sure you want to remove all database tables? This cannot be undone.', 'wp-oauth-debugger'),
                'confirmResetPlugin' => __('Are you sure you want to reset all plugin data? This will remove all settings, logs, and database tables. This action cannot be undone.', 'wp-oauth-debugger'),
                'processing' => __('Processing...', 'wp-oauth-debugger'),
                'setupDatabase' => __('Setup Database', 'wp-oauth-debugger'),
                'emptyDatabase' => __('Empty Database', 'wp-oauth-debugger'),
                'removeDatabase' => __('Remove Database Tables', 'wp-oauth-debugger'),
                'resetPlugin' => __('Reset All Plugin Data', 'wp-oauth-debugger'),
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
        );
    }
}
