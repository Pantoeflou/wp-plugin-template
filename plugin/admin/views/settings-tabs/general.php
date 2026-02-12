<?php
/**
 * General settings tab partial.
 *
 * This file is included by settings-page.php when the "General" tab is active.
 * The main settings fields are rendered by do_settings_fields() in the parent template.
 * This partial can contain additional content, explanatory text, or custom UI elements.
 *
 * Available fields in this tab:
 * - delete_data_on_uninstall: Whether to remove all plugin data when uninstalling
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
| General Tab Additional Content
|--------------------------------------------------------------------------
|
| This section can be used to add helpful information or documentation
| that doesn't fit in the field descriptions.
|
| The main field (delete_data_on_uninstall) is rendered automatically
| by the Settings API in the form-table above.
|
*/
?>

<div class="wp-forever-tab-info">
	<h3><?php esc_html_e( 'About WP Forever', 'wp-forever' ); ?></h3>
	<p>
		<?php
		printf(
			/* translators: %s: Plugin version number */
			esc_html__( 'You are using WP Forever version %s.', 'wp-forever' ),
			esc_html( WP_FOREVER_VERSION )
		);
		?>
	</p>
	<p>
		<?php esc_html_e( 'Use the settings on this page to configure the plugin behavior.', 'wp-forever' ); ?>
	</p>
</div>
