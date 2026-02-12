<?php
/**
 * Template loader.
 *
 * Many plugins render HTML by including PHP template files. The best-practice
 * WordPress UX is to allow themes to override those templates.
 *
 * This class implements a simple, predictable template resolution:
 *
 * 1) Theme override (child theme wins)
 *    - {theme}/wp-forever/{template}
 * 2) Plugin default
 *    - {plugin}/templates/{template}
 *
 * You can also filter the resolved path.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads templates with theme overrides.
 *
 * @since 0.2.0
 */
class WP_Forever_Template_Loader {

	/**
	 * Subdirectory inside the active theme where overrides live.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	private string $theme_subdir = 'wp-forever';

	/**
	 * Locate a template.
	 *
	 * @since 0.2.0
	 * @param string $template Relative path within templates folder (e.g. 'shortcodes/example.php').
	 * @return string|false Absolute path to template or false if not found.
	 */
	public function locate( string $template ) {
		$template = ltrim( $template, '/\\' );

		// 1) Theme override.
		$theme_path = trailingslashit( get_stylesheet_directory() ) . trailingslashit( $this->theme_subdir ) . $template;
		if ( file_exists( $theme_path ) ) {
			/**
			 * Filter the resolved template path.
			 *
			 * @since 0.2.0
			 * @param string $path     Absolute resolved path.
			 * @param string $template Relative template requested.
			 * @param string $source   Source label: 'theme'|'plugin'.
			 */
			return apply_filters( 'wp_forever/template_path', $theme_path, $template, 'theme' );
		}

		// 2) Plugin default.
		$plugin_path = trailingslashit( WP_FOREVER_PLUGIN_DIR ) . 'templates/' . $template;
		if ( file_exists( $plugin_path ) ) {
			return apply_filters( 'wp_forever/template_path', $plugin_path, $template, 'plugin' );
		}

		return false;
	}

	/**
	 * Render a template and return the HTML.
	 *
	 * Variables passed in $vars are extracted into the template scope.
	 *
	 * @since 0.2.0
	 * @param string $template Relative template path.
	 * @param array  $vars     Variables to extract into the template.
	 * @return string Rendered output (or empty string if template missing).
	 */
	public function render( string $template, array $vars = array() ): string {
		$path = $this->locate( $template );
		if ( false === $path ) {
			return '';
		}

		ob_start();
		if ( ! empty( $vars ) ) {
			extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}
		include $path;
		return (string) ob_get_clean();
	}
}
