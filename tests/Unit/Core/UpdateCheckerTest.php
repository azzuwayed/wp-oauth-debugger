<?php

namespace WP_OAuth_Debugger\Tests\Unit\Core;

use WP_OAuth_Debugger\Tests\Support\UnitTestCase;
use WP_OAuth_Debugger\Core\UpdateChecker;
use Brain\Monkey\Functions;
use Mockery;

class UpdateCheckerTest extends UnitTestCase {
    private $update_checker;

    protected function setUp(): void {
        parent::setUp();

        // Mock WordPress functions
        Functions\when('is_admin')->justReturn(true);
        Functions\when('get_site_option')->justReturn([]);
        Functions\when('update_site_option')->justReturn(true);

        // Create a partial mock of UpdateChecker
        $this->update_checker = Mockery::mock(UpdateChecker::class)->makePartial();
    }

    public function test_init_checks_admin_area(): void {
        // Test that init only runs in admin area
        Functions\expect('is_admin')->once()->andReturn(false);
        $this->update_checker->init();

        // Verify no update checker was created
        $this->assertFalse($this->update_checker->hasUpdateChecker());
    }

    public function test_init_creates_update_checker(): void {
        // Mock the PucFactory
        $factory = Mockery::mock('alias:YahnisElsts\PluginUpdateChecker\v5\PucFactory');
        $factory->shouldReceive('buildUpdateChecker')
            ->once()
            ->withArgs(function ($url, $file, $branch) {
                return $url === 'https://github.com/azzuwayed/wp-oauth-debugger' &&
                    $file === WP_PLUGIN_DIR . '/wp-oauth-debugger/wp-oauth-debugger.php' &&
                    $branch === 'main';
            })
            ->andReturn(Mockery::mock('YahnisElsts\PluginUpdateChecker\v5\Plugin\UpdateChecker'));

        $this->update_checker->init();

        // Verify update checker was created
        $this->assertTrue($this->update_checker->hasUpdateChecker());
    }

    public function test_get_changelog_returns_html(): void {
        // Mock file operations
        Functions\when('file_exists')->justReturn(true);
        Functions\when('file_get_contents')->justReturn('# Changelog\n\n## 1.0.0\n- Initial release');

        $changelog = $this->update_checker->get_changelog();

        $this->assertStringContains('<h1>Changelog</h1>', $changelog);
        $this->assertStringContains('<h2>1.0.0</h2>', $changelog);
        $this->assertStringContains('<li>Initial release</li>', $changelog);
    }

    public function test_get_changelog_returns_link_when_file_missing(): void {
        // Mock file operations
        Functions\when('file_exists')->justReturn(false);
        Functions\when('esc_url')->justReturn('https://github.com/azzuwayed/wp-oauth-debugger');

        $changelog = $this->update_checker->get_changelog();

        $this->assertStringContains('https://github.com/azzuwayed/wp-oauth-debugger', $changelog);
    }

    public function test_update_check_interval_is_set(): void {
        $factory = Mockery::mock('alias:YahnisElsts\PluginUpdateChecker\v5\PucFactory');
        $checker = Mockery::mock('YahnisElsts\PluginUpdateChecker\v5\Plugin\UpdateChecker');

        $factory->shouldReceive('buildUpdateChecker')->andReturn($checker);
        $checker->shouldReceive('setBranch')->once()->with('main');
        $checker->shouldReceive('setAuthentication')->once();
        $checker->shouldReceive('setCheckPeriod')->once()->with(12 * 3600); // 12 hours in seconds

        $this->update_checker->init();
    }
}
