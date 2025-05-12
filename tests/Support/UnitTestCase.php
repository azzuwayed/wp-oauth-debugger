<?php

namespace WP_OAuth_Debugger\Tests\Support;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case for unit tests.
 *
 * This uses Brain\Monkey without loading the WordPress environment
 * to enable true isolation for unit tests.
 */
abstract class UnitTestCase extends PHPUnitTestCase {
    use MockeryPHPUnitIntegration;

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
     * Create a mock of a class.
     *
     * @param string $class The class to mock.
     * @return \Mockery\MockInterface
     */
    protected function mock($class) {
        return \Mockery::mock($class);
    }

    /**
     * Create a spy of a class.
     *
     * @param string $class The class to spy on.
     * @return \Mockery\MockInterface
     */
    protected function spy($class) {
        return \Mockery::spy($class);
    }

    /**
     * Assert string contains text.
     */
    public function assertStringContains($needle, $haystack) {
        $this->assertStringContainsString($needle, $haystack);
    }
}
