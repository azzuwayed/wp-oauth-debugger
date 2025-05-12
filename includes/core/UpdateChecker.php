<?php

namespace WP_OAuth_Debugger\Core;

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Handles plugin updates from GitHub.
 */
class UpdateChecker {
    /**
     * GitHub repository details.
     */
    private const GITHUB_USERNAME = 'azzuwayed';
    private const GITHUB_REPO = 'wp-oauth-debugger';
    private const UPDATE_CHECK_INTERVAL = 12; // hours

    /**
     * Initialize the update checker.
     */
    public static function init() {
        // Only check for updates if we're in the admin area
        if (!is_admin()) {
            return;
        }

        // Initialize the update checker
        $update_checker = PucFactory::buildUpdateChecker(
            sprintf(
                'https://github.com/%s/%s/',
                self::GITHUB_USERNAME,
                self::GITHUB_REPO
            ),
            WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'wp-oauth-debugger.php',
            'wp-oauth-debugger'
        );

        // Set the branch that contains the stable release
        $update_checker->setBranch('main');

        // Optional: Set the authentication token for private repositories
        // $update_checker->setAuthentication('your-github-token');

        // Set the update check interval
        $update_checker->setCheckInterval(self::UPDATE_CHECK_INTERVAL * 3600);

        // Add custom update message
        add_filter('puc_pre_inject_update-' . $update_checker->getUniqueName(), function ($update) {
            $update->sections['changelog'] = self::get_changelog();
            return $update;
        });

        // Add custom update notification message
        add_filter('puc_pre_inject_info-' . $update_checker->getUniqueName(), function ($info) {
            if (isset($info->sections['changelog'])) {
                $info->sections['changelog'] = self::get_changelog();
            }
            return $info;
        });
    }

    /**
     * Get the changelog for the update notification.
     *
     * @return string
     */
    private static function get_changelog() {
        $changelog_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'CHANGELOG.md';

        if (file_exists($changelog_file)) {
            $changelog = file_get_contents($changelog_file);
            // Convert markdown to HTML
            $changelog = wpautop($changelog);
            return $changelog;
        }

        return sprintf(
            __('For a complete list of changes, please visit the <a href="%s" target="_blank">GitHub repository</a>.', 'wp-oauth-debugger'),
            sprintf('https://github.com/%s/%s/releases', self::GITHUB_USERNAME, self::GITHUB_REPO)
        );
    }
}
