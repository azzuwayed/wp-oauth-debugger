<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/yourusername/wp-oauth-debugger
 * @since      1.0.0
 *
 * @package    WP_OAuth_Debugger
 * @subpackage WP_OAuth_Debugger/includes
 */

namespace WP_OAuth_Debugger\Core;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_OAuth_Debugger
 * @subpackage WP_OAuth_Debugger/includes
 * @author     Your Name <your.email@example.com>
 */
class Deactivator {

	/**
	 * Clean up plugin data on deactivation.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear scheduled events
		\wp_clear_scheduled_hook( 'oauth_debugger_cleanup_logs' );

		// Clear transients
		\delete_transient( 'oauth_debugger_last_scan' );
		\delete_transient( 'oauth_debugger_security_status' );

		// Optionally clear debug logs if setting is enabled
		if ( \get_option( 'oauth_debugger_clear_logs_on_deactivate', false ) ) {
			global $wpdb;
			$wpdb->query( "DELETE FROM {$wpdb->prefix}oauth_debugger_logs" );
		}

		// Flush rewrite rules
		\flush_rewrite_rules();
	}
}
