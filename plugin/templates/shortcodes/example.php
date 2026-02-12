<?php
/**
 * Default template for the [wp_forever_example] shortcode.
 *
 * Themes can override this template by creating:
 * - wp-forever/shortcodes/example.php in the active theme
 *
 * Available variables:
 * - $title (string)
 * - $message (string)
 * - $atts (array)
 *
 * @package WP_Forever
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wp-forever-box">
	<h3 class="wp-forever-box__title"><?php echo esc_html( $title ); ?></h3>
	<p><?php echo esc_html( $message ); ?></p>
	<p class="wp-forever-box__muted">
		<?php esc_html_e( 'This output is rendered from a template that your theme can override.', 'wp-forever' ); ?>
	</p>
</div>
