<?php
/**
 * Plugin activator.
 *
 * This class runs during plugin activation. It handles:
 * - Version compatibility checks (PHP and WordPress)
 * - Setting default options
 * - Any one-time setup tasks
 *
 * Why a separate class? Keeps activation logic isolated and testable.
 * The main plugin file stays clean and focused on bootstrapping.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/core
 * @since      0.1.0
 */

// Prevent direct file access.
// This check ensures the file is loaded within WordPress context.
// Without it, someone could access this file directly via URL.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 * It's called via register_activation_hook() in the main plugin file.
 *
 * @since 0.1.0
 */
class WP_Forever_Activator {

	/**
	 * Activate the plugin.
	 *
	 * This method is called via register_activation_hook() when the plugin
	 * is activated through the WordPress admin. Use this for:
	 *
	 * - Checking minimum PHP/WordPress versions (fail early if incompatible)
	 * - Creating database tables (if needed)
	 * - Setting default options
	 * - Flushing rewrite rules (if registering custom post types)
	 * - Scheduling cron events
	 *
	 * Note: This runs ONCE on activation, not on every page load.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function activate( bool $network_wide = false ): void {
		// Check minimum PHP version.
		// We check this here (in addition to plugin header) because the header check
		// only works in WordPress 5.2+, and we want a clear error message.
		if ( version_compare( PHP_VERSION, WP_FOREVER_MIN_PHP_VERSION, '<' ) ) {
			// Deactivate immediately to prevent a broken state.
			deactivate_plugins( WP_FOREVER_PLUGIN_BASENAME );

			// Show a user-friendly error and stop.
			wp_die(
				sprintf(
					/* translators: %s: Minimum PHP version required (e.g., "8.0") */
					esc_html__( 'WP Forever requires PHP %s or higher. Please upgrade your PHP version.', 'wp-forever' ),
					esc_html( WP_FOREVER_MIN_PHP_VERSION )
				),
				esc_html__( 'Plugin Activation Error', 'wp-forever' ),
				array(
					'response'  => 200, // Don't show as server error.
					'back_link' => true, // Show "Go Back" link.
				)
			);
		}

		// Check minimum WordPress version.
		// get_bloginfo('version') returns the current WP version.
		if ( version_compare( get_bloginfo( 'version' ), WP_FOREVER_MIN_WP_VERSION, '<' ) ) {
			deactivate_plugins( WP_FOREVER_PLUGIN_BASENAME );

			wp_die(
				sprintf(
					/* translators: %s: Minimum WordPress version required (e.g., "6.0") */
					esc_html__( 'WP Forever requires WordPress %s or higher. Please upgrade WordPress.', 'wp-forever' ),
					esc_html( WP_FOREVER_MIN_WP_VERSION )
				),
				esc_html__( 'Plugin Activation Error', 'wp-forever' ),
				array(
					'response'  => 200,
					'back_link' => true,
				)
			);
		}

		// Perform install (single site or multisite network activation).
		if ( is_multisite() && $network_wide ) {
			$site_ids = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				self::activate_site();
				restore_current_blog();
			}
		} else {
			self::activate_site();
		}

		// Fire an action so other code can hook into activation.
		do_action( 'wp_forever/activated', $network_wide );

		// Future: If you register custom post types, flush rewrite rules here.
		flush_rewrite_rules();
	}

	/**
	 * Activate on a single site.
	 *
	 * This method is called for each site in multisite network activation.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	private static function activate_site(): void {
		// Store the plugin version in the database.
		// This is useful for running upgrade routines when the plugin is updated.
		// We use add_option() instead of update_option() on first install because
		// add_option() won't overwrite existing values.
		if ( false === get_option( 'wp_forever_version' ) ) {
			add_option( 'wp_forever_version', WP_FOREVER_VERSION );
		} else {
			// If upgrading, update the version.
			// Future: Add upgrade routines here based on old vs new version.
			update_option( 'wp_forever_version', WP_FOREVER_VERSION );
		}

		// Ensure custom tables are created / upgraded.
		if ( class_exists( 'WP_Forever_Services' ) ) {
			WP_Forever_Services::db()->install_or_upgrade();
		}

		// Future: If you need scheduled tasks, set them up here.
		// if ( ! wp_next_scheduled( 'wp_forever_daily_cron' ) ) {
		//     wp_schedule_event( time(), 'daily', 'wp_forever_daily_cron' );
		// }
	}
}
