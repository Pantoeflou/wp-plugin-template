<?php
/**
 * Notices settings tab partial.
 *
 * This file is included by settings-page.php when the "Notices" tab is active.
 * The main settings fields are rendered by do_settings_fields() in the parent template.
 *
 * Available fields in this tab:
 * - show_welcome_notice: Whether to show the welcome notice to new users
 *
 * This tab also includes a "Reset Dismissed Notices" button that allows
 * users to re-enable notices they've previously dismissed.
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
| Notices Tab Additional Content
|--------------------------------------------------------------------------
|
| Provide information about how notices work and a way to reset them.
|
*/
?>

<div class="wp-forever-tab-info">
	<h3><?php esc_html_e( 'About Notices', 'wp-forever' ); ?></h3>
	<p>
		<?php esc_html_e( 'WP Forever displays helpful notices in the WordPress admin to guide you through setup and important updates.', 'wp-forever' ); ?>
	</p>
	<p>
		<?php esc_html_e( 'You can dismiss most notices by clicking the X button. Once dismissed, a notice will not appear again.', 'wp-forever' ); ?>
	</p>

	<?php
	/*
	|--------------------------------------------------------------------------
	| Reset Dismissed Notices
	|--------------------------------------------------------------------------
	|
	| This button allows users to reset all dismissed notices so they can
	| see them again. Useful if they dismissed something accidentally.
	|
	| The actual reset is handled via AJAX in Group E (Notices module).
	| For now, we add the button structure that will be wired up later.
	|
	*/
	?>
	<h4><?php esc_html_e( 'Reset Dismissed Notices', 'wp-forever' ); ?></h4>
	<p>
		<?php esc_html_e( 'If you have dismissed notices that you want to see again, click the button below to reset them.', 'wp-forever' ); ?>
	</p>
	<p>
		<button type="button" class="button button-secondary" id="wp-forever-reset-notices" disabled>
			<?php esc_html_e( 'Reset All Dismissed Notices', 'wp-forever' ); ?>
		</button>
		<span class="description" style="margin-left: 10px;">
			<?php esc_html_e( '(This feature will be available in a future update)', 'wp-forever' ); ?>
		</span>
	</p>
	<p class="description">
		<?php esc_html_e( 'Note: This only affects notices for your user account.', 'wp-forever' ); ?>
	</p>
</div>
