<?php
/**
 * Admin-specific functionality.
 *
 * This class handles everything that runs in the WordPress admin area:
 * - Enqueueing admin-specific styles and scripts
 * - Adding admin menus and settings pages (Phase 2)
 * - Admin-only hooks and filters
 *
 * Why separate admin from public?
 * - Performance: Admin code doesn't load on the front-end
 * - Organization: Clear separation of concerns
 * - Security: Admin-only code is isolated
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/admin
 * @since      0.1.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for enqueueing
 * the admin-specific stylesheet and JavaScript.
 *
 * @since 0.1.0
 */
class WP_Forever_Admin {

	/**
	 * Enqueue admin styles.
	 *
	 * This method is hooked to 'admin_enqueue_scripts' and loads CSS
	 * files needed in the WordPress admin area.
	 *
	 * The $hook_suffix parameter tells us which admin page we're on.
	 * Use this to load styles only where needed (better performance).
	 *
	 * Common hook suffixes:
	 * - 'toplevel_page_wp-forever' — Our main menu page
	 * - 'settings_page_wp-forever' — Our settings submenu page
	 * - 'post.php', 'post-new.php' — Post editor screens
	 *
	 * @since 0.1.0
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_styles( string $hook_suffix ): void {
		// Uncomment to load styles only on our plugin's admin pages:
		// if ( 'toplevel_page_wp-forever' !== $hook_suffix ) {
		//     return;
		// }

		wp_enqueue_style(
			'wp-forever-admin',                                 // Handle — unique identifier.
			WP_FOREVER_PLUGIN_URL . 'assets/css/admin.css',     // Source URL.
			array(),                                            // Dependencies (none).
			WP_FOREVER_VERSION                                  // Version — for cache busting.
		);
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * This method is hooked to 'admin_enqueue_scripts' and loads JavaScript
	 * files needed in the WordPress admin area.
	 *
	 * We also use wp_localize_script() to pass PHP data to JavaScript.
	 * This is how we make AJAX URLs, nonces, and other data available to JS.
	 *
	 * @since 0.1.0
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		// Uncomment to load scripts only on our plugin's admin pages:
		// if ( 'toplevel_page_wp-forever' !== $hook_suffix ) {
		//     return;
		// }

		wp_enqueue_script(
			'wp-forever-admin',                               // Handle — unique identifier.
			WP_FOREVER_PLUGIN_URL . 'assets/js/admin.js',     // Source URL.
			array( 'wp-i18n' ),                               // Dependencies — wp-i18n for translations.
			WP_FOREVER_VERSION,                               // Version — for cache busting.
			true                                              // Load in footer (better performance).
		);

		// Set up JavaScript translations.
		// This makes wp.i18n.__() available in admin.js for translatable strings.
		// Requires JSON translation files in /languages/ directory.
		wp_set_script_translations(
			'wp-forever-admin',                              // Script handle.
			'wp-forever',                                    // Text domain.
			WP_FOREVER_PLUGIN_DIR . 'languages'              // Path to translations.
		);

		// Pass data from PHP to JavaScript.
		// In JS, access via: wpForever.ajaxUrl, wpForever.nonce, etc.
		wp_localize_script(
			'wp-forever-admin', // Script handle to attach data to.
			'wpForever',        // JavaScript object name.
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),            // WordPress AJAX endpoint.
				'nonce'        => wp_create_nonce( 'wp_forever_ajax' ),     // General AJAX nonce.
				'dismissNonce' => wp_create_nonce( 'wp_forever_dismiss_notice' ), // Notice dismiss nonce.
				'resetNonce'   => wp_create_nonce( 'wp_forever_reset_notices' ),  // Notice reset nonce.
			)
		);
	}
}
