<?php
/**
 * Debug logger.
 *
 * Writes log messages to WordPress debug.log with structured format.
 * Supports multiple log levels and can be configured via Settings page.
 *
 * The logger can be enabled/disabled in two ways:
 * 1. Via the Settings page (Settings → WP Forever → Logging tab)
 * 2. Via WP_DEBUG + WP_DEBUG_LOG constants (fallback if Settings not configured)
 *
 * Log levels in order of verbosity:
 * - debug:   Detailed info for developers (most verbose)
 * - info:    General informational messages
 * - warning: Potential issues that may need attention
 * - error:   Errors requiring immediate action (least verbose)
 *
 * Usage:
 *     // Via the class directly:
 *     WP_Forever_Logger::log( 'Something happened', 'info' );
 *
 *     // Or via helper function:
 *     wp_forever_log( 'Something happened', 'info' );
 *
 * Log output format:
 *     [2026-01-30 12:34:56][WP Forever][INFO] Something happened
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
 * Debug logger class.
 *
 * A static class that handles all plugin logging. Uses static methods
 * so logging can be called from anywhere without instantiation.
 *
 * Why static?
 * - Logging is a utility function, not an object with state
 * - Calling WP_Forever_Logger::log() is cleaner than creating instances
 * - No need for dependency injection for a simple logging utility
 *
 * @since 0.2.0
 */
class WP_Forever_Logger {

	/**
	 * Log levels in order of severity.
	 *
	 * Lower index = more verbose (debug shows everything).
	 * Higher index = more severe (error shows only errors).
	 *
	 * The numeric values are used for comparison:
	 * If minimum level is 'warning' (2), then 'debug' (0) and 'info' (1) are filtered out.
	 *
	 * @since 0.2.0
	 * @var array<string, int>
	 */
	private const LEVELS = array(
		'debug'   => 0,
		'info'    => 1,
		'warning' => 2,
		'error'   => 3,
	);

	/**
	 * Log a message.
	 *
	 * This is the main entry point for logging. It:
	 * 1. Validates the log level
	 * 2. Checks if logging is enabled
	 * 3. Checks if the level meets the minimum threshold
	 * 4. Formats and writes the log entry
	 *
	 * @since 0.2.0
	 *
	 * @param string $message The message to log.
	 * @param string $level   Log level: 'debug', 'info', 'warning', 'error'.
	 * @return void
	 */
	public static function log( string $message, string $level = 'debug' ): void {
		// Validate level — fall back to 'debug' if invalid.
		// This prevents errors from typos like 'warn' instead of 'warning'.
		if ( ! isset( self::LEVELS[ $level ] ) ) {
			$level = 'debug';
		}

		// Check if logging is enabled (via Settings or WP_DEBUG).
		// This is the first gate — if logging is off, we exit early.
		if ( ! self::is_logging_enabled() ) {
			return;
		}

		// Check if this level meets the minimum threshold.
		// For example, if min is 'warning', 'debug' and 'info' are filtered out.
		if ( ! self::should_log_level( $level ) ) {
			return;
		}

		// Format the message with timestamp, prefix, and level.
		$formatted = self::format_message( $message, $level );

		// Write to the debug log.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging.
		error_log( $formatted );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * Logging is enabled if:
	 * 1. Settings page has logging_enabled = true, OR
	 * 2. WP_DEBUG and WP_DEBUG_LOG are both true (fallback if Settings not configured)
	 *
	 * The Settings page takes precedence. If logging_enabled is explicitly
	 * set to false, logging is disabled even if WP_DEBUG is true.
	 *
	 * @since 0.2.0
	 *
	 * @return bool True if logging is enabled.
	 */
	private static function is_logging_enabled(): bool {
		// Check Settings page first.
		// We pass null as default to detect "not set" vs "set to false".
		$settings_enabled = wp_forever_get_setting( 'logging_enabled', null );

		// If Settings has an explicit value, use it.
		if ( $settings_enabled !== null ) {
			return (bool) $settings_enabled;
		}

		// Fall back to WP_DEBUG constants.
		// Both WP_DEBUG and WP_DEBUG_LOG must be true for logging to work.
		return defined( 'WP_DEBUG' ) && WP_DEBUG
			&& defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
	}

	/**
	 * Check if a log level should be logged based on minimum threshold.
	 *
	 * Compares the requested level against the configured minimum.
	 * Only logs if the requested level is >= the minimum.
	 *
	 * Example:
	 * - Min level is 'warning' (value 2)
	 * - 'error' (3) >= 2, so it's logged
	 * - 'debug' (0) < 2, so it's filtered out
	 *
	 * @since 0.2.0
	 *
	 * @param string $level The log level to check.
	 * @return bool True if the level should be logged.
	 */
	private static function should_log_level( string $level ): bool {
		// Get the configured minimum level (default: warning).
		$min_level = wp_forever_get_setting( 'log_level', 'warning' );

		// Get numeric values for comparison.
		// Use 0 as fallback for the requested level (treat unknown as debug).
		// Use 2 as fallback for min level (treat unknown as warning).
		$level_value = self::LEVELS[ $level ] ?? 0;
		$min_value   = self::LEVELS[ $min_level ] ?? 2;

		// Log if requested level >= minimum level.
		return $level_value >= $min_value;
	}

	/**
	 * Format a log message.
	 *
	 * Creates a structured log entry with:
	 * - Timestamp in local time (WordPress timezone)
	 * - Plugin prefix for easy grep/filtering
	 * - Log level in uppercase
	 * - The actual message
	 *
	 * Format: [2026-01-30 12:34:56][WP Forever][WARNING] Message here
	 *
	 * @since 0.2.0
	 *
	 * @param string $message The message to format.
	 * @param string $level   The log level.
	 * @return string The formatted log entry.
	 */
	private static function format_message( string $message, string $level ): string {
		// Get timestamp using WordPress timezone (set in Settings → General).
		// This makes logs easier to correlate with user actions.
		$timestamp = current_time( 'Y-m-d H:i:s' );

		// Uppercase the level for visibility in log files.
		$level_upper = strtoupper( $level );

		// Build the formatted message.
		return sprintf(
			'[%s][WP Forever][%s] %s',
			$timestamp,
			$level_upper,
			$message
		);
	}

	/**
	 * Get available log levels.
	 *
	 * Returns all valid log levels and their numeric values.
	 * Useful for validation or building select dropdowns.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, int> Array of level => value pairs.
	 */
	public static function get_levels(): array {
		return self::LEVELS;
	}
}
