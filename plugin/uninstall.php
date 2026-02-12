<?php
/**
 * Uninstall script.
 *
 * This file is executed when the plugin is DELETED through the WordPress admin.
 * (Not when deactivated — that's handled by class-deactivator.php)
 *
 * Use this for permanent cleanup:
 * - Delete plugin options from wp_options table
 * - Drop custom database tables
 * - Remove user meta
 * - Delete transients
 * - Clean up uploaded files created by the plugin
 *
 * IMPORTANT: This file must work STANDALONE.
 * WordPress loads this file directly without loading the plugin.
 * Do NOT rely on plugin classes, functions, or constants here.
 *
 * @package WP_Forever
 * @since   0.1.0
 */

/*
|--------------------------------------------------------------------------
| Security Check
|--------------------------------------------------------------------------
|
| WP_UNINSTALL_PLUGIN is defined by WordPress when this file is called
| through the proper uninstall mechanism. If it's not defined, someone
| is trying to access this file directly — exit immediately.
|
*/
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Delete Plugin Options
|--------------------------------------------------------------------------
|
| Remove all options the plugin stored in the wp_options table.
| These are stored via add_option() and update_option() during plugin use.
|
*/

// Respect the "Delete data on uninstall" setting.
//
// Uninstall runs WITHOUT loading the plugin code, so we must read the option directly.
$settings = (array) get_option( 'wp_forever_settings', array() );
$delete_data = isset( $settings['delete_data_on_uninstall'] ) ? (bool) $settings['delete_data_on_uninstall'] : false;

// Always clear scheduled events (safe even if we keep data).
wp_clear_scheduled_hook( 'wp_forever_daily_cron' );

if ( $delete_data ) {
	// Main plugin version tracking option.
	delete_option( 'wp_forever_version' );

	// Main settings array.
	delete_option( 'wp_forever_settings' );

	// DB schema version tracking option.
	delete_option( 'wp_forever_db_schema_version' );
}

// Future: Add any additional options your plugin creates.
// delete_option( 'wp_forever_license_key' );
// delete_option( 'wp_forever_feature_flags' );

/*
|--------------------------------------------------------------------------
| Delete Transients
|--------------------------------------------------------------------------
|
| Transients are temporary cached data. They should be cleaned up
| when the plugin is removed.
|
*/

if ( $delete_data ) {
	// Delete known transients.
	delete_transient( 'wp_forever_cache' );
}

// Future: If you have multiple transients with a pattern, you might need
// to query the database directly:
// global $wpdb;
// $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_forever_%'" );
// $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_forever_%'" );

/*
|--------------------------------------------------------------------------
| Delete User Meta
|--------------------------------------------------------------------------
|
| If your plugin stores per-user data (like dismissed notices, preferences),
| clean it up here.
|
*/

// Example: Delete dismissed notices for all users.
// The '0' user_id with 'true' for delete_all removes meta for ALL users.
// delete_metadata( 'user', 0, 'wp_forever_dismissed_notices', '', true );

// Future: Add other user meta cleanup.
// delete_metadata( 'user', 0, 'wp_forever_user_preferences', '', true );

/*
|--------------------------------------------------------------------------
| Delete Post Meta
|--------------------------------------------------------------------------
|
| If your plugin stores data attached to posts (custom fields), you may
| want to clean it up. Be careful here — only delete what your plugin created.
|
*/

// Example: Delete all post meta with our prefix.
// delete_metadata( 'post', 0, '_wp_forever_custom_field', '', true );

/*
|--------------------------------------------------------------------------
| Drop Custom Database Tables
|--------------------------------------------------------------------------
|
| If your plugin created custom database tables, drop them here.
| Only uncomment if your plugin actually creates tables.
|
*/

// global $wpdb;

// Example: Drop a custom table.
// $table_name = $wpdb->prefix . 'wp_forever_logs';
// $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// Example: Drop multiple tables.
// $tables = array(
//     $wpdb->prefix . 'wp_forever_logs',
//     $wpdb->prefix . 'wp_forever_events',
// );
// foreach ( $tables as $table ) {
//     $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
// }

/*
|--------------------------------------------------------------------------
| Clean Up Uploaded Files
|--------------------------------------------------------------------------
|
| If your plugin created files in the uploads directory, clean them up.
| BE VERY CAREFUL here — only delete files your plugin created.
|
*/

// Example: Delete plugin's upload directory.
// $upload_dir = wp_upload_dir();
// $plugin_upload_dir = $upload_dir['basedir'] . '/wp-forever';
//
// if ( is_dir( $plugin_upload_dir ) ) {
//     // Recursively delete the directory and its contents.
//     // Use WP_Filesystem for better compatibility.
//     global $wp_filesystem;
//     if ( empty( $wp_filesystem ) ) {
//         require_once ABSPATH . 'wp-admin/includes/file.php';
//         WP_Filesystem();
//     }
//     $wp_filesystem->rmdir( $plugin_upload_dir, true );
// }

/*
|--------------------------------------------------------------------------
| Clear Scheduled Events
|--------------------------------------------------------------------------
|
| While deactivation should clear cron events, it's good practice to
| ensure they're removed during uninstall too (in case deactivation was skipped).
|
*/

// (Already cleared above.)

// Future: Clear other cron events.
// wp_clear_scheduled_hook( 'wp_forever_hourly_task' );
// wp_clear_scheduled_hook( 'wp_forever_weekly_cleanup' );

/*
|--------------------------------------------------------------------------
| Multisite Cleanup
|--------------------------------------------------------------------------
|
| If the plugin was network-activated on a multisite installation,
| we need to clean up data from all sites.
|
*/


/**
 * Multisite cleanup.
 *
 * In multisite, options and tables exist per-site. We repeat the above logic per site.
 */
if ( is_multisite() ) {
	// Get all site IDs in the network.
	$site_ids = get_sites( array( 'fields' => 'ids' ) );

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		$site_settings = (array) get_option( 'wp_forever_settings', array() );
		$site_delete   = isset( $site_settings['delete_data_on_uninstall'] ) ? (bool) $site_settings['delete_data_on_uninstall'] : false;

		wp_clear_scheduled_hook( 'wp_forever_daily_cron' );

		if ( $site_delete ) {
			delete_option( 'wp_forever_version' );
			delete_option( 'wp_forever_settings' );
			delete_option( 'wp_forever_db_schema_version' );
			delete_transient( 'wp_forever_cache' );

			// Drop custom tables for this blog.
			global $wpdb;
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wp_forever_events' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		restore_current_blog();
	}
}

// Single-site table cleanup.
if ( ! is_multisite() && $delete_data ) {
	global $wpdb;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wp_forever_events' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
}
