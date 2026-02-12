<?php
/**
 * Internationalization (i18n) handler.
 *
 * This class is responsible for loading the plugin's text domain,
 * which enables translation of user-facing strings.
 *
 * Why i18n matters:
 * - WordPress is used worldwide; your plugin should be translatable
 * - Even if you only speak English, others can contribute translations
 * - It's a WordPress.org plugin directory requirement
 *
 * How it works:
 * 1. Wrap all user-facing strings with __(), _e(), esc_html__(), etc.
 * 2. Generate a .pot file with all translatable strings
 * 3. Translators create .po/.mo files for their languages
 * 4. This class loads those translation files
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
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 0.1.0
 */
class WP_Forever_I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * This method should be called early in the plugin lifecycle,
	 * typically on the 'plugins_loaded' hook before other plugin code runs.
	 *
	 * WordPress will look for translation files in this order:
	 * 1. wp-content/languages/plugins/wp-forever-{locale}.mo (user/site translations)
	 * 2. wp-content/plugins/wp-forever/languages/wp-forever-{locale}.mo (bundled)
	 *
	 * The first location allows users/hosts to provide translations that won't
	 * be overwritten when the plugin updates.
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'wp-forever', // Text domain — must match the one in plugin header.
			false,        // Deprecated parameter, always false.
			dirname( WP_FOREVER_PLUGIN_BASENAME ) . '/languages/' // Relative path to .mo files.
		);
	}
}
