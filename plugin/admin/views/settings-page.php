<?php
/**
 * Settings page template.
 *
 * This is the main template for the WP Forever settings page.
 * It displays:
 * - Page title
 * - Tab navigation
 * - Settings form with current tab's fields
 *
 * Variables available in this template:
 * - $current_tab (string): The currently active tab slug
 * - $tabs (array): Available tabs (slug => label)
 *
 * The form submits to options.php (WordPress Settings API handles saving).
 * settings_fields() outputs the nonce and hidden fields.
 * Each tab partial renders its own fields.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/admin/views
 * @since      0.2.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<?php
	/*
	|--------------------------------------------------------------------------
	| Page Title
	|--------------------------------------------------------------------------
	|
	| Uses get_admin_page_title() which returns the title we set in
	| add_options_page(). This ensures consistency.
	|
	*/
	?>
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php
	/*
	|--------------------------------------------------------------------------
	| Tab Navigation
	|--------------------------------------------------------------------------
	|
	| WordPress uses the nav-tab-wrapper pattern for settings tabs.
	| The 'nav-tab-active' class highlights the current tab.
	|
	| We use add_query_arg() to build tab URLs, which properly handles
	| existing query parameters.
	|
	*/
	?>
	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_slug => $tab_label ) : ?>
			<?php
			// Build the URL for this tab.
			$tab_url = add_query_arg(
				array(
					'page' => 'wp-forever',
					'tab'  => $tab_slug,
				),
				admin_url( 'options-general.php' )
			);

			// Determine if this tab is active.
			$is_active = ( $current_tab === $tab_slug );
			$nav_class = $is_active ? 'nav-tab nav-tab-active' : 'nav-tab';
			?>
			<a href="<?php echo esc_url( $tab_url ); ?>" class="<?php echo esc_attr( $nav_class ); ?>">
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<?php
	/*
	|--------------------------------------------------------------------------
	| Settings Form
	|--------------------------------------------------------------------------
	|
	| The form submits to options.php which is WordPress's built-in handler
	| for the Settings API. It handles:
	| - Nonce verification
	| - Capability checking
	| - Calling our sanitize callback
	| - Saving to the database
	| - Redirecting back with success/error messages
	|
	| settings_fields() outputs:
	| - Nonce field for security
	| - Hidden fields WordPress needs for processing
	|
	*/
	?>
	<form method="post" action="options.php">
		<?php
		// Output nonce and hidden fields for this settings group.
		settings_fields( 'wp_forever_settings_group' );
		?>

		<div class="wp-forever-settings-content">
			<?php
			/*
			|--------------------------------------------------------------------------
			| Tab Content
			|--------------------------------------------------------------------------
			|
			| Each tab has its own partial file that contains the fields for that tab.
			| We use do_settings_sections() to render the fields registered with
			| add_settings_field().
			|
			| The page slug format is: wp-forever_{tab}
			| This matches what we registered in class-settings.php.
			|
			*/

			// Build the page slug for this tab's settings section.
			// Format: 'wp-forever_general', 'wp-forever_logging', etc.
			$settings_page_slug = 'wp-forever_' . $current_tab;

			// Render all sections and fields registered for this tab's page.
			do_settings_sections( $settings_page_slug );
			?>

			<?php
			// Include the tab-specific partial for any additional content.
			$tab_partial = WP_FOREVER_PLUGIN_DIR . 'admin/views/settings-tabs/' . $current_tab . '.php';
			if ( file_exists( $tab_partial ) ) {
				include $tab_partial;
			}
			?>
		</div>

		<?php
		/*
		|--------------------------------------------------------------------------
		| Submit Button
		|--------------------------------------------------------------------------
		|
		| submit_button() is a WordPress helper that outputs a properly styled
		| submit button with the right classes and text.
		|
		*/
		submit_button();
		?>
	</form>
</div>
