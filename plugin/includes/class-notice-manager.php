<?php
/**
 * Admin notice manager.
 *
 * Handles registering, rendering, and dismissing admin notices.
 * Tracks dismissed notices in user meta for persistence across sessions.
 *
 * How it works:
 * 1. Notices are registered via add_notice() during plugin init
 * 2. On admin page load, render_notices() outputs them (via admin_notices hook)
 * 3. User clicks X → AJAX saves to user meta → notice hidden permanently
 * 4. On next page load, is_dismissed() checks user meta → notice skipped
 *
 * Notice types (WordPress standard):
 * - success: Green, positive confirmation
 * - error:   Red, something went wrong
 * - warning: Yellow/orange, attention needed
 * - info:    Blue, neutral information
 *
 * Usage:
 *     $manager = new WP_Forever_Notice_Manager();
 *     $manager->add_notice( 'my-notice', 'Hello world!', 'info' );
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes
 * @since      0.2.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notice manager class.
 *
 * Manages the lifecycle of admin notices: registration, rendering,
 * and dismissal persistence.
 *
 * @since 0.2.0
 */
class WP_Forever_Notice_Manager {

	/**
	 * Registered notices.
	 *
	 * Array of notices indexed by ID. Each notice is an array with keys:
	 * - id: Unique identifier
	 * - message: HTML content to display
	 * - type: success|error|warning|info
	 * - dismissible: Whether user can dismiss
	 * - persistent: Whether dismissal persists across sessions
	 *
	 * @since 0.2.0
	 * @var array<string, array>
	 */
	private array $notices = array();

	/**
	 * User meta key for storing dismissed notice IDs.
	 *
	 * Each user has their own dismissed notices, so we use user meta.
	 * The value is an array of notice IDs that have been dismissed.
	 *
	 * @since 0.2.0
	 * @var string
	 */
	private const DISMISSED_META_KEY = 'wp_forever_dismissed_notices';

	/**
	 * Register a notice to be displayed.
	 *
	 * Call this during plugin initialization (e.g., on 'admin_init').
	 * Notices are only displayed if the user hasn't dismissed them.
	 *
	 * @since 0.2.0
	 *
	 * @param string $id      Unique notice identifier (alphanumeric, dashes, underscores).
	 * @param string $message The notice message. Can contain HTML (will be sanitized on output).
	 * @param string $type    Notice type: 'success', 'error', 'warning', 'info'.
	 * @param array  $args    Optional arguments:
	 *                        - dismissible (bool): Show X button. Default true.
	 *                        - persistent (bool): Remember dismissal. Default true.
	 * @return void
	 */
	public function add_notice(
		string $id,
		string $message,
		string $type = 'info',
		array $args = array()
	): void {
		// Default values for optional args.
		$defaults = array(
			'dismissible' => true,  // Show the X button.
			'persistent'  => true,  // Remember dismissal across sessions.
		);

		// Merge defaults with provided args, then add the required fields.
		$this->notices[ $id ] = array_merge( $defaults, $args, array(
			'id'      => $id,
			'message' => $message,
			'type'    => $type,
		) );
	}

	/**
	 * Render all registered notices.
	 *
	 * This is hooked to 'admin_notices'. It loops through all registered
	 * notices and outputs those that haven't been dismissed.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function render_notices(): void {
		foreach ( $this->notices as $id => $notice ) {
			// Skip notices that have been dismissed (if persistent).
			if ( $notice['persistent'] && $this->is_dismissed( $id ) ) {
				continue;
			}

			$this->render_notice( $notice );
		}
	}

	/**
	 * Render a single notice.
	 *
	 * Outputs the HTML for one admin notice with proper classes
	 * and data attributes for JavaScript integration.
	 *
	 * @since 0.2.0
	 *
	 * @param array $notice Notice data array.
	 * @return void
	 */
	private function render_notice( array $notice ): void {
		// Build the CSS classes array.
		$classes = array(
			'notice',                              // WordPress base class.
			'notice-' . esc_attr( $notice['type'] ), // Type-specific styling.
		);

		// Add dismissible class if X button should show.
		if ( $notice['dismissible'] ) {
			$classes[] = 'is-dismissible';
		}

		// Add our custom class for JavaScript targeting.
		// This lets us find WP Forever notices specifically.
		$classes[] = 'wp-forever-notice';

		// Output the notice HTML.
		// - data-notice-id: Used by JS to identify which notice was dismissed
		// - wp_kses_post: Allows safe HTML (links, strong, em, etc.)
		printf(
			'<div class="%s" data-notice-id="%s"><p>%s</p></div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $notice['id'] ),
			wp_kses_post( $notice['message'] )
		);
	}

	/**
	 * Check if a notice has been dismissed by the current user.
	 *
	 * Looks up the user meta to see if this notice ID is in their
	 * dismissed list.
	 *
	 * @since 0.2.0
	 *
	 * @param string $id The notice ID to check.
	 * @return bool True if dismissed, false otherwise.
	 */
	public function is_dismissed( string $id ): bool {
		$user_id = get_current_user_id();

		// No user logged in means no dismissal tracking.
		if ( ! $user_id ) {
			return false;
		}

		// Get the dismissed notices array from user meta.
		$dismissed = get_user_meta( $user_id, self::DISMISSED_META_KEY, true );

		// Ensure we have an array (empty string on first access).
		$dismissed = is_array( $dismissed ) ? $dismissed : array();

		// Check if this notice ID is in the dismissed list.
		return in_array( $id, $dismissed, true );
	}

	/**
	 * Dismiss a notice for the current user.
	 *
	 * Adds the notice ID to the user's dismissed list in user meta.
	 * Called via AJAX when user clicks the X button.
	 *
	 * @since 0.2.0
	 *
	 * @param string $id The notice ID to dismiss.
	 * @return void
	 */
	public function dismiss( string $id ): void {
		$user_id = get_current_user_id();

		// No user logged in means no dismissal tracking.
		if ( ! $user_id ) {
			return;
		}

		// Get current dismissed list.
		$dismissed = get_user_meta( $user_id, self::DISMISSED_META_KEY, true );
		$dismissed = is_array( $dismissed ) ? $dismissed : array();

		// Only add if not already dismissed.
		if ( ! in_array( $id, $dismissed, true ) ) {
			$dismissed[] = $id;
			update_user_meta( $user_id, self::DISMISSED_META_KEY, $dismissed );
		}
	}

	/**
	 * Reset all dismissed notices for the current user.
	 *
	 * Clears the user meta, allowing all notices to show again.
	 * Called from the Settings page "Reset Notices" button.
	 *
	 * @since 0.2.0
	 *
	 * @return void
	 */
	public function reset_dismissed(): void {
		$user_id = get_current_user_id();

		// No user logged in means nothing to reset.
		if ( ! $user_id ) {
			return;
		}

		delete_user_meta( $user_id, self::DISMISSED_META_KEY );
	}

	/**
	 * Get all registered notices.
	 *
	 * Useful for debugging or building admin UI.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, array> All registered notices.
	 */
	public function get_notices(): array {
		return $this->notices;
	}

	/**
	 * Get the user meta key for dismissed notices.
	 *
	 * Useful for cleanup during uninstall.
	 *
	 * @since 0.2.0
	 *
	 * @return string The meta key.
	 */
	public static function get_dismissed_meta_key(): string {
		return self::DISMISSED_META_KEY;
	}
}
