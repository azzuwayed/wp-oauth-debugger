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
    private const GITHUB_USERNAME       = 'azzuwayed';
    private const GITHUB_REPO           = 'wp-oauth-debugger';
    private const UPDATE_CHECK_INTERVAL = 12; // hours

    /**
     * Initialize the update checker.
     */
    public static function init() {
        // Only check for updates if we're in the admin area
        if (! is_admin()) {
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
        add_filter(
            'puc_request_info_result-wp-oauth-debugger',
            function ($update) {
                if (is_object($update) && isset($update->sections)) {
                    $update->sections['changelog'] = self::get_changelog();
                } elseif (is_array($update) && isset($update['sections'])) {
                    $update['sections']['changelog'] = self::get_changelog();
                }
                return $update;
            }
        );

        // Add custom query args
        add_filter(
            'puc_request_info_query_args-wp-oauth-debugger',
            function ($queryArgs) {
                $queryArgs['plugin'] = 'wp-oauth-debugger';
                return $queryArgs;
            }
        );
    }

    /**
     * Get the changelog for the update notification.
     *
     * @return string
     */
    public static function get_changelog() {
        $changelog_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'CHANGELOG.md';

        if (file_exists($changelog_file)) {
            $changelog = file_get_contents($changelog_file);

            // Simple markdown to HTML conversion
            // First, do the headings
            $changelog = preg_replace('/^#\s+(.*?)$/m', '<h1>$1</h1>', $changelog);
            $changelog = preg_replace('/^##\s+(.*?)$/m', '<h2>$1</h2>', $changelog);
            $changelog = preg_replace('/^###\s+(.*?)$/m', '<h3>$1</h3>', $changelog);

            // Then, do the list items
            $changelog = preg_replace('/^-\s+(.*?)$/m', '<li>$1</li>', $changelog);

            // Convert newlines to paragraphs, but not within headings or list items
            $changelog = preg_replace('/(?<!<\/h[1-6]>|<\/li>)\n\n(?!<h[1-6]>|<li>)/s', '</p><p>', $changelog);
            $changelog = '<p>' . $changelog . '</p>';

            // Clean up any double paragraph tags
            $changelog = str_replace('<p><p>', '<p>', $changelog);
            $changelog = str_replace('</p></p>', '</p>', $changelog);

            // Make sure headings and list items aren't wrapped in paragraph tags
            $changelog = str_replace('<p><h', '<h', $changelog);
            $changelog = str_replace('</h1></p>', '</h1>', $changelog);
            $changelog = str_replace('</h2></p>', '</h2>', $changelog);
            $changelog = str_replace('</h3></p>', '</h3>', $changelog);
            $changelog = str_replace('<p><li>', '<li>', $changelog);
            $changelog = str_replace('</li></p>', '</li>', $changelog);

            return $changelog;
        }

        return sprintf(
            __('For a complete list of changes, please visit the <a href="%s" target="_blank">GitHub repository</a>.', 'wp-oauth-debugger'),
            sprintf('https://github.com/%s/%s/releases', self::GITHUB_USERNAME, self::GITHUB_REPO)
        );
    }
}
