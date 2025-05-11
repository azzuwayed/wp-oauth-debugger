<?php
namespace WP_OAuth_Debugger\Public;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * The public-facing functionality of the plugin.
 */
class PublicFacing {
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
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        if ($this->should_enqueue_assets()) {
            wp_enqueue_style(
                $this->plugin_name,
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/oauth-debug-public.css',
                array(),
                $this->version,
                'all'
            );
        }
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        if ($this->should_enqueue_assets()) {
            wp_enqueue_script(
                $this->plugin_name,
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/oauth-debug-public.js',
                array('jquery'),
                $this->version,
                false
            );

            wp_localize_script($this->plugin_name, 'oauthDebugPublic', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('oauth_debug_public_nonce')
            ));
        }
    }

    /**
     * Determine if assets should be enqueued on the current page.
     *
     * @return bool
     */
    private function should_enqueue_assets() {
        // Only enqueue on pages that might need OAuth debugging
        return is_singular() || is_page() || is_front_page();
    }

    /**
     * Register REST API endpoints for the diagnostic agent.
     */
    public function register_rest_routes() {
        register_rest_route('oauth-debugger/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_debug_status'),
            'permission_callback' => array($this, 'check_api_permission')
        ));

        register_rest_route('oauth-debugger/v1', '/logs', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_debug_logs'),
            'permission_callback' => array($this, 'check_api_permission')
        ));

        register_rest_route('oauth-debugger/v1', '/tokens', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_active_tokens'),
            'permission_callback' => array($this, 'check_api_permission')
        ));

        register_rest_route('oauth-debugger/v1', '/security', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_security_status'),
            'permission_callback' => array($this, 'check_api_permission')
        ));
    }

    /**
     * Check if the request has permission to access the API.
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function check_api_permission($request) {
        // Check for valid API key in headers
        $api_key = $request->get_header('X-OAuth-Debug-Key');
        if (!$api_key) {
            return false;
        }

        // Verify API key against stored value
        $stored_key = get_option('oauth_debugger_api_key');
        if (!$stored_key || $api_key !== $stored_key) {
            return false;
        }

        return true;
    }

    /**
     * Get debug status endpoint.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_debug_status($request) {
        $debug_helper = new DebugHelper();
        $status = array(
            'enabled' => defined('OAUTH_DEBUG') && OAUTH_DEBUG,
            'log_level' => defined('OAUTH_DEBUG_LOG_LEVEL') ? OAUTH_DEBUG_LOG_LEVEL : 'info',
            'log_retention' => defined('OAUTH_DEBUG_LOG_RETENTION') ? OAUTH_DEBUG_LOG_RETENTION : 7,
            'server_info' => $debug_helper->get_server_info(),
            'plugin_version' => WP_OAUTH_DEBUGGER_VERSION
        );

        return rest_ensure_response($status);
    }

    /**
     * Get debug logs endpoint.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_debug_logs($request) {
        $debug_helper = new DebugHelper();
        $logs = $debug_helper->get_recent_logs();

        return rest_ensure_response($logs);
    }

    /**
     * Get active tokens endpoint.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_active_tokens($request) {
        $debug_helper = new DebugHelper();
        $tokens = $debug_helper->get_active_tokens();

        return rest_ensure_response($tokens);
    }

    /**
     * Get security status endpoint.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function get_security_status($request) {
        $debug_helper = new DebugHelper();
        $security = $debug_helper->get_security_status();

        return rest_ensure_response($security);
    }

    /**
     * Add debug information to the page when in debug mode.
     */
    public function add_debug_info() {
        if (!defined('OAUTH_DEBUG') || !OAUTH_DEBUG) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $debug_helper = new DebugHelper();
        $active_tokens = $debug_helper->get_active_tokens();
        $security_status = $debug_helper->get_security_status();

        include plugin_dir_path(dirname(__FILE__)) . 'templates/debug-info.php';
    }
} 