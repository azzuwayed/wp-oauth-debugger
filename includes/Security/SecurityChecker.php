<?php

namespace WP_OAuth_Debugger\Security;

use WP_OAuth_Debugger\Debug\DebugHelper;

/**
 * Handles security analysis for OAuth implementations.
 */
class SecurityChecker {
    /**
     * @var DebugHelper
     */
    private $debug_helper;

    /**
     * Constructor
     */
    public function __construct() {
        $this->debug_helper = new DebugHelper();
    }

    /**
     * Get the current security status of the OAuth implementation.
     *
     * @return array Security status information
     */
    public function get_security_status() {
        // This is a simplified placeholder implementation
        // In a real implementation, this would analyze the actual OAuth configuration

        $logs = $this->debug_helper->get_logs();
        $failed_logins = 0;
        $last_scan_timestamp = get_option('oauth_debugger_last_security_scan', 0);

        // Count failed logins in the last 24 hours
        foreach ($logs as $log) {
            if (
                $log['level'] === 'ERROR' &&
                strpos($log['message'], 'authentication failed') !== false &&
                strtotime($log['timestamp']) > time() - (24 * 60 * 60)
            ) {
                $failed_logins++;
            }
        }

        $status = 'secure'; // Default status
        $security_issues = $this->get_security_issues();

        if (!empty($security_issues)) {
            $has_high_severity = false;
            $has_medium_severity = false;

            foreach ($security_issues as $issue) {
                if ($issue['severity'] === 'high') {
                    $has_high_severity = true;
                    break;
                } elseif ($issue['severity'] === 'medium') {
                    $has_medium_severity = true;
                }
            }

            if ($has_high_severity) {
                $status = 'danger';
            } elseif ($has_medium_severity) {
                $status = 'warning';
            } else {
                $status = 'info'; // Only low severity issues
            }
        }

        return array(
            'status' => $status,
            'failed_logins' => $failed_logins,
            'issue_count' => count($security_issues),
            'last_scan' => date('Y-m-d H:i:s', $last_scan_timestamp ? $last_scan_timestamp : time())
        );
    }

    /**
     * Get a list of detected security issues.
     *
     * @return array List of security issues
     */
    public function get_security_issues() {
        // This is a placeholder implementation
        // In a real implementation, this would analyze the actual OAuth configuration and return real issues

        $issues = array();

        // Example issues for demonstration purposes
        if (!is_ssl()) {
            $issues[] = array(
                'title' => __('HTTPS is not enabled', 'wp-oauth-debugger'),
                'description' => __('Your site is not using HTTPS, which is required for secure OAuth operations.', 'wp-oauth-debugger'),
                'severity' => 'high',
                'recommendation' => __('Enable HTTPS for your website and ensure all OAuth endpoints use HTTPS.', 'wp-oauth-debugger'),
                'reference' => 'https://oauth.net/articles/authentication/#tls'
            );
        }

        // Check for state parameter usage
        $logs = $this->debug_helper->get_logs(100);
        $state_parameter_used = false;
        foreach ($logs as $log) {
            if (
                strpos($log['message'], 'state parameter') !== false ||
                (isset($log['context']) && is_array($log['context']) && isset($log['context']['state']))
            ) {
                $state_parameter_used = true;
                break;
            }
        }

        if (!$state_parameter_used) {
            $issues[] = array(
                'title' => __('State parameter not detected', 'wp-oauth-debugger'),
                'description' => __('The state parameter does not appear to be used in OAuth requests, which can make your implementation vulnerable to CSRF attacks.', 'wp-oauth-debugger'),
                'severity' => 'medium',
                'recommendation' => __('Implement the state parameter in all authorization requests and verify it on callback.', 'wp-oauth-debugger'),
                'reference' => 'https://oauth.net/articles/authentication/#state'
            );
        }

        // Check for token expiration
        $tokens = $this->debug_helper->get_active_tokens();
        $long_lived_tokens = 0;
        foreach ($tokens as $token) {
            if (
                empty($token['expires_at']) ||
                $token['expires_at'] === '0000-00-00 00:00:00' ||
                strtotime($token['expires_at']) > time() + (24 * 60 * 60 * 30) // 30 days
            ) {
                $long_lived_tokens++;
            }
        }

        if ($long_lived_tokens > 0) {
            $issues[] = array(
                'title' => sprintf(__('Long-lived tokens detected (%d)', 'wp-oauth-debugger'), $long_lived_tokens),
                'description' => __('Some access tokens have very long or no expiration times, which poses a security risk if compromised.', 'wp-oauth-debugger'),
                'severity' => 'low',
                'recommendation' => __('Use shorter expiration times for access tokens (e.g., 1 hour) and implement refresh token rotation.', 'wp-oauth-debugger'),
                'reference' => 'https://oauth.net/articles/authentication/#token-lifetime'
            );
        }

        return $issues;
    }

    /**
     * Run a security scan of the OAuth implementation.
     *
     * @return array Scan results
     */
    public function run_security_scan() {
        // Record scan time
        update_option('oauth_debugger_last_security_scan', time());

        // Get current issues
        $issues = $this->get_security_issues();

        return array(
            'issues' => $issues,
            'count' => count($issues),
            'timestamp' => current_time('mysql')
        );
    }
}
