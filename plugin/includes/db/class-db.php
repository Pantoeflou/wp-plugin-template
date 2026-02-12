<?php
/**
 * Database access and lifecycle.
 *
 * This class is responsible for:
 * - creating/upgrading custom tables (activation + upgrades)
 * - providing helpers for table names
 * - providing a safe place to add query helpers later
 *
 * You do NOT need a DB class if your plugin only uses the Options API / Post Meta.
 * However, many "real" plugins eventually need at least one custom table.
 * Having the pattern in the template makes future work consistent.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes/db
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB service.
 *
 * @since 0.2.0
 */
class WP_Forever_DB {

	/**
	 * Option key used to store schema version.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	private string $schema_version_option = 'wp_forever_db_schema_version';

	/**
	 * Return the absolute table name for our events table.
	 *
	 * @since 0.2.0
	 * @global wpdb $wpdb
	 * @return string
	 */
	public function table_events(): string {
		global $wpdb;
		return $wpdb->prefix . 'wp_forever_events';
	}

	/**
	 * Create or upgrade custom tables.
	 *
	 * Call this on activation and on version upgrades.
	 * Uses dbDelta() so it can perform simple ALTERs.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function install_or_upgrade(): void {
		$installed = (string) get_option( $this->schema_version_option, '' );
		$target    = defined( 'WP_FOREVER_DB_SCHEMA_VERSION' ) ? (string) WP_FOREVER_DB_SCHEMA_VERSION : '';

		// If schema is up-to-date, do nothing.
		if ( ! empty( $installed ) && $installed === $target ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( wp_forever_db_schema_sql() as $sql ) {
			dbDelta( $sql );
		}

		update_option( $this->schema_version_option, $target );
	}

	/**
	 * Drop custom tables.
	 *
	 * Only call this during uninstall when the user explicitly opts in.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function drop_tables(): void {
		global $wpdb;
		$tables = array(
			$this->table_events(),
		);

		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		}

		delete_option( $this->schema_version_option );
	}
}
