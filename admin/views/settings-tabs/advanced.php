<?php
/**
 * Advanced settings tab partial.
 *
 * This file is included by settings-page.php when the "Advanced" tab is active.
 * The main settings fields are rendered by do_settings_fields() in the parent template.
 *
 * Available fields in this tab:
 * - debug_mode: Enable additional debugging features and verbose output
 * - rest_api_enabled: Enable/disable WP Forever REST routes
 * - enable_woo_features: Enable/disable WooCommerce hooks
 *
 * This tab contains settings that should be used with caution and are primarily
 * intended for developers or advanced troubleshooting.
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
| Advanced Tab Additional Content
|--------------------------------------------------------------------------
|
| Provide warnings and information about advanced settings.
|
*/
?>

<div class="wp-forever-tab-info">
	<h3><?php esc_html_e( 'Advanced Settings', 'wp-forever' ); ?></h3>

	<div class="notice notice-warning inline" style="margin: 15px 0;">
		<p>
			<strong><?php esc_html_e( 'Caution:', 'wp-forever' ); ?></strong>
			<?php esc_html_e( 'These settings are intended for developers and advanced troubleshooting. Changing them may affect plugin performance or behavior.', 'wp-forever' ); ?>
		</p>
	</div>

	<h4><?php esc_html_e( 'Debug Mode', 'wp-forever' ); ?></h4>
	<p>
		<?php esc_html_e( 'When debug mode is enabled:', 'wp-forever' ); ?>
	</p>
	<ul>
		<li><?php esc_html_e( 'Additional debugging information may be displayed in the admin', 'wp-forever' ); ?></li>
		<li><?php esc_html_e( 'More verbose error messages will be shown', 'wp-forever' ); ?></li>
		<li><?php esc_html_e( 'Performance may be slightly reduced', 'wp-forever' ); ?></li>
	</ul>
	<p class="description">
		<?php esc_html_e( 'Only enable this when actively troubleshooting issues. Disable it on production sites.', 'wp-forever' ); ?>
	</p>

	<h4><?php esc_html_e( 'REST API', 'wp-forever' ); ?></h4>
	<p>
		<?php esc_html_e( 'WP Forever ships a minimal REST route (ping) as a working example. Disable it if your plugin does not need public endpoints.', 'wp-forever' ); ?>
	</p>
	<p class="description">
		<?php esc_html_e( 'Example: /wp-json/wp-forever/v1/ping', 'wp-forever' ); ?>
	</p>

	<h4><?php esc_html_e( 'WooCommerce Integration', 'wp-forever' ); ?></h4>
	<p>
		<?php esc_html_e( 'Enable this to register WooCommerce-specific hooks. The plugin will remain safe on sites without WooCommerce, but features will be disabled.', 'wp-forever' ); ?>
	</p>

	<?php
	/*
	|--------------------------------------------------------------------------
	| System Information
	|--------------------------------------------------------------------------
	|
	| Display helpful system information for debugging purposes.
	|
	*/
	?>
	<h4><?php esc_html_e( 'System Information', 'wp-forever' ); ?></h4>
	<table class="widefat striped" style="max-width: 500px;">
		<tbody>
			<tr>
				<td><strong><?php esc_html_e( 'Plugin Version', 'wp-forever' ); ?></strong></td>
				<td><?php echo esc_html( WP_FOREVER_VERSION ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'WordPress Version', 'wp-forever' ); ?></strong></td>
				<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'PHP Version', 'wp-forever' ); ?></strong></td>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'WP_DEBUG', 'wp-forever' ); ?></strong></td>
				<td><?php echo ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? esc_html__( 'Enabled', 'wp-forever' ) : esc_html__( 'Disabled', 'wp-forever' ); ?></td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'WP_DEBUG_LOG', 'wp-forever' ); ?></strong></td>
				<td><?php echo ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? esc_html__( 'Enabled', 'wp-forever' ) : esc_html__( 'Disabled', 'wp-forever' ); ?></td>
			</tr>
		</tbody>
	</table>
</div>
