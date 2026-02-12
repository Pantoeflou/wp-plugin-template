<?php
/**
 * Admin notices handler.
 *
 * Integrates the Notice Manager with WordPress admin hooks.
 * Responsibilities:
 * - Register the welcome notice after plugin activation
 * - Hook the notice manager to admin_notices
 * - Handle AJAX dismiss requests
 * - Provide reset functionality for the Settings page
 *
 * This class is the "controller" that wires together:
 * - WP_Forever_Notice_Manager (the model/data layer)
 * - WordPress hooks (the integration layer)
 * - Settings page (the UI layer)
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/admin
 * @since      0.2.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin notices handler class.
 *
 * @since 0.2.0
 */
class WP_Forever_Notices {

	/**
	 * The notice manager instance.
	 *
	 * Handles the actual notice registration, rendering, and persistence.
	 *
	 * @since 0.2.0
	 * @var WP_Forever_Notice_Manager
	 */
	private WP_Forever_Notice_Manager $notice_manager;

	/**
	 * Constructor.
	 *
	 * Creates the notice manager instance.
	 *
	 * @since 0.2.0
	 */
	public function __construct() {
		$this->notice_manager = new WP_Forever_Notice_Manager();
	}

	/**
	 * Initialize the notices system.
	 *
	 * Registers all necessary hooks for notices to work.
	 * Should be called during plugin init (from class-plugin.php).
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function init(): void {
		// Register notices early in admin_init so they're ready for rendering.
		add_action( 'admin_init', array( $this, 'register_notices' ) );

		// Render notices on admin pages.
		add_action( 'admin_notices', array( $this, 'render_notices' ) );

		// Handle AJAX dismiss requests.
		add_action( 'wp_ajax_wp_forever_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );

		// Handle AJAX reset requests.
		add_action( 'wp_ajax_wp_forever_reset_notices', array( $this, 'ajax_reset_notices' ) );
	}

	/**
	 * Register plugin notices.
	 *
	 * Called on 'admin_init'. Add any notices that should be displayed
	 * to users here.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function register_notices(): void {
		// Add the welcome notice (shown after activation).
		$this->maybe_add_welcome_notice();

		// Future notices can be added here:
		// $this->maybe_add_update_notice();
		// $this->maybe_add_feature_notice();
	}

	/**
	 * Register the welcome notice.
	 *
	 * Shows after plugin activation if enabled in settings.
	 * Includes a link to the settings page to help users get started.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	private function maybe_add_welcome_notice(): void {
		// Check if welcome notice is enabled in settings.
		// Default is true — show unless user has explicitly disabled.
		if ( ! wp_forever_get_setting( 'show_welcome_notice', true ) ) {
			return;
		}

		// Build the welcome message with a link to settings.
		$message = sprintf(
			/* translators: %s: URL to the settings page */
			__( 'Thanks for installing WP Forever! <a href="%s">Configure your settings</a> to get started.', 'wp-forever' ),
			esc_url( admin_url( 'options-general.php?page=wp-forever' ) )
		);

		// Register the notice.
		$this->notice_manager->add_notice(
			'welcome',
			$message,
			'info',
			array(
				'dismissible' => true,
				'persistent'  => true,
			)
		);
	}

	/**
	 * Render all registered notices.
	 *
	 * Callback for 'admin_notices' hook. Delegates to the notice manager.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function render_notices(): void {
		$this->notice_manager->render_notices();
	}

	/**
	 * Handle AJAX notice dismissal.
	 *
	 * Called when user clicks the X button on a notice.
	 * Validates the request and persists the dismissal to user meta.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function ajax_dismiss_notice(): void {
		// Verify nonce for security.
		// The nonce is passed from admin.js which gets it from wp_localize_script.
		check_ajax_referer( 'wp_forever_dismiss_notice', 'nonce' );

		// Get and sanitize the notice ID.
		// sanitize_key() only allows lowercase alphanumeric, dashes, underscores.
		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : '';

		// Validate we have an ID.
		if ( empty( $notice_id ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid notice ID.', 'wp-forever' ) ),
				400
			);
		}

		// Dismiss the notice for this user.
		$this->notice_manager->dismiss( $notice_id );

		// Log the dismissal if logging is enabled.
		wp_forever_log(
			sprintf( 'Notice "%s" dismissed by user %d', $notice_id, get_current_user_id() ),
			'debug'
		);

		// Return success response.
		wp_send_json_success( array( 'dismissed' => $notice_id ) );
	}

	/**
	 * Handle AJAX reset notices.
	 *
	 * Called from the Settings page "Reset Notices" button.
	 * Clears all dismissed notices for the current user.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function ajax_reset_notices(): void {
		// Verify nonce for security.
		check_ajax_referer( 'wp_forever_reset_notices', 'nonce' );

		// Verify user has permission (only users who can manage options).
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Permission denied.', 'wp-forever' ) ),
				403
			);
		}

		// Reset all dismissed notices.
		$this->notice_manager->reset_dismissed();

		// Log the reset.
		wp_forever_log(
			sprintf( 'Dismissed notices reset by user %d', get_current_user_id() ),
			'info'
		);

		// Return success response.
		wp_send_json_success(
			array( 'message' => __( 'Dismissed notices have been reset.', 'wp-forever' ) )
		);
	}

	/**
	 * Get the notice manager instance.
	 *
	 * Allows external code to register additional notices.
	 *
	 * @since 0.2.0
	 *
	 * @return WP_Forever_Notice_Manager The notice manager.
	 */
	public function get_notice_manager(): WP_Forever_Notice_Manager {
		return $this->notice_manager;
	}
}
