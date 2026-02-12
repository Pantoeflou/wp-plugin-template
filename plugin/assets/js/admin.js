/**
 * WP Forever - Admin Scripts
 *
 * JavaScript for the plugin's admin interface.
 * Loaded via wp_enqueue_script() in WP_Forever_Admin::enqueue_scripts().
 *
 * Available globals (from wp_localize_script):
 * - wpForever.ajaxUrl      — WordPress AJAX endpoint URL
 * - wpForever.nonce        — General security nonce for AJAX requests
 * - wpForever.dismissNonce — Nonce for notice dismissal
 * - wpForever.resetNonce   — Nonce for notice reset
 *
 * Guidelines:
 * - Use vanilla JS (no jQuery dependency unless necessary)
 * - Always use nonces for AJAX requests
 * - Handle errors gracefully with user feedback
 * - Keep functions small and focused
 *
 * @package WP_Forever
 * @since   0.1.0
 * @since   0.2.0 Added notice dismissal functionality.
 */

( function() {
	'use strict';

	/*
	|--------------------------------------------------------------------------
	| Configuration
	|--------------------------------------------------------------------------
	*/

	// Access localized data from PHP.
	// These are set via wp_localize_script() in class-admin.php.
	const config = window.wpForever || {
		ajaxUrl: '',
		nonce: '',
		dismissNonce: '',
		resetNonce: ''
	};

	// Access WordPress i18n functions for translations.
	// These are provided by the wp-i18n dependency.
	// Usage: __( 'String to translate', 'wp-forever' )
	const { __, _n, sprintf } = wp.i18n;

	/*
	|--------------------------------------------------------------------------
	| Initialization
	|--------------------------------------------------------------------------
	*/

	/**
	 * Initialize admin functionality.
	 *
	 * Called when DOM is ready. Set up event listeners and initialize
	 * any interactive components here.
	 */
	function init() {
		// Set up event listeners.
		bindEvents();

		// Initialize notice dismissal handlers.
		initNoticeDismiss();

		// Initialize reset notices button (on Settings page).
		initResetNotices();
	}

	/**
	 * Bind event listeners.
	 *
	 * Keep event binding organized in one place.
	 * Use event delegation where possible for better performance.
	 */
	function bindEvents() {
		// Event listeners are set up in the specific init functions.
		// This function is kept for future use.
	}

	/*
	|--------------------------------------------------------------------------
	| AJAX Helpers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Make an AJAX request to WordPress.
	 *
	 * Example usage:
	 *     const response = await makeAjaxRequest( 'wp_forever_save_settings', { key: 'value' } );
	 *
	 * @param {string} action - The AJAX action name (matches add_action( 'wp_ajax_...' )).
	 * @param {Object} data   - Additional data to send with the request.
	 * @returns {Promise}     - Resolves with response data or rejects with error.
	 */
	async function makeAjaxRequest( action, data = {} ) {
		const formData = new FormData();

		// Required: AJAX action name.
		formData.append( 'action', action );

		// Required: Security nonce.
		formData.append( 'nonce', config.nonce );

		// Append additional data.
		Object.entries( data ).forEach( ( [ key, value ] ) => {
			formData.append( key, value );
		} );

		try {
			const response = await fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin', // Include cookies for authentication.
				body: formData
			} );

			const result = await response.json();

			if ( result.success ) {
				return result.data;
			} else {
				throw new Error( result.data || 'Unknown error' );
			}
		} catch ( error ) {
			console.error( 'WP Forever AJAX error:', error );
			throw error;
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Event Handlers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Example: Handle form submission via AJAX.
	 *
	 * Uncomment and customize when you have forms to handle.
	 *
	 * @param {Event} event - The form submit event.
	 */
	// async function handleFormSubmit( event ) {
	//     event.preventDefault();
	//
	//     const form = event.target;
	//     const submitButton = form.querySelector( '[type="submit"]' );
	//     const originalText = submitButton.textContent;
	//
	//     try {
	//         // Show loading state.
	//         submitButton.textContent = 'Saving...';
	//         submitButton.disabled = true;
	//
	//         // Collect form data.
	//         const formData = new FormData( form );
	//         const data = Object.fromEntries( formData.entries() );
	//
	//         // Make AJAX request.
	//         await makeAjaxRequest( 'wp_forever_save_settings', data );
	//
	//         // Show success message.
	//         showNotice( 'Settings saved successfully.', 'success' );
	//
	//     } catch ( error ) {
	//         // Show error message.
	//         showNotice( 'Error saving settings: ' + error.message, 'error' );
	//
	//     } finally {
	//         // Restore button state.
	//         submitButton.textContent = originalText;
	//         submitButton.disabled = false;
	//     }
	// }

	/*
	|--------------------------------------------------------------------------
	| Notice Handling
	|--------------------------------------------------------------------------
	*/

	/**
	 * Initialize notice dismissal handlers.
	 *
	 * WordPress adds the X button to .is-dismissible notices automatically,
	 * but doesn't persist the dismissal. We intercept the click to send
	 * an AJAX request that saves the dismissal to user meta.
	 */
	function initNoticeDismiss() {
		// Find all WP Forever notices that are dismissible.
		const notices = document.querySelectorAll( '.wp-forever-notice.is-dismissible' );

		notices.forEach( function( notice ) {
			// WordPress adds the dismiss button after a short delay.
			// We use MutationObserver to wait for it, or check if already present.
			const dismissButton = notice.querySelector( '.notice-dismiss' );

			if ( dismissButton ) {
				// Button already exists, bind handler.
				bindDismissHandler( notice, dismissButton );
			} else {
				// Wait for WordPress to add the button.
				observeForDismissButton( notice );
			}
		} );
	}

	/**
	 * Observe a notice for the dismiss button to be added.
	 *
	 * WordPress adds the .notice-dismiss button via JavaScript after page load.
	 * We use MutationObserver to detect when it's added.
	 *
	 * @param {Element} notice - The notice element to observe.
	 */
	function observeForDismissButton( notice ) {
		const observer = new MutationObserver( function( mutations, obs ) {
			const dismissButton = notice.querySelector( '.notice-dismiss' );
			if ( dismissButton ) {
				bindDismissHandler( notice, dismissButton );
				obs.disconnect(); // Stop observing once found.
			}
		} );

		observer.observe( notice, { childList: true, subtree: true } );

		// Safety timeout: stop observing after 5 seconds.
		setTimeout( function() {
			observer.disconnect();
		}, 5000 );
	}

	/**
	 * Bind the dismiss handler to a notice's dismiss button.
	 *
	 * @param {Element} notice        - The notice element.
	 * @param {Element} dismissButton - The dismiss button element.
	 */
	function bindDismissHandler( notice, dismissButton ) {
		dismissButton.addEventListener( 'click', function() {
			// Get the notice ID from the data attribute.
			const noticeId = notice.dataset.noticeId;

			if ( ! noticeId ) {
				return; // No ID means we can't persist dismissal.
			}

			// Send AJAX request to persist the dismissal.
			// We don't need to wait for the response — the notice is already
			// being removed by WordPress's built-in handler.
			const formData = new FormData();
			formData.append( 'action', 'wp_forever_dismiss_notice' );
			formData.append( 'notice_id', noticeId );
			formData.append( 'nonce', config.dismissNonce );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			} ).catch( function( error ) {
				// Log error but don't interrupt user experience.
				console.error( 'WP Forever: Failed to persist notice dismissal:', error );
			} );
		} );
	}

	/**
	 * Initialize the Reset Notices button on the Settings page.
	 *
	 * This button clears all dismissed notices for the current user,
	 * allowing them to see notices they've previously dismissed.
	 */
	function initResetNotices() {
		const resetButton = document.getElementById( 'wp-forever-reset-notices' );

		if ( ! resetButton ) {
			return; // Button not on this page.
		}

		// Enable the button (it starts disabled until JS loads).
		resetButton.disabled = false;

		// Update the description text.
		const description = resetButton.nextElementSibling;
		if ( description && description.classList.contains( 'description' ) ) {
			description.textContent = '';
		}

		resetButton.addEventListener( 'click', function() {
			// Confirm the action.
			// Using __() for translatable string.
			if ( ! confirm( __( 'Reset all dismissed notices? They will appear again.', 'wp-forever' ) ) ) {
				return;
			}

			// Show loading state.
			const originalText = resetButton.textContent;
			resetButton.textContent = __( 'Resetting...', 'wp-forever' );
			resetButton.disabled = true;

			// Send AJAX request.
			const formData = new FormData();
			formData.append( 'action', 'wp_forever_reset_notices' );
			formData.append( 'nonce', config.resetNonce );

			fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			} )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( result ) {
				if ( result.success ) {
					// Show success message.
					alert( result.data.message || __( 'Notices have been reset.', 'wp-forever' ) );
				} else {
					// Show error message.
					const errorPrefix = __( 'Error:', 'wp-forever' );
					alert( errorPrefix + ' ' + ( result.data.message || __( 'Failed to reset notices.', 'wp-forever' ) ) );
				}
			} )
			.catch( function( error ) {
				console.error( 'WP Forever: Failed to reset notices:', error );
				alert( __( 'Error: Failed to reset notices. Please try again.', 'wp-forever' ) );
			} )
			.finally( function() {
				// Restore button state.
				resetButton.textContent = originalText;
				resetButton.disabled = false;
			} );
		} );
	}

	/*
	|--------------------------------------------------------------------------
	| DOM Ready
	|--------------------------------------------------------------------------
	*/

	// Initialize when DOM is ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		// DOM already loaded (script loaded with defer or at end of body).
		init();
	}

} )();
