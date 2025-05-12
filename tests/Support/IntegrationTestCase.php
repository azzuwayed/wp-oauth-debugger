<?php

namespace WP_OAuth_Debugger\Tests\Support;

/**
 * Base test case for integration tests.
 */
abstract class IntegrationTestCase extends TestCase {
    /**
     * Set up the test environment.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->setUpTestData();
    }

    /**
     * Tear down the test environment.
     */
    protected function tearDown(): void {
        $this->tearDownTestData();
        parent::tearDown();
    }

    /**
     * Set up test data.
     */
    protected function setUpTestData(): void {
        // Override in child classes to set up test data
    }

    /**
     * Tear down test data.
     */
    protected function tearDownTestData(): void {
        // Override in child classes to clean up test data
    }

    /**
     * Create a test user.
     *
     * @param array $args Optional. User arguments.
     * @return int User ID.
     */
    protected function createTestUser($args = []): int {
        $defaults = [
            'user_login' => 'testuser',
            'user_pass'  => 'testpass',
            'user_email' => 'test@example.com',
            'role'       => 'administrator',
        ];

        $args = wp_parse_args($args, $defaults);
        return wp_insert_user($args);
    }

    /**
     * Create a test OAuth client.
     *
     * @param array $args Optional. Client arguments.
     * @return int Client ID.
     */
    protected function createTestClient($args = []): int {
        $defaults = [
            'client_id'     => 'test_client_' . uniqid(),
            'client_secret' => wp_generate_password(32, false),
            'name'          => 'Test Client',
            'redirect_uri'  => 'https://example.com/callback',
            'grant_types'   => ['authorization_code', 'refresh_token'],
            'scope'         => 'read write',
        ];

        $args = wp_parse_args($args, $defaults);
        // TODO: Implement client creation based on your plugin's structure
        return 0;
    }
}
