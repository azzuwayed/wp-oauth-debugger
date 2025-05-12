<?php

namespace WP_OAuth_Debugger\Tests\Support;

use WP_UnitTestCase;
use Brain\Monkey;

/**
 * Base test case for all tests.
 */
abstract class TestCase extends WP_UnitTestCase {
    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Assert that a string contains another string.
     *
     * @param string $needle   The string to search for.
     * @param string $haystack The string to search in.
     * @param string $message  Optional. Message to display on failure.
     */
    protected function assertStringContains($needle, $haystack, $message = ''): void {
        $this->assertStringContainsString($needle, $haystack, $message);
    }

    /**
     * Assert that a string does not contain another string.
     *
     * @param string $needle   The string to search for.
     * @param string $haystack The string to search in.
     * @param string $message  Optional. Message to display on failure.
     */
    protected function assertStringNotContains($needle, $haystack, $message = ''): void {
        $this->assertStringNotContainsString($needle, $haystack, $message);
    }
}
