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
     * Create a test user with a unique username and email.
     *
     * @param array $args Optional. User arguments.
     * @return int User ID.
     * @throws \Exception If user creation fails.
     */
    protected function createTestUser($args = []): int {
        // Create unique identifier for this test user
        $unique_id = uniqid();

        $defaults = [
            'user_login' => 'testuser_' . $unique_id,
            'user_pass'  => 'testpass',
            'user_email' => 'test_' . $unique_id . '@example.com',
            'role'       => 'administrator',
        ];

        $args = wp_parse_args($args, $defaults);

        // If custom login or email was provided, make them unique too
        if (isset($args['user_login']) && strpos($args['user_login'], 'testuser_') !== 0) {
            $args['user_login'] = $args['user_login'] . '_' . $unique_id;
        }

        if (isset($args['user_email']) && strpos($args['user_email'], 'test_') !== 0) {
            $email_parts = explode('@', $args['user_email']);
            $args['user_email'] = $email_parts[0] . '_' . $unique_id . '@' . ($email_parts[1] ?? 'example.com');
        }

        $result = wp_insert_user($args);

        if (is_wp_error($result)) {
            throw new \Exception('Failed to create test user: ' . $result->get_error_message());
        }

        return $result;
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
