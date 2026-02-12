/* global wpForeverPublic */
/**
 * WP Forever — public script.
 *
 * This file demonstrates a safe frontend AJAX pattern.
 *
 * It ships with the template so you have:
 * - a working enqueue example
 * - a working AJAX request example
 *
 * If your plugin doesn't need frontend JS, you can delete this file
 * and remove the enqueue from public/class-public.php.
 */

(function () {
	'use strict';

	if ( typeof wpForeverPublic === 'undefined' ) {
		return;
	}

	function ping() {
		var data = new FormData();
		data.append( 'action', 'wp_forever_public_ping' );
		data.append( 'nonce', wpForeverPublic.nonce );

		fetch( wpForeverPublic.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data
		} )
			.then( function (res) { return res.json(); } )
			.then( function (json) {
				// eslint-disable-next-line no-console
				if ( wpForeverPublic.debug && window.console ) console.log( 'WP Forever ping:', json );
			} )
			.catch( function () {
				// Swallow errors by default — frontend should not break the page.
			} );
	}

	// Example: ping on DOM ready.
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', ping );
	} else {
		ping();
	}
})();
