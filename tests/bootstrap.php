<?php

/**
 * WordPress OAuth Debugger Test Bootstrap
 *
 * @package WP_OAuth_Debugger
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load PHPUnit Polyfills
define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname(__DIR__) . '/vendor/yoast/phpunit-polyfills');
require_once WP_TESTS_PHPUNIT_POLYFILLS_PATH . '/phpunitpolyfills-autoload.php';

// Then, load WordPress test environment
$_tests_dir = getenv('WP_TESTS_DIR');

if (! $_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

if (! file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    require dirname(__DIR__) . '/wp-oauth-debugger.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// Load our test case classes
require_once __DIR__ . '/Support/TestCase.php';
require_once __DIR__ . '/Support/UnitTestCase.php';
require_once __DIR__ . '/Support/IntegrationTestCase.php';
