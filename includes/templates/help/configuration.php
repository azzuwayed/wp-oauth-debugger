<?php

/**
 * Configuration Help Page
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="oauth-debugger-help-section">
    <h2><?php _e('Configuration Guide', 'wp-oauth-debugger'); ?></h2>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Basic Configuration', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('Add these constants to your wp-config.php file to configure the plugin:', 'wp-oauth-debugger'); ?></p>

        <div class="example-box">
            <h4><?php _e('Development Environment', 'wp-oauth-debugger'); ?></h4>
            <pre>// Enable debugging
define('OAUTH_DEBUG', true);

// Set minimum log level
define('OAUTH_DEBUG_LOG_LEVEL', 'debug');

// Set log retention period (days)
define('OAUTH_DEBUG_LOG_RETENTION', 7);

// Enable PKCE support
define('OAUTH_PKCE_ENABLED', true);

// Configure rate limiting
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 120); // requests per minute</pre>
        </div>

        <div class="example-box">
            <h4><?php _e('Production Environment', 'wp-oauth-debugger'); ?></h4>
            <pre>// Enable debugging with limited logging
define('OAUTH_DEBUG', true);
define('OAUTH_DEBUG_LOG_LEVEL', 'warning');

// Set shorter log retention
define('OAUTH_DEBUG_LOG_RETENTION', 3);

// Enable security features
define('OAUTH_PKCE_ENABLED', true);
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 60); // stricter rate limit</pre>
        </div>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Common Scenarios', 'wp-oauth-debugger'); ?></h3>

        <div class="example-box">
            <h4><?php _e('Single-Page Application (SPA)', 'wp-oauth-debugger'); ?></h4>
            <pre>// Enable CORS for SPA
add_action('init', function() {
    header('Access-Control-Allow-Origin: https://your-spa-domain.com');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
});

// Enable PKCE (required for SPAs)
define('OAUTH_PKCE_ENABLED', true);

// Configure token lifetime
define('OAUTH_ACCESS_TOKEN_LIFETIME', 3600); // 1 hour
define('OAUTH_REFRESH_TOKEN_LIFETIME', 2592000); // 30 days

// Enable security features
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 60);</pre>
            <div class="note">
                <strong><?php _e('Note:', 'wp-oauth-debugger'); ?></strong>
                <?php _e('Replace "https://your-spa-domain.com" with your actual SPA domain.', 'wp-oauth-debugger'); ?>
            </div>
        </div>

        <div class="example-box">
            <h4><?php _e('Mobile Application', 'wp-oauth-debugger'); ?></h4>
            <pre>// Enable PKCE (required for mobile apps)
define('OAUTH_PKCE_ENABLED', true);

// Configure token lifetime
define('OAUTH_ACCESS_TOKEN_LIFETIME', 3600); // 1 hour
define('OAUTH_REFRESH_TOKEN_LIFETIME', 2592000); // 30 days

// Enable security features
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 30); // stricter rate limit for mobile

// Configure logging
define('OAUTH_DEBUG_LOG_LEVEL', 'warning');
define('OAUTH_DEBUG_LOG_RETENTION', 3);</pre>
        </div>

        <div class="example-box">
            <h4><?php _e('Server-to-Server Integration', 'wp-oauth-debugger'); ?></h4>
            <pre>// Disable PKCE (not needed for server-to-server)
define('OAUTH_PKCE_ENABLED', false);

// Configure token lifetime
define('OAUTH_ACCESS_TOKEN_LIFETIME', 7200); // 2 hours
define('OAUTH_REFRESH_TOKEN_LIFETIME', 0); // No refresh tokens

// Enable security features
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 100);

// Configure logging
define('OAUTH_DEBUG_LOG_LEVEL', 'info');
define('OAUTH_DEBUG_LOG_RETENTION', 7);</pre>
        </div>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Security Hardening', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('Additional security measures for production environments:', 'wp-oauth-debugger'); ?></p>

        <div class="example-box">
            <h4><?php _e('Enhanced Security Configuration', 'wp-oauth-debugger'); ?></h4>
            <pre>// Enable all security features
define('OAUTH_PKCE_ENABLED', true);
define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 30);

// Configure secure token handling
define('OAUTH_ACCESS_TOKEN_LIFETIME', 1800); // 30 minutes
define('OAUTH_REFRESH_TOKEN_LIFETIME', 604800); // 7 days
define('OAUTH_TOKEN_ROTATION', true); // Enable token rotation

// Configure logging
define('OAUTH_DEBUG_LOG_LEVEL', 'warning');
define('OAUTH_DEBUG_LOG_RETENTION', 3);
define('OAUTH_DEBUG_MASK_SENSITIVE', true);

// Add security headers
add_action('init', function() {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
});</pre>
        </div>

        <div class="warning">
            <strong><?php _e('Important:', 'wp-oauth-debugger'); ?></strong>
            <?php _e('These security measures may impact user experience. Test thoroughly before implementing in production.', 'wp-oauth-debugger'); ?>
        </div>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('WordPress Settings', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('Configure the plugin through the WordPress admin interface:', 'wp-oauth-debugger'); ?></p>

        <table>
            <thead>
                <tr>
                    <th><?php _e('Setting', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Description', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Default', 'wp-oauth-debugger'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php _e('Log Level', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Minimum level of logs to record', 'wp-oauth-debugger'); ?></td>
                    <td>info</td>
                </tr>
                <tr>
                    <td><?php _e('Log Retention', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Number of days to keep logs', 'wp-oauth-debugger'); ?></td>
                    <td>7</td>
                </tr>
                <tr>
                    <td><?php _e('Rate Limit', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Requests per minute', 'wp-oauth-debugger'); ?></td>
                    <td>60</td>
                </tr>
                <tr>
                    <td><?php _e('Allowed Roles', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Roles that can access debug features', 'wp-oauth-debugger'); ?></td>
                    <td>administrator</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
