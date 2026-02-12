<?php
/**
 * Settings page handler.
 *
 * Manages the plugin's settings page using WordPress Settings API.
 * This class handles:
 * - Registering the settings page under Settings menu
 * - Registering settings with WordPress
 * - Rendering the tabbed settings interface
 * - Validating and sanitizing user input
 *
 * The Settings API pattern used here:
 * 1. register_setting() - tells WP about our option
 * 2. add_settings_section() - groups related fields
 * 3. add_settings_field() - individual form fields
 * 4. settings_fields() + do_settings_sections() - render in template
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
 * Settings page class.
 *
 * Handles all settings page functionality including registration,
 * rendering, and validation.
 *
 * @since 0.2.0
 */
class WP_Forever_Settings {

	/**
	 * Option name in the database.
	 *
	 * All plugin settings are stored in this single option as an array.
	 * This reduces database queries compared to multiple options.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'wp_forever_settings';

	/**
	 * Option group for Settings API.
	 *
	 * Used in register_setting() and settings_fields().
	 *
	 * @var string
	 */
	private const OPTION_GROUP = 'wp_forever_settings_group';

	/**
	 * Settings page slug.
	 *
	 * Used in add_options_page() and URL parameter.
	 *
	 * @var string
	 */
	private const PAGE_SLUG = 'wp-forever';

	/**
	 * Available tabs.
	 *
	 * Each tab corresponds to a settings section and a partial template.
	 *
	 * @var array<string, string>
	 */
	private array $tabs = array(
		'general'  => 'General',
		'logging'  => 'Logging',
		'notices'  => 'Notices',
		'advanced' => 'Advanced',
	);

	/**
	 * Initialize the settings page.
	 *
	 * Called from the main plugin class to set up hooks.
	 *
	 * @since 0.2.0
	 */
	public function init(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get default settings values.
	 *
	 * These defaults are used when:
	 * - Plugin is first activated (no settings exist yet)
	 * - A specific setting key doesn't exist in stored options
	 * - Settings are reset to defaults
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, mixed> Default settings array.
	 */
	public function get_defaults(): array {
		return array(
			// General tab.
			'delete_data_on_uninstall' => false,

			// Logging tab.
			'logging_enabled'          => false,
			'log_level'                => 'warning',

			// Notices tab.
			'show_welcome_notice'      => true,

			// Advanced tab.
			'debug_mode'               => false,
			'rest_api_enabled'         => true,
			'enable_woo_features'      => false,
		);
	}

	/**
	 * Register the settings page under Settings menu.
	 *
	 * Uses add_options_page() to place our settings under Settings → WP Forever.
	 * This is the standard location for plugin settings and keeps the admin
	 * menu clean (no top-level menu for a simple plugin).
	 *
	 * Hooked to: admin_menu
	 *
	 * @since 0.2.0
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'WP Forever Settings', 'wp-forever' ), // Page title (browser tab).
			__( 'WP Forever', 'wp-forever' ),          // Menu title.
			'manage_options',                           // Capability required.
			self::PAGE_SLUG,                            // Menu slug.
			array( $this, 'render_settings_page' )      // Callback to render page.
		);
	}

	/**
	 * Register settings with WordPress Settings API.
	 *
	 * This tells WordPress about our settings option and sets up
	 * the sanitization callback. We use a single option array
	 * (wp_forever_settings) rather than multiple options.
	 *
	 * Hooked to: admin_init
	 *
	 * @since 0.2.0
	 */
	public function register_settings(): void {
		// Register the main settings option.
		register_setting(
			self::OPTION_GROUP,    // Option group (used in settings_fields()).
			self::OPTION_NAME,     // Option name in database.
			array(
				'type'              => 'array',
				'description'       => __( 'WP Forever plugin settings', 'wp-forever' ),
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_defaults(),
			)
		);

		// Register sections for each tab.
		$this->register_general_section();
		$this->register_logging_section();
		$this->register_notices_section();
		$this->register_advanced_section();
	}

	/**
	 * Register General settings section and fields.
	 *
	 * @since 0.2.0
	 */
	private function register_general_section(): void {
		add_settings_section(
			'wp_forever_general_section',
			__( 'General Settings', 'wp-forever' ),
			array( $this, 'render_general_section_description' ),
			self::PAGE_SLUG . '_general'
		);

		add_settings_field(
			'delete_data_on_uninstall',
			__( 'Delete Data on Uninstall', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_general',
			'wp_forever_general_section',
			array(
				'id'          => 'delete_data_on_uninstall',
				'description' => __( 'Remove all plugin data when the plugin is deleted.', 'wp-forever' ),
				'default'     => false,
			)
		);
	}

	/**
	 * Register Logging settings section and fields.
	 *
	 * @since 0.2.0
	 */
	private function register_logging_section(): void {
		add_settings_section(
			'wp_forever_logging_section',
			__( 'Logging Settings', 'wp-forever' ),
			array( $this, 'render_logging_section_description' ),
			self::PAGE_SLUG . '_logging'
		);

		add_settings_field(
			'logging_enabled',
			__( 'Enable Debug Logging', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_logging',
			'wp_forever_logging_section',
			array(
				'id'          => 'logging_enabled',
				'description' => __( 'Write debug messages to wp-content/debug.log.', 'wp-forever' ),
				'default'     => false,
			)
		);

		add_settings_field(
			'log_level',
			__( 'Minimum Log Level', 'wp-forever' ),
			array( $this, 'render_select_field' ),
			self::PAGE_SLUG . '_logging',
			'wp_forever_logging_section',
			array(
				'id'          => 'log_level',
				'description' => __( 'Only log messages at this level or higher.', 'wp-forever' ),
				'default'     => 'warning',
				'options'     => array(
					'debug'   => __( 'Debug (most verbose)', 'wp-forever' ),
					'info'    => __( 'Info', 'wp-forever' ),
					'warning' => __( 'Warning', 'wp-forever' ),
					'error'   => __( 'Error (least verbose)', 'wp-forever' ),
				),
			)
		);
	}

	/**
	 * Register Notices settings section and fields.
	 *
	 * @since 0.2.0
	 */
	private function register_notices_section(): void {
		add_settings_section(
			'wp_forever_notices_section',
			__( 'Notice Settings', 'wp-forever' ),
			array( $this, 'render_notices_section_description' ),
			self::PAGE_SLUG . '_notices'
		);

		add_settings_field(
			'show_welcome_notice',
			__( 'Show Welcome Notice', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_notices',
			'wp_forever_notices_section',
			array(
				'id'          => 'show_welcome_notice',
				'description' => __( 'Display a welcome message after plugin activation.', 'wp-forever' ),
				'default'     => true,
			)
		);
	}

	/**
	 * Register Advanced settings section and fields.
	 *
	 * @since 0.2.0
	 */
	private function register_advanced_section(): void {
		add_settings_section(
			'wp_forever_advanced_section',
			__( 'Advanced Settings', 'wp-forever' ),
			array( $this, 'render_advanced_section_description' ),
			self::PAGE_SLUG . '_advanced'
		);

		add_settings_field(
			'debug_mode',
			__( 'Debug Mode', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_advanced',
			'wp_forever_advanced_section',
			array(
				'id'          => 'debug_mode',
				'description' => __( 'Enable additional debugging features for development.', 'wp-forever' ),
				'default'     => false,
			)
		);

		add_settings_field(
			'rest_api_enabled',
			__( 'Enable REST API', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_advanced',
			'wp_forever_advanced_section',
			array(
				'id'          => 'rest_api_enabled',
				'description' => __( 'Register WP Forever REST routes (namespace: wp-forever/v1).', 'wp-forever' ),
				'default'     => true,
			)
		);

		add_settings_field(
			'enable_woo_features',
			__( 'Enable WooCommerce Features', 'wp-forever' ),
			array( $this, 'render_checkbox_field' ),
			self::PAGE_SLUG . '_advanced',
			'wp_forever_advanced_section',
			array(
				'id'          => 'enable_woo_features',
				'description' => __( 'Turn on WooCommerce-specific hooks (requires WooCommerce).', 'wp-forever' ),
				'default'     => false,
			)
		);
	}

	/**
	 * Render the settings page.
	 *
	 * This is the main callback for the settings page. It:
	 * 1. Checks user capabilities
	 * 2. Renders the page header and tab navigation
	 * 3. Includes the appropriate tab content
	 *
	 * @since 0.2.0
	 */
	public function render_settings_page(): void {
		// Verify user has permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access this page.', 'wp-forever' ),
				esc_html__( 'Access Denied', 'wp-forever' ),
				array( 'response' => 403 )
			);
		}

		// Set up variables for the template.
		// These are used by settings-page.php to render tabs and content.
		$current_tab = $this->get_current_tab();
		$tabs        = $this->tabs;

		// Include the settings page template.
		// Variables $current_tab and $tabs are available in the template scope.
		include WP_FOREVER_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	/**
	 * Get the current active tab.
	 *
	 * Reads from URL parameter with fallback to 'general'.
	 * Validates against allowed tabs to prevent injection.
	 *
	 * @since 0.2.0
	 *
	 * @return string Current tab slug.
	 */
	public function get_current_tab(): string {
		$allowed_tabs = array_keys( $this->tabs );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation, no data modification.
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		return in_array( $current_tab, $allowed_tabs, true ) ? $current_tab : 'general';
	}

	/**
	 * Get URL for a specific tab.
	 *
	 * @since 0.2.0
	 *
	 * @param string $tab Tab slug.
	 * @return string Tab URL.
	 */
	public function get_tab_url( string $tab ): string {
		return add_query_arg(
			array(
				'page' => self::PAGE_SLUG,
				'tab'  => $tab,
			),
			admin_url( 'options-general.php' )
		);
	}

	/**
	 * Get all tabs.
	 *
	 * @since 0.2.0
	 *
	 * @return array<string, string> Array of tab slug => label.
	 */
	public function get_tabs(): array {
		return $this->tabs;
	}

	/**
	 * Get the page slug.
	 *
	 * @since 0.2.0
	 *
	 * @return string Page slug.
	 */
	public function get_page_slug(): string {
		return self::PAGE_SLUG;
	}

	/**
	 * Get the option group.
	 *
	 * @since 0.2.0
	 *
	 * @return string Option group name.
	 */
	public function get_option_group(): string {
		return self::OPTION_GROUP;
	}

	/**
	 * Sanitize all settings before saving.
	 *
	 * This callback receives the entire settings array and must return
	 * a sanitized version. Each field is validated individually based
	 * on its expected type.
	 *
	 * @since 0.2.0
	 *
	 * @param array $input Raw input from form submission.
	 * @return array Sanitized settings array.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = array();
		$defaults  = $this->get_defaults();

		// Delete data on uninstall (checkbox → boolean).
		$sanitized['delete_data_on_uninstall'] = ! empty( $input['delete_data_on_uninstall'] );

		// Logging enabled (checkbox → boolean).
		$sanitized['logging_enabled'] = ! empty( $input['logging_enabled'] );

		// Log level (select → validate against allowed values).
		$allowed_levels            = array( 'debug', 'info', 'warning', 'error' );
		$sanitized['log_level']    = isset( $input['log_level'] ) && in_array( $input['log_level'], $allowed_levels, true )
			? $input['log_level']
			: $defaults['log_level'];

		// Show welcome notice (checkbox → boolean).
		$sanitized['show_welcome_notice'] = ! empty( $input['show_welcome_notice'] );

		// Advanced settings (checkbox → boolean).
		$sanitized['debug_mode']          = ! empty( $input['debug_mode'] );
		$sanitized['rest_api_enabled']    = ! empty( $input['rest_api_enabled'] );
		$sanitized['enable_woo_features'] = ! empty( $input['enable_woo_features'] );

		// Log that settings were saved (useful for testing logging).
		if ( class_exists( 'WP_Forever_Logger' ) ) {
			WP_Forever_Logger::log(
				sprintf( 'Settings updated by user %d', get_current_user_id() ),
				'info'
			);
		}

		// Add success message.
		add_settings_error(
			self::OPTION_NAME,
			'settings_updated',
			__( 'Settings saved.', 'wp-forever' ),
			'success'
		);

		return $sanitized;
	}

	/**
	 * Render a checkbox field.
	 *
	 * @since 0.2.0
	 *
	 * @param array $args Field arguments including 'id', 'description', 'default'.
	 */
	public function render_checkbox_field( array $args ): void {
		$options = get_option( self::OPTION_NAME, array() );
		$value   = $options[ $args['id'] ] ?? $args['default'];
		$checked = checked( $value, true, false );

		printf(
			'<label><input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s /> %4$s</label>',
			esc_attr( $args['id'] ),
			esc_attr( self::OPTION_NAME ),
			$checked,
			isset( $args['label'] ) ? esc_html( $args['label'] ) : ''
		);

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render a select dropdown field.
	 *
	 * @since 0.2.0
	 *
	 * @param array $args Field arguments including 'id', 'options', 'description', 'default'.
	 */
	public function render_select_field( array $args ): void {
		$options       = get_option( self::OPTION_NAME, array() );
		$current_value = $options[ $args['id'] ] ?? $args['default'];

		printf(
			'<select id="%1$s" name="%2$s[%1$s]">',
			esc_attr( $args['id'] ),
			esc_attr( self::OPTION_NAME )
		);

		foreach ( $args['options'] as $value => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $value ),
				selected( $current_value, $value, false ),
				esc_html( $label )
			);
		}

		echo '</select>';

		if ( ! empty( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render General section description.
	 *
	 * @since 0.2.0
	 */
	public function render_general_section_description(): void {
		echo '<p>' . esc_html__( 'Configure general plugin behavior.', 'wp-forever' ) . '</p>';
	}

	/**
	 * Render Logging section description.
	 *
	 * @since 0.2.0
	 */
	public function render_logging_section_description(): void {
		echo '<p>' . esc_html__( 'Configure debug logging. Logs are written to wp-content/debug.log when WP_DEBUG_LOG is enabled.', 'wp-forever' ) . '</p>';
	}

	/**
	 * Render Notices section description.
	 *
	 * @since 0.2.0
	 */
	public function render_notices_section_description(): void {
		echo '<p>' . esc_html__( 'Configure admin notice behavior.', 'wp-forever' ) . '</p>';
	}

	/**
	 * Render Advanced section description.
	 *
	 * @since 0.2.0
	 */
	public function render_advanced_section_description(): void {
		echo '<p>' . esc_html__( 'Advanced settings for developers. Use with caution.', 'wp-forever' ) . '</p>';
	}
}
