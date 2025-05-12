<?php

namespace WP_OAuth_Debugger\Admin;

use WP_OAuth_Debugger\Admin\Settings\SettingsManager;
use WP_OAuth_Debugger\Admin\Pages\DebugPage;
use WP_OAuth_Debugger\Admin\Pages\MonitorPage;
use WP_OAuth_Debugger\Admin\Pages\SecurityPage;
use WP_OAuth_Debugger\Admin\Pages\HelpPage;
use WP_OAuth_Debugger\Admin\Pages\SettingsPage;
use WP_OAuth_Debugger\Admin\Assets\AssetManager;
use WP_OAuth_Debugger\Admin\Ajax\AjaxHandler;

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
     * @var SettingsManager
     */
    private $settings_manager;

    /**
     * @var AssetManager
     */
    private $asset_manager;

    /**
     * @var AjaxHandler
     */
    private $ajax_handler;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Initialize components
        $this->settings_manager = new SettingsManager();
        $this->asset_manager = new AssetManager($plugin_name, $version);
        $this->ajax_handler = new AjaxHandler();

        // Register hooks
        $this->register_hooks();
    }

    /**
     * Register all necessary hooks
     */
    private function register_hooks() {
        // Assets
        add_action('admin_enqueue_scripts', array($this->asset_manager, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->asset_manager, 'enqueue_scripts'));

        // Menu and pages
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Settings
        add_action('admin_init', array($this->settings_manager, 'register_settings'));

        // AJAX handlers
        add_action('wp_ajax_oauth_debugger_clear_logs', array($this->ajax_handler, 'clear_logs'));
        add_action('wp_ajax_oauth_debugger_delete_token', array($this->ajax_handler, 'delete_token'));
        add_action('wp_ajax_oauth_debugger_get_updates', array($this->ajax_handler, 'get_updates'));
        add_action('wp_ajax_oauth_debugger_setup_database', array($this->ajax_handler, 'setup_database'));
        add_action('wp_ajax_oauth_debugger_empty_database', array($this->ajax_handler, 'empty_database'));
        add_action('wp_ajax_oauth_debugger_remove_database', array($this->ajax_handler, 'remove_database'));
        add_action('wp_ajax_oauth_debugger_reset_plugin', array($this->ajax_handler, 'reset_plugin'));
        add_action('wp_ajax_oauth_debugger_manual_update_check', array($this->ajax_handler, 'manual_update_check'));
        add_action('wp_ajax_oauth_debugger_clear_api_response', array($this->ajax_handler, 'clear_api_response'));
    }

    /**
     * Register admin menu pages.
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('OAuth Debugger', 'wp-oauth-debugger'),
            __('OAuth Debugger', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger',
            array(new DebugPage(), 'render'),
            'dashicons-search',
            30
        );

        // Submenu pages
        add_submenu_page(
            'oauth-debugger',
            __('Live Monitor', 'wp-oauth-debugger'),
            __('Live Monitor', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-monitor',
            array(new MonitorPage(), 'render')
        );

        add_submenu_page(
            'oauth-debugger',
            __('Security Analysis', 'wp-oauth-debugger'),
            __('Security Analysis', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-security',
            array(new SecurityPage(), 'render')
        );

        add_submenu_page(
            'oauth-debugger',
            __('Settings', 'wp-oauth-debugger'),
            __('Settings', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-settings',
            array(new SettingsPage($this->settings_manager), 'render')
        );

        add_submenu_page(
            'oauth-debugger',
            __('Help & Documentation', 'wp-oauth-debugger'),
            __('Help & Documentation', 'wp-oauth-debugger'),
            'manage_options',
            'oauth-debugger-help',
            array(new HelpPage(), 'render')
        );
    }

    /**
     * Get the plugin name.
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Get the plugin version.
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Enqueue styles for the admin area.
     * This delegates to the AssetManager.
     */
    public function enqueue_styles() {
        $this->asset_manager->enqueue_styles();
    }

    /**
     * Enqueue scripts for the admin area.
     * This delegates to the AssetManager.
     */
    public function enqueue_scripts() {
        $this->asset_manager->enqueue_scripts();
    }
}
