<?php
/**
 * AJAX helper utilities.
 *
 * WordPress offers two common ways to build request/response interactions:
 * - admin-ajax.php (classic AJAX)
 * - REST API (modern)
 *
 * Many plugins still use admin-ajax.php for backwards compatibility or
 * because it integrates neatly with admin screens.
 *
 * This helper provides consistent patterns for:
 * - method enforcement (POST only)
 * - nonce verification
 * - capability enforcement (admin)
 * - consistent JSON responses
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes/ajax
 * @since      0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Small helper for admin-ajax handlers.
 *
 * @since 0.2.0
 */
final class WP_Forever_AJAX {

	/**
	 * Require a POST request.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public static function require_post(): void {
		if ( 'POST' !== strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
			self::send_error( __( 'Invalid request method.', 'wp-forever' ), 405 );
		}
	}

	/**
	 * Verify a nonce.
	 *
	 * @since 0.2.0
	 * @param string $nonce_action The action string used when creating the nonce.
	 * @param string $nonce_field  The request field containing the nonce (default: 'nonce').
	 * @return void
	 */
	public static function verify_nonce( string $nonce_action, string $nonce_field = 'nonce' ): void {
		$nonce = isset( $_POST[ $nonce_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			self::send_error( __( 'Security check failed.', 'wp-forever' ), 403 );
		}
	}

	/**
	 * Require a capability.
	 *
	 * @since 0.2.0
	 * @param string $cap Capability name (e.g. 'manage_options').
	 * @return void
	 */
	public static function require_cap( string $cap ): void {
		if ( ! current_user_can( $cap ) ) {
			self::send_error( __( 'You do not have permission to do that.', 'wp-forever' ), 403 );
		}
	}

	/**
	 * Send a success response and end execution.
	 *
	 * @since 0.2.0
	 * @param array  $data    Optional data payload.
	 * @param string $message Optional human message.
	 * @return void
	 */
	public static function send_success( array $data = array(), string $message = '' ): void {
		wp_send_json_success(
			array(
				'message' => $message,
				'data'    => $data,
			)
		);
	}

	/**
	 * Send an error response and end execution.
	 *
	 * @since 0.2.0
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 * @param array  $data    Optional data payload.
	 * @return void
	 */
	public static function send_error( string $message, int $status = 400, array $data = array() ): void {
		wp_send_json_error(
			array(
				'message' => $message,
				'data'    => $data,
			),
			$status
		);
	}
}
