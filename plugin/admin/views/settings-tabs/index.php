<?php
/**
 * Silence is golden.
 *
 * This file prevents directory browsing. If someone accesses this directory
 * directly via URL, they see a blank page instead of a file listing.
 *
 * @package WP_Forever
 * @since   0.2.0
 */

// Prevent direct access and exit silently.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
