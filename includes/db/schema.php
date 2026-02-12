<?php
/**
 * Database schema definitions.
 *
 * If your plugin needs custom tables (common for logs, events, queues, etc.),
 * define the CREATE TABLE statements here.
 *
 * We keep schema strings in a dedicated file because:
 * - it keeps the DB class readable
 * - it makes it easy to review schema changes in git
 * - it helps separate "DDL" from runtime logic
 *
 * IMPORTANT:
 * - Use {$wpdb->prefix} for table names.
 * - Use dbDelta() for creates/upgrades.
 * - Track schema version separately from plugin version.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes/db
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current schema version.
 *
 * Bump this when you change any CREATE TABLE statements.
 *
 * @since 0.2.0
 */
define( 'WP_FOREVER_DB_SCHEMA_VERSION', '1.0.0' );

/**
 * Get the CREATE TABLE statements for dbDelta().
 *
 * @since 0.2.0
 * @global wpdb $wpdb WordPress database abstraction object.
 * @return string[] Array of CREATE TABLE statements.
 */
function wp_forever_db_schema_sql(): array {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	// Example table: lightweight event log.
	// Remove this table entirely if your plugin does not need custom tables.
	$table_events = $wpdb->prefix . 'wp_forever_events';

	return array(
		"CREATE TABLE {$table_events} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_key VARCHAR(191) NOT NULL,
			event_payload LONGTEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY event_key (event_key),
			KEY created_at (created_at)
		) {$charset_collate};",
	);
}
