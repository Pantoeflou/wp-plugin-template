<?php
/**
 * WooCommerce integration.
 *
 * Many commercial plugins integrate with WooCommerce in some way (products,
 * orders, checkout fields, subscriptions, etc.). This template includes a
 * safe pattern:
 * - integration code exists, but is no-op unless WooCommerce is active
 * - all checks are centralized in one class
 * - if a feature is enabled but Woo isn't active, show an admin notice
 *
 * This keeps your plugin from fataling on sites that don't have WooCommerce.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/integrations/woocommerce
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce integration bootstrap.
 *
 * @since 0.2.0
 */
class WP_Forever_WooCommerce {

	/**
	 * Check if WooCommerce is active.
	 *
	 * @since 0.2.0
	 * @return bool
	 */
	public function is_available(): bool {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Register WooCommerce hooks.
	 *
	 * Call this from the main plugin orchestrator.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function register_hooks(): void {
		if ( ! $this->is_available() ) {
			// If you have Woo-only features, you can use this hook to warn admins.
			add_action( 'admin_notices', array( $this, 'maybe_show_missing_notice' ) );
			return;
		}

		// Example hook: add a product tab (placeholder).
		// add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_tab' ) );
	}

	/**
	 * Show an admin notice if a Woo-only feature is enabled.
	 *
	 * This template does not ship any Woo-only features by default, so this
	 * notice is disabled unless you wire it to a real setting.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function maybe_show_missing_notice(): void {
		// Example toggle: if your settings have a 'enable_woo_features' flag, check it here.
		$settings = (array) get_option( 'wp_forever_settings', array() );
		$enabled  = isset( $settings['enable_woo_features'] ) ? (bool) $settings['enable_woo_features'] : false;

		if ( ! $enabled ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html__( 'WP Forever: WooCommerce features are enabled, but WooCommerce is not active. Please install/activate WooCommerce or disable Woo features in settings.', 'wp-forever' )
		);
	}
}
