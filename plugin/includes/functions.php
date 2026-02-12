<?php
/**
 * Global helper functions.
 *
 * This file contains utility functions that are used throughout the plugin.
 * These functions are available in both admin and public contexts.
 *
 * Guidelines for this file:
 * - Only add functions used in multiple places
 * - Keep functions small and focused (single responsibility)
 * - Prefix all functions with 'wp_forever_' to avoid conflicts
 * - Document each function with PHPDoc
 *
 * @package    WP_Forever
 * @subpackage WP_Forever/includes
 * @since      0.1.0
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a plugin option with default fallback.
 *
 * This is a convenience wrapper around get_option() that handles
 * the common pattern of storing multiple settings in a single option array.
 *
 * Example usage:
 *     $api_key = wp_forever_get_option( 'api_key', '' );
 *     $enabled = wp_forever_get_option( 'feature_enabled', false );
 *
 * Why use this instead of get_option() directly?
 * - Cleaner code when accessing individual settings
 * - Centralized default value handling
 * - Easy to change storage structure later
 *
 * @since 0.1.0
 *
 * @param string $key     The option key (without prefix).
 * @param mixed  $default Default value if option doesn't exist.
 * @return mixed The option value, or $default if not set.
 */
function wp_forever_get_option( string $key, mixed $default = null ): mixed {
	// All plugin settings are stored in a single option array.
	// This reduces database queries and keeps options organized.
	$options = get_option( 'wp_forever_settings', array() );

	// Return the specific key if it exists, otherwise the default.
	return $options[ $key ] ?? $default;
}

/**
 * Update a plugin option.
 *
 * This is a convenience wrapper for updating a single key within
 * the plugin's settings array.
 *
 * Example usage:
 *     wp_forever_update_option( 'api_key', 'abc123' );
 *
 * @since 0.1.0
 *
 * @param string $key   The option key (without prefix).
 * @param mixed  $value The value to store.
 * @return bool True if option was updated, false otherwise.
 */
function wp_forever_update_option( string $key, mixed $value ): bool {
	$options         = get_option( 'wp_forever_settings', array() );
	$options[ $key ] = $value;

	return update_option( 'wp_forever_settings', $options );
}

/**
 * Check if the plugin is in debug mode.
 *
 * Debug mode is enabled when WP_DEBUG is true. Use this to conditionally
 * enable verbose logging, show debug info, etc.
 *
 * Example usage:
 *     if ( wp_forever_is_debug() ) {
 *         wp_forever_log( 'Processing started', 'debug' );
 *     }
 *
 * @since 0.1.0
 *
 * @return bool True if WP_DEBUG is enabled, false otherwise.
 */
function wp_forever_is_debug(): bool {
	return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

/**
 * Log a message to the debug log.
 *
 * Convenience wrapper for WP_Forever_Logger::log().
 *
 * Writes log messages to WordPress debug.log with structured format.
 * Logging can be enabled via:
 * 1. Settings page (Settings → WP Forever → Logging tab)
 * 2. WP_DEBUG + WP_DEBUG_LOG constants (fallback)
 *
 * Example usage:
 *     wp_forever_log( 'User saved settings' );
 *     wp_forever_log( 'API error: ' . $error_message, 'error' );
 *
 * Log output format:
 *     [2026-01-30 12:34:56][WP Forever][INFO] Your message here
 *
 * @since 0.1.0
 * @since 0.2.0 Now uses WP_Forever_Logger class.
 *
 * @param string $message The message to log.
 * @param string $level   Log level: 'debug', 'info', 'warning', 'error'.
 * @return void
 */
function wp_forever_log( string $message, string $level = 'debug' ): void {
	// Delegate to the Logger class which handles:
	// - Checking if logging is enabled
	// - Filtering by log level
	// - Formatting the message
	WP_Forever_Logger::log( $message, $level );
}

/**
 * Check if the current request is a WordPress AJAX request.
 *
 * This is a convenience wrapper around wp_doing_ajax() for clarity.
 *
 * @since 0.1.0
 *
 * @return bool True if this is an AJAX request, false otherwise.
 */
function wp_forever_is_ajax(): bool {
	return wp_doing_ajax();
}

/**
 * Get the plugin version.
 *
 * Returns the current plugin version from the constant.
 * Useful when you need the version but don't want to use the constant directly.
 *
 * @since 0.1.0
 *
 * @return string The plugin version (e.g., '0.1.0').
 */
function wp_forever_version(): string {
	return WP_FOREVER_VERSION;
}

/*
|--------------------------------------------------------------------------
| Settings Accessor Functions (Phase 2)
|--------------------------------------------------------------------------
|
| These functions provide convenient access to plugin settings.
| They use static caching to reduce database queries.
|
*/

/**
 * Get a plugin setting with default fallback.
 *
 * This is the primary way to access plugin settings. It:
 * - Uses static caching to reduce database queries
 * - Falls back to defaults if setting doesn't exist
 * - Is used internally by all settings checks
 *
 * Example usage:
 *     $level = wp_forever_get_setting( 'log_level', 'warning' );
 *     $enabled = wp_forever_get_setting( 'logging_enabled', false );
 *
 * @since 0.2.0
 *
 * @param string $key     The setting key.
 * @param mixed  $default Default value if setting doesn't exist.
 * @return mixed The setting value, or $default if not set.
 */
function wp_forever_get_setting( string $key, mixed $default = null ): mixed {
	// Static cache to avoid repeated database queries.
	static $settings = null;

	if ( $settings === null ) {
		$settings = get_option( 'wp_forever_settings', array() );
	}

	return $settings[ $key ] ?? $default;
}

/**
 * Check if debug logging is enabled.
 *
 * Logging can be enabled via:
 * 1. Settings page (logging_enabled checkbox)
 * 2. WP_DEBUG + WP_DEBUG_LOG constants (fallback)
 *
 * @since 0.2.0
 *
 * @return bool True if logging is enabled.
 */
function wp_forever_is_logging_enabled(): bool {
	// First check Settings page.
	$settings_enabled = wp_forever_get_setting( 'logging_enabled', null );

	if ( $settings_enabled !== null ) {
		return (bool) $settings_enabled;
	}

	// Fall back to WP_DEBUG constants.
	return defined( 'WP_DEBUG' ) && WP_DEBUG
		&& defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
}

/**
 * Check if debug mode is enabled.
 *
 * Debug mode enables additional developer features.
 * Separate from logging — this is for UI/behavior changes.
 *
 * @since 0.2.0
 *
 * @return bool True if debug mode is enabled.
 */
function wp_forever_is_debug_mode(): bool {
	return (bool) wp_forever_get_setting( 'debug_mode', false );
}

/**
 * Get the minimum log level.
 *
 * Returns the configured minimum log level for filtering.
 *
 * @since 0.2.0
 *
 * @return string Log level: 'debug', 'info', 'warning', or 'error'.
 */
function wp_forever_get_log_level(): string {
	return wp_forever_get_setting( 'log_level', 'warning' );
}
