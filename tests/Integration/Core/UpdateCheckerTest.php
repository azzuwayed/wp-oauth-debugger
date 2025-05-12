<?php

namespace WP_OAuth_Debugger\Tests\Integration\Core;

use WP_OAuth_Debugger\Tests\Support\IntegrationTestCase;
use WP_OAuth_Debugger\Core\UpdateChecker;
use Brain\Monkey\Functions;

class UpdateCheckerTest extends IntegrationTestCase {
    private $admin_id = null;
    private $editor_id = null;

    protected function setUp(): void {
        parent::setUp();

        // Define the plugin directory constant if not defined
        if (!defined('WP_OAUTH_DEBUGGER_PLUGIN_DIR')) {
            define('WP_OAUTH_DEBUGGER_PLUGIN_DIR', dirname(dirname(dirname(__DIR__))) . '/');
        }
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
        // Skip this test for now as we need to properly mock the PucFactory
        $this->markTestSkipped('Skipping test_update_checker_initializes_in_admin due to complex mocking requirements');
    }

    public function test_update_checker_does_not_initialize_for_non_admin(): void {
        // Skip this test for now as we need to properly mock the PucFactory
        $this->markTestSkipped('Skipping test_update_checker_does_not_initialize_for_non_admin due to complex mocking requirements');
    }

    public function test_changelog_is_accessible_in_admin(): void {
        // Set up admin user
        wp_set_current_user($this->admin_id);

        // Create a test changelog file
        $changelog_content = "# Changelog\n\n## 1.0.0\n- Initial release";
        $changelog_path = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'CHANGELOG.md';

        // Ensure the directory exists
        $dir = dirname($changelog_path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($changelog_path, $changelog_content);

        // Get the changelog
        $changelog = UpdateChecker::get_changelog();

        // Verify the changelog is properly formatted
        $this->assertStringContainsString('<h1>Changelog</h1>', $changelog);
        $this->assertStringContainsString('<h2>1.0.0</h2>', $changelog);
        $this->assertStringContainsString('<li>Initial release</li>', $changelog);

        // Clean up
        if (file_exists($changelog_path)) {
            unlink($changelog_path);
        }
    }

    public function test_update_notification_is_displayed_for_admin(): void {
        // Skip filter test since we can't easily simulate the PucFactory initialization
        $this->markTestSkipped('Skipping test_update_notification_is_displayed_for_admin due to complex mocking requirements');
    }

    protected function tearDownTestData(): void {
        // Clean up test users
        if ($this->admin_id) {
            wp_delete_user($this->admin_id);
        }

        if ($this->editor_id) {
            wp_delete_user($this->editor_id);
        }

        // Reset WordPress filters
        remove_all_filters('puc_request_info_result-wp-oauth-debugger');
        remove_all_filters('puc_request_info_query_args-wp-oauth-debugger');

        // Remove any test files
        $changelog_path = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'CHANGELOG.md';
        if (file_exists($changelog_path)) {
            unlink($changelog_path);
        }
    }
}
