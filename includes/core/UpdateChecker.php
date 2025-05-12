<?php

namespace WP_OAuth_Debugger\Core;

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

        // Check if the update checker class exists
        if (!class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
            // Log error and exit gracefully
            error_log('WP OAuth Debugger: Update checker library not found. Updates will not be available.');
            return;
        }

        try {
            // Initialize the update checker
            $update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                sprintf(
                    'https://github.com/%s/%s/',
                    self::GITHUB_USERNAME,
                    self::GITHUB_REPO
                ),
                WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'wp-oauth-debugger.php',
                'wp-oauth-debugger'
            );

            // Set the branch that contains the stable release
            if (method_exists($update_checker, 'setBranch')) {
                $update_checker->setBranch('main');
            }

            // Set the update check interval - handle different API versions
            self::configureCheckInterval($update_checker);

            // Get current plugin version
            $current_version = defined('WP_OAUTH_DEBUGGER_VERSION') ? WP_OAUTH_DEBUGGER_VERSION : '0.0.0';

            // Add custom update message
            add_filter(
                'puc_request_info_result-wp-oauth-debugger',
                function ($update) use ($current_version) {
                    if (is_object($update) && isset($update->sections)) {
                        $update->sections['changelog'] = self::get_changelog($current_version);
                        $update->sections['description'] = self::get_description();
                    } elseif (is_array($update) && isset($update['sections'])) {
                        $update['sections']['changelog'] = self::get_changelog($current_version);
                        $update['sections']['description'] = self::get_description();
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

            // Add upgrade notice based on changelog
            add_action('in_plugin_update_message-wp-oauth-debugger/wp-oauth-debugger.php', function ($plugin_data, $response) {
                // Check if there are important notices in the changelog
                if (!empty($response->upgrade_notice)) {
                    echo '<br /><span style="color: #ff0000; font-weight: bold;">' .
                        wp_kses_post($response->upgrade_notice) .
                        '</span>';
                }
            }, 10, 2);
        } catch (\Exception $e) {
            error_log('WP OAuth Debugger: Update checker error: ' . $e->getMessage());
        }
    }

    /**
     * Configure the check interval based on available API
     *
     * @param object $update_checker The update checker instance
     */
    private static function configureCheckInterval($update_checker) {
        $interval = self::UPDATE_CHECK_INTERVAL * 3600;

        // Try different approaches to set the check interval
        if (method_exists($update_checker, 'setCheckInterval')) {
            // Direct method if available (older versions)
            $update_checker->setCheckInterval($interval);
        } elseif (
            property_exists($update_checker, 'scheduler') &&
            is_object($update_checker->scheduler) &&
            method_exists($update_checker->scheduler, 'setCheckInterval')
        ) {
            // Access scheduler directly (newer versions)
            $update_checker->scheduler->setCheckInterval($interval);
        } elseif (
            class_exists('\YahnisElsts\PluginUpdateChecker\v5p5\Scheduler') &&
            method_exists('\YahnisElsts\PluginUpdateChecker\v5p5\Scheduler', 'normalizeCheckInterval')
        ) {
            // Log that we couldn't set the interval
            error_log('WP OAuth Debugger: Could not set update check interval - using default.');
        }
    }

    /**
     * Get plugin description for the update information.
     *
     * @return string
     */
    private static function get_description() {
        $readme_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'README.md';

        if (file_exists($readme_file)) {
            $readme = file_get_contents($readme_file);

            // Try to extract the description section
            $matches = [];
            if (preg_match('/^#+\s*Description\s*$(.*?)^#+\s/ms', $readme, $matches)) {
                return self::convert_markdown_to_html($matches[1]);
            }

            // Fallback to first paragraph if no Description section
            $matches = [];
            if (preg_match('/^(?:#.*?\n\n)?(.+?)(?=\n\n)/s', $readme, $matches)) {
                return self::convert_markdown_to_html($matches[1]);
            }
        }

        return 'A comprehensive debugging and monitoring tool for OAuth implementations in WordPress.';
    }

    /**
     * Get the changelog for the update notification.
     *
     * @param string $current_version Current plugin version.
     * @return string
     */
    public static function get_changelog($current_version = '') {
        $changelog_file = WP_OAUTH_DEBUGGER_PLUGIN_DIR . 'CHANGELOG.md';

        if (file_exists($changelog_file)) {
            try {
                $changelog = file_get_contents($changelog_file);

                if (false === $changelog) {
                    return self::get_fallback_changelog_message();
                }

                // If we have a current version, try to show only relevant changes (newer than current)
                if (!empty($current_version)) {
                    $changelog = self::filter_changelog_by_version($changelog, $current_version);
                }

                return self::convert_markdown_to_html($changelog);
            } catch (\Exception $e) {
                error_log('WP OAuth Debugger: Error reading changelog: ' . $e->getMessage());
                return self::get_fallback_changelog_message();
            }
        }

        return self::get_fallback_changelog_message();
    }

    /**
     * Filter changelog to only show versions newer than current.
     *
     * @param string $changelog Full changelog text.
     * @param string $current_version Current version to filter from.
     * @return string Filtered changelog.
     */
    private static function filter_changelog_by_version($changelog, $current_version) {
        // Split changelog into lines to process
        $lines = explode("\n", $changelog);
        $filtered_lines = [];
        $include_section = true;
        $current_section_version = '';

        // Always include header
        $filtered_lines[] = "# Changelog";
        $filtered_lines[] = "";

        foreach ($lines as $line) {
            // Check if line is a version header
            $matches = [];
            if (preg_match('/^## \[([\d\.]+|Unreleased)\]/i', $line, $matches)) {
                $version_str = $matches[1];

                // Always include "Unreleased" section
                if (strtolower($version_str) === 'unreleased') {
                    $include_section = true;
                    $current_section_version = '';
                } else {
                    // For numbered versions, compare with current
                    $current_section_version = $version_str;
                    $include_section = (version_compare($version_str, $current_version, '>'));
                }
            }

            // Include line if current section should be included
            if ($include_section) {
                $filtered_lines[] = $line;
            }
        }

        // If we didn't include any version sections, return a message
        if (count($filtered_lines) <= 2) {
            $filtered_lines[] = "No new changes since version $current_version.";
        }

        return implode("\n", $filtered_lines);
    }

    /**
     * Convert markdown to HTML with improved formatting.
     *
     * @param string $markdown Markdown text to convert.
     * @return string HTML output.
     */
    private static function convert_markdown_to_html($markdown) {
        // Clean up whitespace
        $markdown = trim($markdown);

        // Convert headings
        $markdown = preg_replace('/^#\s+(.*?)$/m', '<h1>$1</h1>', $markdown);
        $markdown = preg_replace('/^##\s+(.*?)$/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^###\s+(.*?)$/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^####\s+(.*?)$/m', '<h4>$1</h4>', $markdown);

        // Process lists
        $markdown = preg_replace('/^(\s*)-\s+(.*?)$/m', '$1<li>$2</li>', $markdown);
        $markdown = preg_replace('/^(\s*)\*\s+(.*?)$/m', '$1<li>$2</li>', $markdown);
        $markdown = preg_replace('/^(\s*)\d+\.\s+(.*?)$/m', '$1<li>$2</li>', $markdown);

        // Group list items
        $markdown = preg_replace('/<\/li>\n<li>/s', '</li><li>', $markdown);
        $markdown = preg_replace('/(<li>.*?<\/li>)/s', '<ul>$1</ul>', $markdown);
        $markdown = str_replace('</ul><ul>', '', $markdown);

        // Process links
        $markdown = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" target="_blank">$1</a>', $markdown);

        // Process emphasis
        $markdown = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $markdown);
        $markdown = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/_(.*?)_/', '<em>$1</em>', $markdown);

        // Process code
        $markdown = preg_replace('/`(.*?)`/', '<code>$1</code>', $markdown);

        // Convert newlines to paragraphs
        $blocks = preg_split('/\n\n+/', $markdown);
        $markdown = '';

        foreach ($blocks as $block) {
            if (!preg_match('/^<(h[1-6]|ul|ol|table|pre|blockquote)/', $block)) {
                $block = '<p>' . str_replace("\n", '<br>', $block) . '</p>';
            }
            $markdown .= $block . "\n\n";
        }

        // Final cleanup
        $markdown = str_replace('<p><h', '<h', $markdown);
        $markdown = preg_replace('/<\/h([1-6])><\/p>/', '</h$1>', $markdown);
        $markdown = str_replace('<p><ul>', '<ul>', $markdown);
        $markdown = str_replace('</ul></p>', '</ul>', $markdown);

        return $markdown;
    }

    /**
     * Get fallback message when changelog is not available.
     *
     * @return string
     */
    private static function get_fallback_changelog_message() {
        return sprintf(
            '<p>%s</p>',
            sprintf(
                __('For a complete list of changes, please visit the <a href="%s" target="_blank">GitHub repository</a>.', 'wp-oauth-debugger'),
                sprintf('https://github.com/%s/%s/releases', self::GITHUB_USERNAME, self::GITHUB_REPO)
            )
        );
    }
}
