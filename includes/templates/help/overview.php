<?php

/**
 * Overview Help Page
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="oauth-debugger-help-section">
	<h2><?php _e( 'Welcome to OAuth Debugger', 'wp-oauth-debugger' ); ?></h2>

	<p><?php _e( 'OAuth Debugger is a comprehensive tool designed to help developers and administrators monitor, debug, and secure OAuth implementations in WordPress. This plugin provides real-time insights into OAuth operations, security analysis, and detailed logging capabilities.', 'wp-oauth-debugger' ); ?></p>

	<div class="note">
		<strong><?php _e( 'Note:', 'wp-oauth-debugger' ); ?></strong>
		<?php _e( 'This plugin is designed to work with any OAuth 2.0 implementation in WordPress, including custom OAuth servers and third-party OAuth plugins.', 'wp-oauth-debugger' ); ?>
	</div>

	<h3><?php _e( 'Key Features', 'wp-oauth-debugger' ); ?></h3>
	<ul>
		<li><strong><?php _e( 'Real-time Monitoring:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Track OAuth requests, responses, and errors as they happen.', 'wp-oauth-debugger' ); ?></li>
		<li><strong><?php _e( 'Security Analysis:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Comprehensive security scanning and vulnerability detection.', 'wp-oauth-debugger' ); ?></li>
		<li><strong><?php _e( 'Token Management:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Monitor and manage active OAuth tokens.', 'wp-oauth-debugger' ); ?></li>
		<li><strong><?php _e( 'Detailed Logging:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Capture and analyze OAuth operations with configurable log levels.', 'wp-oauth-debugger' ); ?></li>
		<li><strong><?php _e( 'Security Recommendations:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Get actionable insights to improve your OAuth implementation.', 'wp-oauth-debugger' ); ?></li>
	</ul>

	<h3><?php _e( 'Getting Started', 'wp-oauth-debugger' ); ?></h3>
	<ol>
		<li><?php _e( 'Activate the plugin through the WordPress admin interface.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Configure basic settings in the plugin settings page.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Access the debug interface through WordPress Admin → OAuth Debugger.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Review the security analysis and implement recommended improvements.', 'wp-oauth-debugger' ); ?></li>
	</ol>

	<div class="warning">
		<strong><?php _e( 'Important:', 'wp-oauth-debugger' ); ?></strong>
		<?php _e( 'While this plugin is designed to help secure your OAuth implementation, it should not be used as a replacement for proper security practices. Always follow OAuth 2.0 best practices and keep your WordPress installation and plugins up to date.', 'wp-oauth-debugger' ); ?>
	</div>

	<h3><?php _e( 'Development vs. Production', 'wp-oauth-debugger' ); ?></h3>
	<p><?php _e( 'The plugin automatically detects whether it\'s running in a development or production environment and adjusts its behavior accordingly:', 'wp-oauth-debugger' ); ?></p>

	<table>
		<thead>
			<tr>
				<th><?php _e( 'Feature', 'wp-oauth-debugger' ); ?></th>
				<th><?php _e( 'Development', 'wp-oauth-debugger' ); ?></th>
				<th><?php _e( 'Production', 'wp-oauth-debugger' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php _e( 'Debug Logging', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Enabled by default', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Configurable', 'wp-oauth-debugger' ); ?></td>
			</tr>
			<tr>
				<td><?php _e( 'Security Checks', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Development-specific checks', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Production-focused checks', 'wp-oauth-debugger' ); ?></td>
			</tr>
			<tr>
				<td><?php _e( 'Rate Limiting', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Relaxed', 'wp-oauth-debugger' ); ?></td>
				<td><?php _e( 'Strict', 'wp-oauth-debugger' ); ?></td>
			</tr>
		</tbody>
	</table>

	<h3><?php _e( 'Support and Resources', 'wp-oauth-debugger' ); ?></h3>
	<p><?php _e( 'For additional help and resources:', 'wp-oauth-debugger' ); ?></p>
	<ul>
		<li><?php _e( 'Check the detailed documentation in each section of this help system.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Review the example configurations for common use cases.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Consult the troubleshooting guide for common issues and solutions.', 'wp-oauth-debugger' ); ?></li>
		<li><?php _e( 'Visit the plugin\'s GitHub repository for updates and community support.', 'wp-oauth-debugger' ); ?></li>
	</ul>
</div>
