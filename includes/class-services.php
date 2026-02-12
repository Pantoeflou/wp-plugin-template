<?php
/**
 * Services registry.
 *
 * WordPress plugins often end up with a lot of "new ClassName()" scattered
 * across files. That works, but it becomes hard to:
 * - share single instances (DB, template loader, integrations)
 * - keep the bootstrap (core/class-plugin.php) readable
 * - swap implementations later (e.g. different logger)
 *
 * This file provides a *tiny*, WordPress-native alternative to a full DI
 * container: a registry of lazily-created services.
 *
 * It is intentionally simple:
 * - no reflection
 * - no configuration language
 * - no autowiring
 *
 * If you later choose to adopt a DI container, this becomes a clean seam:
 * you replace the internals while keeping call-sites stable.
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
 * Lightweight service registry.
 *
 * @since 0.2.0
 */
final class WP_Forever_Services {

	/**
	 * Cached service instances.
	 *
	 * @since 0.2.0
	 * @var array<string,object>
	 */
	private static array $instances = array();

	/**
	 * Get the DB service.
	 *
	 * @since 0.2.0
	 * @return WP_Forever_DB
	 */
	public static function db(): WP_Forever_DB {
		if ( ! isset( self::$instances['db'] ) ) {
			self::$instances['db'] = new WP_Forever_DB();
		}
		return self::$instances['db'];
	}

	/**
	 * Get the template loader.
	 *
	 * @since 0.2.0
	 * @return WP_Forever_Template_Loader
	 */
	public static function templates(): WP_Forever_Template_Loader {
		if ( ! isset( self::$instances['templates'] ) ) {
			self::$instances['templates'] = new WP_Forever_Template_Loader();
		}
		return self::$instances['templates'];
	}

	/**
	 * Get the REST API router.
	 *
	 * @since 0.2.0
	 * @return WP_Forever_REST
	 */
	public static function rest(): WP_Forever_REST {
		if ( ! isset( self::$instances['rest'] ) ) {
			self::$instances['rest'] = new WP_Forever_REST();
		}
		return self::$instances['rest'];
	}

	/**
	 * Get the WooCommerce integration.
	 *
	 * Note: This does not *require* WooCommerce to be installed. The
	 * integration class is responsible for checking availability.
	 *
	 * @since 0.2.0
	 * @return WP_Forever_WooCommerce
	 */
	public static function woocommerce(): WP_Forever_WooCommerce {
		if ( ! isset( self::$instances['woocommerce'] ) ) {
			self::$instances['woocommerce'] = new WP_Forever_WooCommerce();
		}
		return self::$instances['woocommerce'];
	}

	/**
	 * Reset all services.
	 *
	 * Useful for unit tests (or debugging) where you want a fresh instance.
	 * Avoid using this in normal runtime.
	 *
	 * @since 0.2.0
	 * @return void
	 */
	public static function reset(): void {
		self::$instances = array();
	}
}
