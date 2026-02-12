<?php
/**
 * Plugin Name:       WP Forever
 * Plugin URI:        https://example.com/wp-forever
 * Description:       A WordPress plugin template for consistent, well-documented plugin development.
 * Version:           0.2.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-forever
 * Domain Path:       /languages
 *
 * @package WP_Forever
 * @since   0.1.0
 *
 * This is the main plugin file — the entry point that WordPress loads.
 * It's responsible for:
 * - Defining plugin constants
 * - Loading required files
 * - Registering activation/deactivation hooks
 * - Initializing the plugin on the appropriate hook
 *
 * This file is intentionally kept simple and focused on bootstrapping.
 * Business logic belongs in the classes it loads.
 */

/*
|--------------------------------------------------------------------------
| Security: Prevent Direct Access
|--------------------------------------------------------------------------
|
| ABSPATH is defined by WordPress. If it's not defined, someone is trying
| to access this file directly via URL (not through WordPress). We exit
| immediately to prevent any code execution outside WordPress context.
|
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
|
| These constants are used throughout the plugin. Defining them here
| (in one place) makes the codebase easier to maintain and update.
|
| Naming convention: WP_FOREVER_ prefix to avoid conflicts with other plugins.
|
*/

/**
 * Plugin version.
 *
 * Used for cache busting (asset URLs) and database migrations.
 * Update this when releasing a new version.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_VERSION', '0.2.0' );

/**
 * Database schema version.
 *
 * Increment this integer whenever you change a custom table definition in
 * `includes/db/schema.php`. WordPress will then run `dbDelta()` to apply changes.
 *
 * @since 0.2.0
 */
define( 'WP_FOREVER_DB_SCHEMA_VERSION', 1 );

/**
 * Absolute path to the plugin directory.
 *
 * Use this when including PHP files.
 * Example: require_once WP_FOREVER_PLUGIN_DIR . 'includes/functions.php';
 *
 * Includes trailing slash.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * URL to the plugin directory.
 *
 * Use this when enqueueing assets (CSS, JS, images).
 * Example: WP_FOREVER_PLUGIN_URL . 'assets/css/admin.css'
 *
 * Includes trailing slash.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 *
 * Returns 'wp-forever/wp-forever.php' (folder/file).
 * Used by register_activation_hook(), plugin_action_links, etc.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Minimum WordPress version required.
 *
 * Checked during activation. If WP is older, activation fails with a message.
 * Update this when using features from newer WP versions.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_MIN_WP_VERSION', '6.0' );

/**
 * Minimum PHP version required.
 *
 * Checked during activation. If PHP is older, activation fails with a message.
 * PHP 8.0+ gives us: named arguments, union types, match expressions, etc.
 *
 * @since 0.1.0
 */
define( 'WP_FOREVER_MIN_PHP_VERSION', '8.0' );

/*
|--------------------------------------------------------------------------
| Load Dependencies
|--------------------------------------------------------------------------
|
| We manually require files instead of using an autoloader. This approach:
| - Works on any WordPress host (no Composer required)
| - Is explicit about what's loaded
| - Is easy to understand and debug
|
| Load order matters: load dependencies before the classes that use them.
|
*/

// Core classes — always loaded (needed for bootstrap).
require_once WP_FOREVER_PLUGIN_DIR . 'core/class-activator.php';
require_once WP_FOREVER_PLUGIN_DIR . 'core/class-deactivator.php';
require_once WP_FOREVER_PLUGIN_DIR . 'core/class-i18n.php';
require_once WP_FOREVER_PLUGIN_DIR . 'core/class-plugin.php';

// Shared includes — helper functions and utilities.
require_once WP_FOREVER_PLUGIN_DIR . 'includes/functions.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/class-logger.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/class-notice-manager.php';

// Shared infrastructure (DB layer, REST/AJAX helpers, template loader).
require_once WP_FOREVER_PLUGIN_DIR . 'includes/class-template-loader.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/ajax/class-ajax.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/db/schema.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/db/class-db.php';
require_once WP_FOREVER_PLUGIN_DIR . 'includes/rest/class-rest.php';

// Optional integrations.
require_once WP_FOREVER_PLUGIN_DIR . 'integrations/woocommerce/class-woocommerce.php';

// Lightweight service registry (centralizes shared instances).
require_once WP_FOREVER_PLUGIN_DIR . 'includes/class-services.php';

// Admin classes — only load in WordPress admin (saves resources on front-end).
if ( is_admin() ) {
	require_once WP_FOREVER_PLUGIN_DIR . 'admin/class-admin.php';
	require_once WP_FOREVER_PLUGIN_DIR . 'admin/class-settings.php';
	require_once WP_FOREVER_PLUGIN_DIR . 'admin/class-notices.php';
}

// Public classes — load on front-end AND during AJAX.
// Note: is_admin() returns true during AJAX, but we still need public code for AJAX handlers.
if ( ! is_admin() || wp_doing_ajax() ) {
	require_once WP_FOREVER_PLUGIN_DIR . 'public/class-public.php';
}

/*
|--------------------------------------------------------------------------
| Activation & Deactivation Hooks
|--------------------------------------------------------------------------
|
| These hooks run when the plugin is activated/deactivated via the WP admin.
|
| Activation: Set up the plugin (create tables, set defaults, etc.)
| Deactivation: Clean up temporary data (clear cron, flush rewrites)
|
| Note: Uninstall (deleting the plugin) is handled by uninstall.php.
|
*/

// Run WP_Forever_Activator::activate() when plugin is activated.
register_activation_hook( __FILE__, array( 'WP_Forever_Activator', 'activate' ) );

// Run WP_Forever_Deactivator::deactivate() when plugin is deactivated.
register_deactivation_hook( __FILE__, array( 'WP_Forever_Deactivator', 'deactivate' ) );

/*
|--------------------------------------------------------------------------
| Initialize the Plugin
|--------------------------------------------------------------------------
|
| We hook into 'plugins_loaded' instead of running immediately because:
| - WordPress core is fully loaded
| - Other plugins are loaded (important for compatibility)
| - Translations are available
|
| This is the WordPress best practice for plugin initialization.
|
*/

/**
 * Initialize the plugin.
 *
 * Called on the 'plugins_loaded' hook to ensure WordPress and other
 * plugins are fully loaded before we run.
 *
 * @since 0.1.0
 * @return void
 */
function wp_forever_init(): void {
	// Load translations first — ensures all our strings can be translated.
	WP_Forever_I18n::load_textdomain();

	// Create and run the main plugin class.
	// This registers all the hooks needed for the plugin to function.
	$plugin = new WP_Forever_Plugin();
	$plugin->run();
}

// Hook our initialization function to 'plugins_loaded'.
// Priority 10 (default) is fine — we don't need to run early or late.
add_action( 'plugins_loaded', 'wp_forever_init' );
