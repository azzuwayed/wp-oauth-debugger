<?php

/**
 * Plugin Constants
 *
 * This file defines all the constants used in the plugin.
 *
 * @package WP_OAuth_Debugger
 */

// Plugin Version.
if (! defined('WP_OAUTH_DEBUGGER_VERSION')) {
    define('WP_OAUTH_DEBUGGER_VERSION', '1.0.0');
}

// Plugin Directory.
if (! defined('WP_OAUTH_DEBUGGER_PLUGIN_DIR')) {
    define('WP_OAUTH_DEBUGGER_PLUGIN_DIR', plugin_dir_path(dirname(__FILE__)));
}

// Plugin URL.
if (! defined('WP_OAUTH_DEBUGGER_PLUGIN_URL')) {
    define('WP_OAUTH_DEBUGGER_PLUGIN_URL', plugin_dir_url(dirname(__FILE__)));
}

// Debug Mode.
if (! defined('WP_OAUTH_DEBUGGER_DEBUG')) {
    define('WP_OAUTH_DEBUGGER_DEBUG', true);
}

// Database Connection Constants.
if (! defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

// Additional WordPress Constants.
if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/');
}

if (! defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

if (! defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (! defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}
