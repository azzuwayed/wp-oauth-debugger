<?php

/**
 * Examples Help Page
 *
 * @package WP_OAuth_Debugger
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="oauth-debugger-help-section">
	<h2><?php _e( 'Practical Examples', 'wp-oauth-debugger' ); ?></h2>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Single-Page Application (SPA) Integration', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'Example of integrating OAuth Debugger with a React-based SPA:', 'wp-oauth-debugger' ); ?></p>

		<div class="example-box">
			<h4><?php _e( 'Client-Side Implementation', 'wp-oauth-debugger' ); ?></h4>
			<pre>// React component for OAuth login
import { useEffect, useState } from 'react';

function OAuthLogin() {
	const [authUrl, setAuthUrl] = useState('');

	useEffect(() => {
		// Generate PKCE challenge
		const codeVerifier = generateCodeVerifier();
		const codeChallenge = await generateCodeChallenge(codeVerifier);

		// Store code verifier for later use
		sessionStorage.setItem('code_verifier', codeVerifier);

		// Construct authorization URL
		const url = new URL('https://your-wordpress-site.com/oauth/authorize');
		url.searchParams.append('client_id', 'your_client_id');
		url.searchParams.append('redirect_uri', 'https://your-spa.com/callback');
		url.searchParams.append('response_type', 'code');
		url.searchParams.append('code_challenge', codeChallenge);
		url.searchParams.append('code_challenge_method', 'S256');
		url.searchParams.append('scope', 'read write');

		setAuthUrl(url.toString());
	}, []);

	return (
		&lt;button onClick={() => window.location.href = authUrl}&gt;
			Login with WordPress
		&lt;/button&gt;
	);
}</pre>
			<div class="note">
				<strong><?php _e( 'Note:', 'wp-oauth-debugger' ); ?></strong>
				<?php _e( 'This example uses PKCE for enhanced security, which is required for SPAs.', 'wp-oauth-debugger' ); ?>
			</div>
		</div>

		<div class="example-box">
			<h4><?php _e( 'Callback Handler', 'wp-oauth-debugger' ); ?></h4>
			<pre>// Handle OAuth callback
async function handleCallback(code) {
	const codeVerifier = sessionStorage.getItem('code_verifier');

	const response = await fetch('https://your-wordpress-site.com/oauth/token', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: new URLSearchParams({
			grant_type: 'authorization_code',
			code: code,
			redirect_uri: 'https://your-spa.com/callback',
			client_id: 'your_client_id',
			code_verifier: codeVerifier
		})
	});

	const data = await response.json();
	// Store tokens securely
	sessionStorage.setItem('access_token', data.access_token);
	sessionStorage.setItem('refresh_token', data.refresh_token);
}</pre>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Mobile App Integration', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'Example of integrating OAuth Debugger with a mobile app using React Native:', 'wp-oauth-debugger' ); ?></p>

		<div class="example-box">
			<h4><?php _e( 'React Native Implementation', 'wp-oauth-debugger' ); ?></h4>
			<pre>import { OAuthClient } from 'react-native-oauth';

const oauthClient = new OAuthClient({
	clientId: 'your_client_id',
	clientSecret: 'your_client_secret',
	redirectUri: 'yourapp://oauth/callback',
	scopes: ['read', 'write'],
	pkceEnabled: true
});

async function login() {
	try {
		const authResult = await oauthClient.authorize();
		// Store tokens securely using react-native-keychain
		await Keychain.setGenericPassword(
			'oauth_tokens',
			JSON.stringify({
				access_token: authResult.accessToken,
				refresh_token: authResult.refreshToken
			})
		);
	} catch (error) {
		console.error('OAuth error:', error);
	}
}</pre>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Server-to-Server Integration', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'Example of integrating OAuth Debugger with a server application:', 'wp-oauth-debugger' ); ?></p>

		<div class="example-box">
			<h4><?php _e( 'PHP Server Implementation', 'wp-oauth-debugger' ); ?></h4>
			<pre>&lt;?php
// Server-to-server OAuth client
class OAuthServerClient {
	private $client_id;
	private $client_secret;
	private $token_url;
	private $access_token;

	public function __construct($client_id, $client_secret, $token_url) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->token_url = $token_url;
	}

	public function getAccessToken() {
		$response = wp_remote_post($this->token_url, array(
			'body' => array(
				'grant_type' => 'client_credentials',
				'client_id' => $this->client_id,
				'client_secret' => $this->client_secret,
				'scope' => 'read write'
			)
		));

		if (is_wp_error($response)) {
			throw new Exception('Failed to get access token');
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$this->access_token = $body['access_token'];
		return $this->access_token;
	}

	public function makeAuthenticatedRequest($endpoint, $method = 'GET', $data = array()) {
		if (!$this->access_token) {
			$this->getAccessToken();
		}

		$args = array(
			'method' => $method,
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->access_token,
				'Content-Type' => 'application/json'
			)
		);

		if (!empty($data)) {
			$args['body'] = json_encode($data);
		}

		$response = wp_remote_request($endpoint, $args);
		return json_decode(wp_remote_retrieve_body($response), true);
	}
}</pre>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Debugging Common Issues', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'Examples of using OAuth Debugger to troubleshoot common problems:', 'wp-oauth-debugger' ); ?></p>

		<div class="example-box">
			<h4><?php _e( 'Token Validation', 'wp-oauth-debugger' ); ?></h4>
			<pre>// Check token validity
$debug_helper = new \WP_OAuth_Debugger\Debug\DebugHelper();
$token_info = $debug_helper->validate_token('your_access_token');

if ($token_info['is_valid']) {
	echo "Token is valid. Expires in: " . $token_info['expires_in'] . " seconds";
} else {
	echo "Token is invalid. Reason: " . $token_info['error'];
}</pre>
		</div>

		<div class="example-box">
			<h4><?php _e( 'Security Analysis', 'wp-oauth-debugger' ); ?></h4>
			<pre>// Run security analysis
$debug_helper = new \WP_OAuth_Debugger\Debug\DebugHelper();
$security_status = $debug_helper->get_security_status();

// Check specific security aspects
if ($security_status['pkce_enabled']) {
	echo "PKCE is properly configured";
} else {
	echo "PKCE is not enabled - this is required for public clients";
}

// Review security recommendations
foreach ($security_status['recommendations'] as $recommendation) {
	echo "Recommendation: " . $recommendation['title'];
	echo "Solution: " . $recommendation['solution'];
}</pre>
		</div>
	</div>

	<div class="oauth-debugger-help-section">
		<h3><?php _e( 'Custom Integration Examples', 'wp-oauth-debugger' ); ?></h3>
		<p><?php _e( 'Examples of custom integrations and extensions:', 'wp-oauth-debugger' ); ?></p>

		<div class="example-box">
			<h4><?php _e( 'Custom Token Storage', 'wp-oauth-debugger' ); ?></h4>
			<pre>// Implement custom token storage
add_filter('oauth_debugger_token_storage', function($storage) {
	return new class implements TokenStorageInterface {
		public function storeToken($token_data) {
			// Store token in custom database table
			global $wpdb;
			$wpdb->insert(
				$wpdb->prefix . 'custom_oauth_tokens',
				array(
					'access_token' => $token_data['access_token'],
					'refresh_token' => $token_data['refresh_token'],
					'expires_at' => time() + $token_data['expires_in'],
					'created_at' => current_time('mysql')
				)
			);
		}

		public function getToken($access_token) {
			global $wpdb;
			return $wpdb->get_row($wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}custom_oauth_tokens
				WHERE access_token = %s AND expires_at > %d",
				$access_token,
				time()
			));
		}
	};
});</pre>
		</div>

		<div class="example-box">
			<h4><?php _e( 'Custom Logging', 'wp-oauth-debugger' ); ?></h4>
			<pre>// Implement custom logging
add_filter('oauth_debugger_log_handler', function($handler) {
	return new class implements LogHandlerInterface {
		public function log($level, $message, array $context = array()) {
			// Log to custom service (e.g., Sentry, Loggly)
			$log_data = array(
				'level' => $level,
				'message' => $message,
				'context' => $context,
				'timestamp' => current_time('mysql')
			);

			// Example: Log to external service
			wp_remote_post('https://your-logging-service.com/logs', array(
				'body' => json_encode($log_data),
				'headers' => array('Content-Type' => 'application/json')
			));
		}
	};
});</pre>
		</div>
	</div>
</div>
