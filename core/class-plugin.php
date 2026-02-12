<?php
/**
 * The core plugin class.
 *
 * This is the main orchestrator that wires everything together.
 * It's intentionally thin — it doesn't contain business logic.
 * Its job is to:
 * - Register hooks that connect our classes to WordPress
 * - Coordinate between different parts of the plugin
 *
 * Why keep it thin?
 * - Easier to understand the plugin's structure at a glance
 * - Business logic belongs in specialized classes
 * - Makes testing individual components easier
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
 * The core plugin class.
 *
 * This class orchestrates the plugin by:
 * - Instantiating the admin and public classes
 * - Registering hooks with WordPress
 * - Coordinating the plugin lifecycle
 *
 * The run() method is called from the main plugin file after
 * all dependencies are loaded.
 *
 * @since 0.1.0
 */
class WP_Forever_Plugin {

	/**
	 * Run the plugin.
	 *
	 * This is the main entry point called from wp-forever.php.
	 * It sets up all the hooks needed for the plugin to function.
	 *
	 * Hook registration follows WordPress best practices:
	 * - Use specific hooks (not 'init' for everything)
	 * - Conditional loading based on context (admin vs. public)
	 * - Appropriate priority (default 10 is usually fine)
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function run(): void {
		// Run upgrade routines if the plugin version changed.
		$this->maybe_run_upgrades();

		// Register admin-specific hooks.
		// These only run in the WordPress admin area.
		$this->register_admin_hooks();

		// Register public-facing hooks.
		// These run on the front-end (and during AJAX).
		$this->register_public_hooks();

		// Fire an action so other code can hook into plugin initialization.
		// Useful for add-ons or custom integrations.
		do_action( 'wp_forever/loaded' );
	}

	/**
	 * Run upgrade routines when the plugin version changes.
	 *
	 * This is the counterpart to core/class-activator.php:
	 * - activation handles first-time install
	 * - this handles updates after the plugin is already installed
	 *
	 * Typical upgrades include:
	 * - db schema changes
	 * - backfilling data
	 * - migrating options
	 *
	 * @since 0.2.0
	 * @return void
	 */
	private function maybe_run_upgrades(): void {
		$installed = (string) get_option( 'wp_forever_version', '0.0.0' );

		if ( version_compare( $installed, WP_FOREVER_VERSION, '>=' ) ) {
			return;
		}

		// Ensure custom tables are at the latest schema.
		WP_Forever_Services::db()->install_or_upgrade();

		// Persist current plugin version.
		update_option( 'wp_forever_version', WP_FOREVER_VERSION );

		/**
		 * Fires after WP Forever runs its upgrade routines.
		 *
		 * @since 0.2.0
		 * @param string $from_version Previously installed version.
		 * @param string $to_version   New version.
		 */
		do_action( 'wp_forever/upgraded', $installed, WP_FOREVER_VERSION );
	}

	/**
	 * Register admin-specific hooks.
	 *
	 * This method sets up all hooks needed for the WordPress admin area.
	 * It only runs when is_admin() is true.
	 *
	 * Why check is_admin()?
	 * - Performance: Admin classes don't need to load on front-end
	 * - Security: Keeps admin code isolated
	 * - Organization: Clear separation of concerns
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function register_admin_hooks(): void {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return;
		}

		// Create admin class instance.
		$admin = new WP_Forever_Admin();

		// Enqueue admin styles.
		// Hook: 'admin_enqueue_scripts' — fired when admin page loads.
		// Priority: 10 (default) — no need to load early or late.
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_styles' ) );

		// Enqueue admin scripts.
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_scripts' ) );

		// Initialize Settings page.
		// This registers the settings menu and Settings API configuration.
		// Must be called on every admin page load so hooks are registered.
		$settings = new WP_Forever_Settings();
		$settings->init();

		// Initialize Admin Notices.
		// This registers the welcome notice and AJAX handlers for dismissal.
		$notices = new WP_Forever_Notices();
		$notices->init();
	}

	/**
	 * Register public-facing hooks.
	 *
	 * This method sets up all hooks needed for the front-end.
	 * It runs on public pages AND during AJAX requests.
	 *
	 * Why include AJAX (wp_doing_ajax())?
	 * - AJAX requests often need public functionality
	 * - Without this, front-end AJAX would fail
	 *
	 * @since 0.1.0
	 * @return void
	 */
	private function register_public_hooks(): void {
		// Only run on front-end or during AJAX.
		// Note: is_admin() returns true during AJAX, so we check wp_doing_ajax() too.
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		// Create public class instance.
		$public = new WP_Forever_Public();

		// Register shortcodes.
		add_action( 'init', array( $public, 'register_shortcodes' ) );

		// Enqueue public styles.
		// Hook: 'wp_enqueue_scripts' — fired when front-end page loads.
		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_styles' ) );

		// Enqueue public scripts.
		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_scripts' ) );

		// Front-end AJAX handlers.
		add_action( 'wp_ajax_wp_forever_public_ping', array( $public, 'handle_public_ping' ) );
		add_action( 'wp_ajax_nopriv_wp_forever_public_ping', array( $public, 'handle_public_ping' ) );

		// Optional REST API routes (controlled via settings).
		$settings = (array) get_option( 'wp_forever_settings', array() );
		if ( isset( $settings['rest_api_enabled'] ) ? (bool) $settings['rest_api_enabled'] : true ) {
			add_action( 'rest_api_init', array( WP_Forever_Services::rest(), 'register_routes' ) );
		}

		// Optional integrations (controlled via settings).
		if ( ! empty( $settings['enable_woo_features'] ) ) {
			WP_Forever_Services::woocommerce()->register_hooks();
		}

		// Add more shortcodes, REST routes, and AJAX endpoints as your plugin grows.
	}
}
