<?php

/**
 * Features Help Page
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="oauth-debugger-help-section">
    <h2><?php _e('Plugin Features', 'wp-oauth-debugger'); ?></h2>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Security Scanner', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('The security scanner provides comprehensive analysis of your OAuth implementation:', 'wp-oauth-debugger'); ?></p>

        <h4><?php _e('Security Checks', 'wp-oauth-debugger'); ?></h4>
        <ul>
            <li>
                <strong><?php _e('PKCE Support', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Checks for PKCE (Proof Key for Code Exchange) implementation, which is crucial for securing public clients like mobile apps and SPAs.', 'wp-oauth-debugger'); ?></p>
                <div class="note">
                    <?php _e('Required for: Mobile applications, Single-page applications (SPAs), Public clients', 'wp-oauth-debugger'); ?>
                </div>
            </li>
            <li>
                <strong><?php _e('CORS Configuration', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Validates Cross-Origin Resource Sharing settings to ensure secure cross-domain communication.', 'wp-oauth-debugger'); ?></p>
                <div class="note">
                    <?php _e('Required when: OAuth client and server are on different domains', 'wp-oauth-debugger'); ?>
                </div>
            </li>
            <li>
                <strong><?php _e('Rate Limiting', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Monitors and enforces request rate limits to prevent abuse and brute force attacks.', 'wp-oauth-debugger'); ?></p>
                <div class="example-box">
                    <h4><?php _e('Default Configuration:', 'wp-oauth-debugger'); ?></h4>
                    <pre>Rate Limit: 60 requests per minute
Window: 60 seconds</pre>
                </div>
            </li>
        </ul>

        <h4><?php _e('Vulnerability Detection', 'wp-oauth-debugger'); ?></h4>
        <ul>
            <li>
                <strong><?php _e('CSRF Protection', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Checks for proper CSRF token implementation in OAuth endpoints.', 'wp-oauth-debugger'); ?></p>
            </li>
            <li>
                <strong><?php _e('Open Redirect', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Detects potential open redirect vulnerabilities in OAuth flows.', 'wp-oauth-debugger'); ?></p>
            </li>
            <li>
                <strong><?php _e('Token Exposure', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Identifies potential token exposure in logs or responses.', 'wp-oauth-debugger'); ?></p>
            </li>
            <li>
                <strong><?php _e('JWT Analysis', 'wp-oauth-debugger'); ?></strong>
                <p><?php _e('Analyzes JWT implementation for security best practices.', 'wp-oauth-debugger'); ?></p>
            </li>
        </ul>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Logging System', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('Comprehensive logging system for OAuth operations:', 'wp-oauth-debugger'); ?></p>

        <h4><?php _e('Log Levels', 'wp-oauth-debugger'); ?></h4>
        <table>
            <thead>
                <tr>
                    <th><?php _e('Level', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Description', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Use Case', 'wp-oauth-debugger'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>debug</td>
                    <td><?php _e('Detailed debugging information', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Development environment', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td>info</td>
                    <td><?php _e('General operational information', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Default production setting', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td>warning</td>
                    <td><?php _e('Potential issues that need attention', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Production monitoring', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td>error</td>
                    <td><?php _e('Critical errors and security issues', 'wp-oauth-debugger'); ?></td>
                    <td><?php _e('Always logged', 'wp-oauth-debugger'); ?></td>
                </tr>
            </tbody>
        </table>

        <h4><?php _e('Log Features', 'wp-oauth-debugger'); ?></h4>
        <ul>
            <li><?php _e('Automatic log rotation and cleanup', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Sensitive data masking', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Configurable retention period', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Export capabilities', 'wp-oauth-debugger'); ?></li>
        </ul>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('Token Management', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('Comprehensive token management and monitoring:', 'wp-oauth-debugger'); ?></p>

        <h4><?php _e('Features', 'wp-oauth-debugger'); ?></h4>
        <ul>
            <li><?php _e('Active token monitoring', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Token revocation', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Token usage statistics', 'wp-oauth-debugger'); ?></li>
            <li><?php _e('Expiration tracking', 'wp-oauth-debugger'); ?></li>
        </ul>

        <div class="warning">
            <strong><?php _e('Security Note:', 'wp-oauth-debugger'); ?></strong>
            <?php _e('Token management operations are restricted to administrators and require proper authentication.', 'wp-oauth-debugger'); ?>
        </div>
    </div>

    <div class="oauth-debugger-help-section">
        <h3><?php _e('API Endpoints', 'wp-oauth-debugger'); ?></h3>
        <p><?php _e('REST API endpoints for programmatic access:', 'wp-oauth-debugger'); ?></p>

        <h4><?php _e('Available Endpoints', 'wp-oauth-debugger'); ?></h4>
        <table>
            <thead>
                <tr>
                    <th><?php _e('Endpoint', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Method', 'wp-oauth-debugger'); ?></th>
                    <th><?php _e('Description', 'wp-oauth-debugger'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>/wp-json/oauth-debugger/v1/logs</code></td>
                    <td>GET</td>
                    <td><?php _e('Retrieve recent logs', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td><code>/wp-json/oauth-debugger/v1/tokens</code></td>
                    <td>GET</td>
                    <td><?php _e('List active tokens', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td><code>/wp-json/oauth-debugger/v1/tokens/{id}</code></td>
                    <td>DELETE</td>
                    <td><?php _e('Delete a specific token', 'wp-oauth-debugger'); ?></td>
                </tr>
                <tr>
                    <td><code>/wp-json/oauth-debugger/v1/security</code></td>
                    <td>GET</td>
                    <td><?php _e('Get security status', 'wp-oauth-debugger'); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="note">
            <strong><?php _e('API Security:', 'wp-oauth-debugger'); ?></strong>
            <?php _e('All API endpoints require authentication and are protected by rate limiting.', 'wp-oauth-debugger'); ?>
        </div>
    </div>
</div>
