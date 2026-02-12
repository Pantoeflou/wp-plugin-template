<?php
/**
 * Public-facing functionality.
 *
 * This class handles everything that runs on the front-end (public) side:
 * - Enqueueing public styles and scripts
 * - Shortcode output (Phase 3)
 * - Public-facing hooks and filters
 *
 * "Public" in WordPress terms means "not the admin area" — it's the
 * website that visitors see.
 *
 * Why separate public from admin?
 * - Performance: Public pages shouldn't load admin code
 * - Security: Public code has different permission requirements
 * - Organization: Clear separation of concerns
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/public
 * @since      0.1.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for enqueueing
 * the public-facing stylesheet and JavaScript.
 *
 * @since 0.1.0
 */
class WP_Forever_Public {

	/**
	 * Whether we should load public assets on the current request.
	 *
	 * We try to keep public asset loading lightweight by default. This flag can be
	 * set in a few ways:
	 * - automatic detection via has_shortcode() on singular content
	 * - forced to true when the shortcode renders (e.g. if output is injected elsewhere)
	 *
	 * @since 0.2.0
	 * @var bool
	 */
	private bool $should_enqueue_assets = false;

	/**
	 * Register shortcodes.
	 *
	 * Hook this to 'init'.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function register_shortcodes(): void {
		add_shortcode( 'wp_forever_example', array( $this, 'render_example_shortcode' ) );
	}

	/**
	 * Enqueue public styles.
	 *
	 * This method is hooked to 'wp_enqueue_scripts' and loads CSS
	 * files needed on the front-end.
	 *
	 * This template loads a small public stylesheet as an example.
	 *
	 * We load it conditionally (only when the example shortcode is present) to
	 * avoid affecting site performance.
	 *
	 * Tips for public styles:
	 * - Keep them minimal to avoid impacting page load
	 * - Consider loading conditionally (only on pages that need them)
	 * - Use unique class prefixes to avoid conflicts with themes
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function enqueue_styles(): void {
		$this->detect_assets_need();
		if ( ! $this->should_enqueue_assets ) {
			return;
		}

		wp_enqueue_style(
			'wp-forever-public',
			WP_FOREVER_PLUGIN_URL . 'assets/css/public.css',
			array(),
			WP_FOREVER_VERSION
		);
	}

	/**
	 * Enqueue public scripts.
	 *
	 * This method is hooked to 'wp_enqueue_scripts' and loads JavaScript
	 * files needed on the front-end.
	 *
	 * This template loads a small public JS file as an example.
	 *
	 * It demonstrates frontend AJAX to admin-ajax.php with a nonce.
	 *
	 * Tips for public scripts:
	 * - Load in footer (last parameter = true) for better performance
	 * - Minimize dependencies on heavy libraries
	 * - Consider loading conditionally (only on pages that need them)
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$this->detect_assets_need();
		if ( ! $this->should_enqueue_assets ) {
			return;
		}

		wp_enqueue_script(
			'wp-forever-public',
			WP_FOREVER_PLUGIN_URL . 'assets/js/public.js',
			array(),
			WP_FOREVER_VERSION,
			true
		);

		wp_localize_script(
			'wp-forever-public',
			'wpForeverPublic',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wp_forever_public' ),
				'debug'   => $this->is_debug_mode(),
			)
		);
	}

	/**
	 * Check whether debug mode is enabled.
	 *
	 * Debug mode is enabled if either:
	 * - the plugin setting "Debug Mode" is checked, OR
	 * - WP_DEBUG is true (developer environment)
	 *
	 * @since 0.2.0
	 * @return bool
	 */
	private function is_debug_mode(): bool {
		$settings = (array) get_option( 'wp_forever_settings', array() );
		$flag     = isset( $settings['debug_mode'] ) ? (bool) $settings['debug_mode'] : false;
		return $flag || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	}

	/**
	 * Render the example shortcode.
	 *
	 * Shortcode tag: [wp_forever_example]
	 *
	 * This demonstrates:
	 * - shortcode registration
	 * - template-based rendering
	 * - theme overrides via WP_Forever_Template_Loader
	 * - safe escaping
	 *
	 * @since 0.2.0
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_example_shortcode( array $atts = array() ): string {
		$this->should_enqueue_assets = true;

		$atts = shortcode_atts(
			array(
				'title'   => __( 'WP Forever Example', 'wp-forever' ),
				'message' => __( 'Hello from a theme-overridable plugin template!', 'wp-forever' ),
			),
			$atts,
			'wp_forever_example'
		);

		$title   = (string) $atts['title'];
		$message = (string) $atts['message'];

		return WP_Forever_Services::templates()->render(
			'shortcodes/example.php',
			array(
				'title'   => $title,
				'message' => $message,
				'atts'    => $atts,
			)
		);
	}

	/**
	 * Handle a public AJAX "ping".
	 *
	 * Action: wp_forever_public_ping
	 * Hooks:
	 * - wp_ajax_wp_forever_public_ping
	 * - wp_ajax_nopriv_wp_forever_public_ping
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function handle_public_ping(): void {
		WP_Forever_AJAX::require_post();
		WP_Forever_AJAX::verify_nonce( 'wp_forever_public' );

		WP_Forever_AJAX::send_success(
			array(
				'time' => current_time( 'mysql' ),
			),
			__( 'Pong', 'wp-forever' )
		);
	}

	/**
	 * Decide if public assets should load on this request.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	private function detect_assets_need(): void {
		if ( $this->should_enqueue_assets ) {
			return;
		}

		// Only attempt auto-detection on singular pages where global $post is meaningful.
		if ( is_singular() ) {
			global $post;
			if ( $post instanceof WP_Post && ! empty( $post->post_content ) ) {
				$this->should_enqueue_assets = has_shortcode( (string) $post->post_content, 'wp_forever_example' );
			}
		}
	}
}
