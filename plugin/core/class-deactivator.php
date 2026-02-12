<?php
/**
 * Plugin deactivator.
 *
 * This class runs during plugin deactivation. It handles:
 * - Clearing scheduled cron events
 * - Flushing rewrite rules (if CPTs were registered)
 * - Any cleanup that should happen when plugin is turned off
 *
 * IMPORTANT: Do NOT delete user data here!
 * Deactivation is temporary — the user might reactivate later.
 * Data deletion belongs in uninstall.php (when plugin is deleted).
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/core
 * @since      0.1.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 * It's called via register_deactivation_hook() in the main plugin file.
 *
 * @since 0.1.0
 */
class WP_Forever_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * This method is called via register_deactivation_hook() when the plugin
	 * is deactivated through the WordPress admin. Use this for:
	 *
	 * - Clearing scheduled cron events (so they don't run without the plugin)
	 * - Flushing rewrite rules (if you registered custom post types)
	 * - Cleaning up temporary data (transients, etc.)
	 *
	 * DO NOT use this for:
	 * - Deleting user data or settings (use uninstall.php instead)
	 * - Dropping database tables (use uninstall.php instead)
	 *
	 * Why? Users often deactivate plugins temporarily for debugging.
	 * They expect their data to still be there when they reactivate.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function deactivate( bool $network_wide = false ): void {
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				self::deactivate_site();
				restore_current_blog();
			}
		} else {
			self::deactivate_site();
		}

		// Fire an action so other code can hook into deactivation.
		do_action( 'wp_forever/deactivated', $network_wide );
	}

	/**
	 * Deactivate on a single site.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	private static function deactivate_site(): void {
		// Clear any scheduled cron events.
		// If we don't do this, WordPress will try to run them and fail
		// because our plugin code won't be loaded.
		wp_clear_scheduled_hook( 'wp_forever_daily_cron' );

		// Flush rewrite rules.
		// If we registered custom post types or custom rewrite rules,
		// we need to flush them so WordPress regenerates its rewrite cache
		// without our rules in it.
		flush_rewrite_rules();

		// Note: We intentionally do NOT delete options or data here.
		// That would be frustrating for users who just want to temporarily
		// disable the plugin. Data cleanup belongs in uninstall.php.
	}
}
