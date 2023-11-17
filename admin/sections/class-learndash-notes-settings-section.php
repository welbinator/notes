<?php
/**
 * This is an abstract class for setting up a Settings Section in LearnDash while allowing the Settings themselves to be saved in their own Options
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section' ) ) ) {

	/**
	 * Class LearnDash Settings Section for Learner Notes Metabox.
	 *
	 * @since 1.0.0
	 */
	abstract class LearnDash_Notes_Settings_Section extends LearnDash_Settings_Section {

		/**
		 * Holds the default values for each Setting in this Section
		 *
		 * @var array $defaults Setting Defaults
		 */
		public $defaults = array();

		/**
		 * Holds the PHP Class used to create this Section
		 *
		 * @var string $class PHP Class used to create this Section
		 */
		public $class;

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			$this->settings_page_id = 'learndash-notes';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = '';

			$class = get_called_class();

			$this->class = $class;

			parent::__construct();

		}

		/**
		 * Load hooks outside of the normal lifecycle of LearnDash_Settings_Section
		 * This is primarily used to force our default values per-Section globally
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public static function load_hooks() {

			$class = get_called_class();

			$fields = call_user_func( array( $class, 'get_settings_fields' ) );

			$defaults = call_user_func( array( $class, 'get_default_values' ) );

			foreach ( array_keys( $defaults ) as $key ) {

				$field = isset( $fields[ $key ] ) ? $fields[ $key ] : array( 'type' => '' );

				// If this is not a toggle field, assume a default value for any get_option() call.
				if ( ! in_array(
					$field['type'],
					array(
						'checkbox-switch',
						'checkbox',
					),
					true
				) ) {

					add_filter( "default_option_{$key}", array( $class, 'force_default_value' ), 10, 2 );
					add_filter( "option_{$key}", array( $class, 'force_default_value' ), 10, 2 );

				}
			}

			add_filter( 'learndash_notes_serialized_option_keys', array( $class, 'set_option_keys' ) );

			$option_keys = self::get_registered_serialized_option_keys();

			if ( isset( $option_keys[ $class ] ) ) {

				add_filter( "default_option_{$option_keys[ $class ]}", array( $class, 'force_default_value' ), 10, 2 );
				add_filter( "option_{$option_keys[ $class ]}", array( $class, 'force_default_value' ), 10, 2 );

			}

		}

		/**
		 * Initialize the metabox settings values.
		 *
		 * @since 1.0.0
		 */
		public function load_settings_values() {

			parent::load_settings_values();

			$class = get_called_class();

			$fields = call_user_func( array( $class, 'get_settings_fields' ) );

			foreach ( $fields as $key => $args ) {

				$args = wp_parse_args(
					$args,
					array(
						'name_wrap' => false,
					)
				);

				if ( $args['name_wrap'] ) {
					continue;
				}

				$value = get_option( $key );

				$this->setting_option_values[ $key ] = $value;

			}

		}

		/**
		 * Defines the default values to use for the Settings within this Section
		 * This should be overridden by your Class
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array  Key=>Value pairs for default values
		 */
		abstract public static function get_default_values();

		/**
		 * Define your fields here, in the same way you would normally do within LearnDash_Settings_Section::load_settings_fields()
		 * This should be overridden by your Class
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array  Key=>Field Args
		 */
		abstract public static function get_settings_fields();

		/**
		 * Provides an interface for setting Option Keys for Serialized Data that can be accessed outside of the Class' normal Lifecycle
		 *
		 * @param   array $option_keys  Class=>Option Key.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array                Class=>Option
		 */
		abstract public static function set_option_keys( $option_keys );

		/**
		 * Ensure a given get_option() call always falls back to the defined default.
		 * If a row exists and is empty, WordPress normally will return the empty value. This prevents that.
		 *
		 * @param   mixed  $value   Returned value from get_option().
		 * @param   string $option  Option name.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  mixed            Returned value for get_option()
		 */
		public static function force_default_value( $value, $option ) {

			$class = get_called_class();

			$defaults = call_user_func( array( $class, 'get_default_values' ) );

			$option_keys = self::get_registered_serialized_option_keys();

			if ( in_array( $option, $option_keys, true ) ) {

				$fields = call_user_func( array( $class, 'get_settings_fields' ) );

				foreach ( $fields as $key => $args ) {

					$name = isset( $args['name'] ) ? $args['name'] : $key;

					$args = wp_parse_args(
						$args,
						array(
							'type' => '',
						)
					);

					// Don't check for default values for toggle fields.
					if ( ! in_array(
						$args['type'],
						array(
							'checkbox-switch',
							'checkbox',
						),
						true
					) ) {
						$field_default = isset( $defaults[ $name ] ) ? $defaults[ $name ] : '';
					} else {
						$field_default = '';
					}

					$default[ $name ] = $field_default;

				}

				if ( empty( $default ) ) {
					return $value;
				}

				if ( ! empty( $value ) ) {

					$value = array_merge( $default, array_filter( $value ) );

					// Ensure that any undefined keys are properly set for a saved value.
					return $value;

				} else {

					return $default;

				}
			} else {

				if ( $value ) {
					return $value;
				}

				$fields = call_user_func( array( $class, 'get_settings_fields' ) );

				// Allow a saved 0 to be considered a "real" value.
				if ( $value !== false && $value !== '' ) {
					return $value;
				}

				$default = isset( $defaults[ $option ] ) ? $defaults[ $option ] : '';

				if ( ! $default ) {
					return $value;
				}

				return $default;

			}

			return $value;

		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 1.0.0
		 */
		public function load_settings_fields() {

			$this->setting_option_fields = array();

			$class = get_called_class();

			$this->setting_option_fields = call_user_func( array( $class, 'get_settings_fields' ) );

			$this->defaults = call_user_func( array( $class, 'get_default_values' ) );

			/** This filter is documented in sfwd-lms/includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			/**
			 * Manually registering our Fields to the WP Options API ensures that they are saved within their own Keys correctly
			 */
			foreach ( $this->setting_option_fields as $key => &$args ) {

				$name = isset( $args['name'] ) ? $args['name'] : $key;

				// Failsafe, shouldn't normally happen.
				if ( ! isset( $this->setting_option_values[ $name ] ) ) {
					continue;
				}

				$default = isset( $this->defaults[ $name ] ) ? $this->defaults[ $name ] : '';

				$args = wp_parse_args(
					$args,
					array(
						'name_wrap'         => false,
						'sanitize_callback' => 'sanitize_text_field',
						'value'             => $this->setting_option_values[ $name ],
						'placeholder'       => $default,
						'editor_args'       => array(),
					)
				);

				if ( $args['type'] === 'wpeditor' ) {

					// Ensure that WP Editor fields save properly.
					if ( ! $args['name_wrap'] ) {

						$args['editor_args']['textarea_name'] = $args['name'];

					} else {

						$args['editor_args']['textarea_name'] = "{$this->setting_option_key}[{$args['name']}]";

					}
				}

				if ( $args['name_wrap'] ) {
					continue;
				}

				// Ensure autoload is disabled and save a default value during creation of this database row.
				add_option( $name, $default, '', false );

			}

			parent::load_settings_fields();

		}

		/**
		 * Sets up the Settings Page and registers the Settings within it.
		 *
		 * This is overrided to register the Settings to a custom "Page" based on both the Page and Section ID to ensure the WP Settings API doesn't clear out saved Settings under a different Section while saving changes.
		 *
		 * @param   string $settings_page_id  Current Settings Page ID, defined by the Class.
		 *
		 * @access  public
		 * @since   1.0.1
		 * @return  void
		 */
		public function settings_page_init( $settings_page_id = '' ) {

			// Ensure settings_page_id is not empty and that it matches the page_id we want to display this section on.
			if ( empty( $settings_page_id ) || $settings_page_id !== $this->settings_page_id || empty( $this->setting_option_fields ) ) {
				return;
			}

			add_settings_section(
				$this->settings_section_key,
				$this->settings_section_label,
				array( $this, 'show_settings_section_description' ),
				"{$this->settings_page_id}-{$this->settings_section_key}"
			);

			foreach ( $this->setting_option_fields as $setting_option_field ) {

				if ( ! isset( $setting_option_field['name'] ) ) {
					continue;
				}

				add_settings_field(
					$setting_option_field['name'],
					$setting_option_field['label'],
					$setting_option_field['display_callback'],
					"{$this->settings_page_id}-{$this->settings_section_key}",
					$this->settings_section_key,
					$setting_option_field
				);

				register_setting(
					"{$this->settings_page_id}-{$this->settings_section_key}",
					$setting_option_field['name'],
					array(
						'sanitize_callback' => $setting_option_field['sanitize_callback'],
					)
				);

			}

			register_setting(
				"{$this->settings_page_id}-{$this->settings_section_key}",
				$this->setting_option_key,
				array(
					'sanitize_callback' => array( $this, 'settings_section_fields_validate' ),
				)
			);

		}

		/**
		 * Show Settings Section Fields.
		 *
		 * @param string $page Page shown.
		 * @param string $section Section shown.
		 *
		 * @access  public
		 * @since   1.0.1
		 * @return  void
		 */
		public function show_settings_section_fields( $page, $section ) {

			global $wp_settings_fields;

			if ( ! isset( $wp_settings_fields[ "{$page}-{$section}" ][ $section ] ) ) {
				return;
			}

			// Pass through our custom Option Page key.
			settings_fields( "{$page}-{$section}" );

			// Ouput fields for our custom Option Page key.
			LearnDash_Settings_Fields::show_section_fields( $wp_settings_fields[ "{$page}-{$section}" ][ $section ] );
		}

		/**
		 * Method to retrieve a list of serialized option keys that have been registered
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array  Class=>Option Key
		 */
		public static function get_registered_serialized_option_keys() {

			$option_keys = apply_filters( 'learndash_notes_serialized_option_keys', array() );

			return $option_keys;

		}
	}

}
