<?php

/**
 * Unit Tests Bootstrap
 *
 * This bootstrap file is specifically for unit tests that don't require WordPress.
 * It sets up Brain\Monkey and other mocking tools without loading WordPress.
 */

// Load the Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load test helpers
require_once __DIR__ . '/Support/TestCase.php';
require_once __DIR__ . '/Support/UnitTestCase.php';

// Define common WordPress constants for unit tests
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', dirname(dirname(__DIR__)));
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', dirname(dirname(__DIR__)));
}

/**
 * Setup functions to mock WordPress functions
 * This avoids "function already defined" errors
 */
function define_wp_functions() {
    if (!function_exists('is_admin')) {
        function is_admin() {
            return false;
        }
    }

    if (!function_exists('get_site_option')) {
        function get_site_option($option, $default = false) {
            return $default;
        }
    }

    if (!function_exists('update_site_option')) {
        function update_site_option($option, $value) {
            return true;
        }
    }

    if (!function_exists('file_exists')) {
        function file_exists($file) {
            return false;
        }
    }

    if (!function_exists('file_get_contents')) {
        function file_get_contents($file) {
            return '';
        }
    }

    if (!function_exists('esc_url')) {
        function esc_url($url) {
            return $url;
        }
    }
}

// Define these functions only if not already defined
define_wp_functions();
