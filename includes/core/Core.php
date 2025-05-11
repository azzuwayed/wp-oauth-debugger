<?php
namespace WP_OAuth_Debugger\Core;

use WP_OAuth_Debugger\Admin\Admin;
use WP_OAuth_Debugger\Core\Loader;
use WP_OAuth_Debugger\Debug\DebugHelper;
use WP_OAuth_Debugger\Public\PublicFacing;

/**
 * The core plugin class.
 */
class Core {
    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @var Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @var string
     */
    protected $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        $this->plugin_name = 'wp-oauth-debugger';
        $this->version = WP_OAUTH_DEBUGGER_VERSION;
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        $this->loader = new Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());
        $debug_helper = new DebugHelper();

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

        // Debug helper hooks
        $this->loader->add_action('init', $debug_helper, 'log_request', 1);
        $this->loader->add_action('wp_oauth_server_before_token_validation', $debug_helper, 'log_token_validation', 10, 2);
        $this->loader->add_action('wp_oauth_server_after_token_validation', $debug_helper, 'log_token_validation_result', 10, 2);
        $this->loader->add_action('wp_oauth_server_before_token_creation', $debug_helper, 'log_token_creation', 10, 2);
        $this->loader->add_action('wp_oauth_server_after_token_creation', $debug_helper, 'log_token_creation_result', 10, 2);
        $this->loader->add_action('wp_oauth_server_before_authorization', $debug_helper, 'log_authorization_attempt', 10, 2);
        $this->loader->add_action('wp_oauth_server_after_authorization', $debug_helper, 'log_authorization_result', 10, 2);
        $this->loader->add_action('wp_scheduled_delete', $debug_helper, 'cleanup_old_logs');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new PublicFacing($this->get_plugin_name(), $this->get_version());
        $debug_helper = new DebugHelper();

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('rest_api_init', $debug_helper, 'register_rest_routes');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of WordPress.
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
} 