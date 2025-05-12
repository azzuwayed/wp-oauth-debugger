<?php

/**
 * Troubleshooting Help Page
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="oauth-debugger-help-section">
	<h2><?php _e( 'Troubleshooting Guide', 'wp-oauth-debugger' ); ?></h2>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Common Issues and Solutions', 'wp-oauth-debugger' ); ?></h3>

		<div class="troubleshooting-item">
			<h4><?php _e( 'PKCE Support Disabled', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'PKCE support is shown as disabled in the security scanner.', 'wp-oauth-debugger' ); ?></p>
			<p><strong><?php _e( 'Possible Causes:', 'wp-oauth-debugger' ); ?></strong></p>
			<ul>
				<li><?php _e( 'PKCE is not enabled in your OAuth server configuration', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'The OAUTH_PKCE_ENABLED constant is not set', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Your OAuth server plugin does not support PKCE', 'wp-oauth-debugger' ); ?></li>
			</ul>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'Add the following to your wp-config.php:', 'wp-oauth-debugger' ); ?>
						<pre>define('OAUTH_PKCE_ENABLED', true);</pre>
					</li>
					<li><?php _e( 'Verify that your OAuth server plugin supports PKCE', 'wp-oauth-debugger' ); ?></li>
					<li><?php _e( 'Update your OAuth server plugin if necessary', 'wp-oauth-debugger' ); ?></li>
				</ol>
			</div>
		</div>

		<div class="troubleshooting-item">
			<h4><?php _e( 'CORS Issues', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'CORS errors when accessing OAuth endpoints from a different domain.', 'wp-oauth-debugger' ); ?></p>
			<p><strong><?php _e( 'Possible Causes:', 'wp-oauth-debugger' ); ?></strong></p>
			<ul>
				<li><?php _e( 'Missing or incorrect CORS headers', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'OAuth server not configured for cross-origin requests', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Incorrect domain in Access-Control-Allow-Origin header', 'wp-oauth-debugger' ); ?></li>
			</ul>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'Add the following to your theme\'s functions.php or a custom plugin:', 'wp-oauth-debugger' ); ?>
						<pre>add_action('init', function() {
	header('Access-Control-Allow-Origin: https://your-client-domain.com');
	header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization');
});</pre>
					</li>
					<li><?php _e( 'Replace "https://your-client-domain.com" with your actual client domain', 'wp-oauth-debugger' ); ?></li>
					<li><?php _e( 'If using multiple domains, implement dynamic CORS origin handling', 'wp-oauth-debugger' ); ?></li>
				</ol>
			</div>
		</div>

		<div class="troubleshooting-item">
			<h4><?php _e( 'Rate Limiting Problems', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Requests are being rate limited too aggressively or not enough.', 'wp-oauth-debugger' ); ?></p>
			<p><strong><?php _e( 'Possible Causes:', 'wp-oauth-debugger' ); ?></h4>
					<ul>
						<li><?php _e( 'Rate limit settings are too strict', 'wp-oauth-debugger' ); ?></li>
						<li><?php _e( 'Rate limit settings are too lenient', 'wp-oauth-debugger' ); ?></li>
						<li><?php _e( 'Rate limiting is not properly configured', 'wp-oauth-debugger' ); ?></li>
					</ul>
					<div class="solution">
						<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
						<ol>
							<li><?php _e( 'Adjust rate limit settings in wp-config.php:', 'wp-oauth-debugger' ); ?>
								<pre>define('OAUTH_RATE_LIMIT_ENABLED', true);
define('OAUTH_RATE_LIMIT_REQUESTS', 60); // Adjust this number</pre>
							</li>
							<li><?php _e( 'Or update through WordPress admin:', 'wp-oauth-debugger' ); ?>
								<ul>
									<li><?php _e( 'Go to OAuth Debugger → Settings', 'wp-oauth-debugger' ); ?></li>
									<li><?php _e( 'Adjust the "Rate Limit" value', 'wp-oauth-debugger' ); ?></li>
								</ul>
							</li>
						</ol>
					</div>
		</div>

		<div class="troubleshooting-item">
			<h4><?php _e( 'Logging Issues', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Logs are not being generated or are incomplete.', 'wp-oauth-debugger' ); ?></p>
			<p><strong><?php _e( 'Possible Causes:', 'wp-oauth-debugger' ); ?></strong></p>
			<ul>
				<li><?php _e( 'Debug mode is not enabled', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Log level is set too high', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Log directory is not writable', 'wp-oauth-debugger' ); ?></li>
			</ul>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'Enable debugging in wp-config.php:', 'wp-oauth-debugger' ); ?>
						<pre>define('OAUTH_DEBUG', true);
define('OAUTH_DEBUG_LOG_LEVEL', 'debug');</pre>
					</li>
					<li><?php _e( 'Check log directory permissions:', 'wp-oauth-debugger' ); ?>
						<pre>chmod 755 wp-content/oauth-debug-logs</pre>
					</li>
					<li><?php _e( 'Verify log retention settings:', 'wp-oauth-debugger' ); ?>
						<pre>define('OAUTH_DEBUG_LOG_RETENTION', 7); // days</pre>
					</li>
				</ol>
			</div>
		</div>

		<div class="troubleshooting-item">
			<h4><?php _e( 'Security Scanner False Positives', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Security scanner reports vulnerabilities that don\'t exist.', 'wp-oauth-debugger' ); ?></p>
			<p><strong><?php _e( 'Possible Causes:', 'wp-oauth-debugger' ); ?></strong></p>
			<ul>
				<li><?php _e( 'Development environment detection issues', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Custom security implementations not recognized', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Plugin conflicts', 'wp-oauth-debugger' ); ?></li>
			</ul>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'Verify environment detection:', 'wp-oauth-debugger' ); ?>
						<pre>define('WP_DEBUG', true); // for development
define('WP_DEBUG', false); // for production</pre>
					</li>
					<li><?php _e( 'Check for plugin conflicts by temporarily disabling other plugins', 'wp-oauth-debugger' ); ?></li>
					<li><?php _e( 'Review custom security implementations', 'wp-oauth-debugger' ); ?></li>
				</ol>
			</div>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Development Environment Issues', 'wp-oauth-debugger' ); ?></h3>

		<div class="troubleshooting-item">
			<h4><?php _e( 'Local Development Setup', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'Security scanner shows warnings in local development environment.', 'wp-oauth-debugger' ); ?></p>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'Configure development environment in wp-config.php:', 'wp-oauth-debugger' ); ?>
						<pre>// Development environment settings
define('WP_DEBUG', true);
define('OAUTH_DEBUG', true);
define('OAUTH_DEBUG_LOG_LEVEL', 'debug');
define('OAUTH_RATE_LIMIT_ENABLED', false); // Disable rate limiting in development
define('OAUTH_DEBUG_LOG_RETENTION', 30); // Longer retention for development</pre>
					</li>
					<li><?php _e( 'Note that some security warnings are expected in development', 'wp-oauth-debugger' ); ?></li>
				</ol>
			</div>
		</div>

		<div class="troubleshooting-item">
			<h4><?php _e( 'SSL Certificate Issues', 'wp-oauth-debugger' ); ?></h4>
			<p><strong><?php _e( 'Issue:', 'wp-oauth-debugger' ); ?></strong> <?php _e( 'SSL certificate warnings in local development.', 'wp-oauth-debugger' ); ?></p>
			<div class="solution">
				<p><strong><?php _e( 'Solution:', 'wp-oauth-debugger' ); ?></strong></p>
				<ol>
					<li><?php _e( 'For local development, you can use a self-signed certificate', 'wp-oauth-debugger' ); ?></li>
					<li><?php _e( 'Or use a local SSL certificate from mkcert:', 'wp-oauth-debugger' ); ?>
						<pre># Install mkcert
brew install mkcert

# Install local CA
mkcert -install

# Create certificate for local domain
mkcert localhost 127.0.0.1 ::1</pre>
					</li>
					<li><?php _e( 'Configure your local web server to use the certificate', 'wp-oauth-debugger' ); ?></li>
				</ol>
			</div>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Getting Additional Help', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'If you\'re still experiencing issues:', 'wp-oauth-debugger' ); ?></p>
		<ul>
			<li><?php _e( 'Check the plugin\'s GitHub repository for known issues and updates', 'wp-oauth-debugger' ); ?></li>
			<li><?php _e( 'Enable debug logging and check the logs for detailed error information', 'wp-oauth-debugger' ); ?></li>
			<li><?php _e( 'Review the WordPress debug log for any related errors', 'wp-oauth-debugger' ); ?></li>
			<li><?php _e( 'Contact the plugin support team with detailed information about your issue', 'wp-oauth-debugger' ); ?></li>
		</ul>

		<div class="note">
			<strong><?php _e( 'When reporting issues, please include:', 'wp-oauth-debugger' ); ?></strong>
			<ul>
				<li><?php _e( 'WordPress version', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Plugin version', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'PHP version', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Relevant error messages', 'wp-oauth-debugger' ); ?></li>
				<li><?php _e( 'Steps to reproduce the issue', 'wp-oauth-debugger' ); ?></li>
			</ul>
		</div>
	</div>
</div>
