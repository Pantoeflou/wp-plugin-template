<?php
/**
 * REST API routes.
 *
 * WordPress REST API is the modern way to expose endpoints for:
 * - JavaScript front-ends
 * - integrations (Zapier, n8n, internal systems)
 * - headless WordPress setups
 *
 * This template ships a minimal namespace + one example route.
 * Delete or adjust this file if your plugin never needs custom endpoints.
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes/rest
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST router.
 *
 * @since 0.2.0
 */
class WP_Forever_REST {

	/**
	 * REST namespace for this plugin.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	private string $namespace = 'wp-forever/v1';

	/**
	 * Register routes.
	 *
	 * Hook this to `rest_api_init`.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/ping',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'ping' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Example endpoint.
	 *
	 * Useful to confirm that routing works and your namespace is set up.
	 *
	 * @since 0.2.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function ping( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'ok'      => true,
				'message' => __( 'WP Forever REST is alive.', 'wp-forever' ),
			),
			200
		);
	}
}
