<?php

namespace WP_OAuth_Debugger\Tests\Integration\Core;

use WP_OAuth_Debugger\Tests\Support\IntegrationTestCase;
use WP_OAuth_Debugger\Core\UpdateChecker;

class UpdateCheckerTest extends IntegrationTestCase {
    private $update_checker;

    protected function setUp(): void {
        parent::setUp();
        $this->update_checker = new UpdateChecker();
    }

    protected function setUpTestData(): void {
        // Create a test admin user
        $this->admin_id = $this->createTestUser([
            'user_login' => 'admin',
            'user_email' => 'admin@example.com',
            'role' => 'administrator'
        ]);
    }

    public function test_update_checker_initializes_in_admin(): void {
        // Set up admin user
        wp_set_current_user($this->admin_id);

        // Initialize the update checker
        $this->update_checker->init();

        // Verify that WordPress update filters are added
        $this->assertTrue(has_filter('puc_request_info_result-wp-oauth-debugger'));
        $this->assertTrue(has_filter('puc_request_info_query_args-wp-oauth-debugger'));
    }

    public function test_update_checker_does_not_initialize_for_non_admin(): void {
        // Create and set up a non-admin user
        $user_id = $this->createTestUser([
            'user_login' => 'editor',
            'user_email' => 'editor@example.com',
            'role' => 'editor'
        ]);
        wp_set_current_user($user_id);

        // Initialize the update checker
        $this->update_checker->init();

        // Verify that WordPress update filters are not added
        $this->assertFalse(has_filter('puc_request_info_result-wp-oauth-debugger'));
        $this->assertFalse(has_filter('puc_request_info_query_args-wp-oauth-debugger'));
    }

    public function test_changelog_is_accessible_in_admin(): void {
        // Set up admin user
        wp_set_current_user($this->admin_id);

        // Create a test changelog file
        $changelog_content = "# Changelog\n\n## 1.0.0\n- Initial release";
        $changelog_path = WP_PLUGIN_DIR . '/wp-oauth-debugger/CHANGELOG.md';
        file_put_contents($changelog_path, $changelog_content);

        // Get the changelog
        $changelog = $this->update_checker->get_changelog();

        // Verify the changelog is properly formatted
        $this->assertStringContains('<h1>Changelog</h1>', $changelog);
        $this->assertStringContains('<h2>1.0.0</h2>', $changelog);
        $this->assertStringContains('<li>Initial release</li>', $changelog);

        // Clean up
        unlink($changelog_path);
    }

    public function test_update_notification_is_displayed_for_admin(): void {
        // Set up admin user
        wp_set_current_user($this->admin_id);

        // Initialize the update checker
        $this->update_checker->init();

        // Simulate an available update
        $update_data = [
            'version' => '1.0.1',
            'download_url' => 'https://github.com/azzuwayed/wp-oauth-debugger/releases/download/1.0.1/wp-oauth-debugger.zip',
            'sections' => [
                'changelog' => '# Changelog\n\n## 1.0.1\n- Bug fixes'
            ]
        ];

        // Apply the update data filter
        $filtered_data = apply_filters('puc_request_info_result-wp-oauth-debugger', $update_data);

        // Verify the update data is properly filtered
        $this->assertArrayHasKey('sections', $filtered_data);
        $this->assertArrayHasKey('changelog', $filtered_data['sections']);
        $this->assertStringContains('<h1>Changelog</h1>', $filtered_data['sections']['changelog']);
    }

    protected function tearDownTestData(): void {
        // Clean up test users
        if (isset($this->admin_id)) {
            wp_delete_user($this->admin_id);
        }

        // Remove any test files
        $changelog_path = WP_PLUGIN_DIR . '/wp-oauth-debugger/CHANGELOG.md';
        if (file_exists($changelog_path)) {
            unlink($changelog_path);
        }
    }
}
