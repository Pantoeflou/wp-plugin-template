<?php
/**
 * Logging settings tab partial.
 *
 * This file is included by settings-page.php when the "Logging" tab is active.
 * The main settings fields are rendered by do_settings_fields() in the parent template.
 *
 * Available fields in this tab:
 * - logging_enabled: Whether debug logging is active
 * - log_level: Minimum log level to record (debug, info, warning, error)
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/admin/views/settings-tabs
 * @since      0.2.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| Logging Tab Additional Content
|--------------------------------------------------------------------------
|
| Provide helpful information about logging configuration and how to
| access the log file.
|
*/
?>

<div class="wp-forever-tab-info">
	<h3><?php esc_html_e( 'About Logging', 'wp-forever' ); ?></h3>
	<p>
		<?php esc_html_e( 'When logging is enabled, messages are written to the WordPress debug log.', 'wp-forever' ); ?>
	</p>

	<h4><?php esc_html_e( 'Log File Location', 'wp-forever' ); ?></h4>
	<p>
		<?php
		// Show the log file path for reference.
		$log_path = WP_CONTENT_DIR . '/debug.log';
		printf(
			/* translators: %s: Path to the debug log file */
			esc_html__( 'Log file: %s', 'wp-forever' ),
			'<code>' . esc_html( $log_path ) . '</code>'
		);
		?>
	</p>

	<h4><?php esc_html_e( 'Log Levels', 'wp-forever' ); ?></h4>
	<ul>
		<li><strong><?php esc_html_e( 'Debug', 'wp-forever' ); ?></strong> — <?php esc_html_e( 'Detailed information for debugging. Very verbose.', 'wp-forever' ); ?></li>
		<li><strong><?php esc_html_e( 'Info', 'wp-forever' ); ?></strong> — <?php esc_html_e( 'General informational messages.', 'wp-forever' ); ?></li>
		<li><strong><?php esc_html_e( 'Warning', 'wp-forever' ); ?></strong> — <?php esc_html_e( 'Potential problems that may need attention.', 'wp-forever' ); ?></li>
		<li><strong><?php esc_html_e( 'Error', 'wp-forever' ); ?></strong> — <?php esc_html_e( 'Errors that need immediate attention.', 'wp-forever' ); ?></li>
	</ul>

	<p class="description">
		<?php esc_html_e( 'Tip: Use "Warning" or "Error" in production to minimize log file size.', 'wp-forever' ); ?>
	</p>

	<?php
	/*
	|--------------------------------------------------------------------------
	| WP_DEBUG Status
	|--------------------------------------------------------------------------
	|
	| Show the current WP_DEBUG status so users understand the fallback behavior.
	|
	*/
	?>
	<h4><?php esc_html_e( 'WordPress Debug Status', 'wp-forever' ); ?></h4>
	<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
		<p>
			<span class="dashicons dashicons-yes" style="color: #00a32a;"></span>
			<?php esc_html_e( 'WP_DEBUG is enabled. Logging will work even if not explicitly enabled above.', 'wp-forever' ); ?>
		</p>
	<?php else : ?>
		<p>
			<span class="dashicons dashicons-no" style="color: #d63638;"></span>
			<?php esc_html_e( 'WP_DEBUG is disabled. Enable "Enable Logging" above to log messages.', 'wp-forever' ); ?>
		</p>
	<?php endif; ?>
</div>
