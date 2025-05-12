<?php

namespace WP_OAuth_Debugger\Tests\Support;

use Mockery;

/**
 * Base test case for unit tests.
 */
abstract class UnitTestCase extends TestCase {
    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        Mockery::setUp();
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a mock of a class.
     *
     * @param string $class The class to mock.
     * @return \Mockery\MockInterface
     */
    protected function mock($class) {
        return Mockery::mock($class);
    }

    /**
     * Create a spy of a class.
     *
     * @param string $class The class to spy on.
     * @return \Mockery\MockInterface
     */
    protected function spy($class) {
        return Mockery::spy($class);
    }
}
