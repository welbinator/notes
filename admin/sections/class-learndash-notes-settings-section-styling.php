<?php
/**
 * LearnDash Settings Section for Learner Notes Metabox.
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Notes_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section_Styling' ) ) ) {

	/**
	 * Class LearnDash Settings Section for Learner Notes Metabox.
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Section_Styling extends LearnDash_Notes_Settings_Section {

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_notes_styling_settings';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_notes_styling';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Styling', 'learndash_student_notes' );

			parent::__construct();

		}

		/**
		 * Set the Option Key for this Class so that the defaults can be properly loaded no matter when it is requested (such as before the Settings Sections are added by LearnDash)
		 *
		 * @param   array $options  Class=>Option Key.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array           Class=>Option
		 */
		public static function set_option_keys( $options ) {

			$options[ __CLASS__ ] = 'learndash_notes_styling_settings';

			return $options;

		}

		/**
		 * Defines the default values to use for the Settings within this Section
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array  Key=>Value pairs for default values
		 */
		public static function get_default_values() {

			$defaults = apply_filters(
				'learndash_notes_styling_default_values',
				array(
					'add_new_note_floating_icon' => 'fa-window-maximize',
					'floating_icon_color'        => '#000000',
					'floating_icon_size'         => '1.2em',
					'bell_icon'                  => 'fa-bell',
					'bell_icon_color'            => '#123456',
					'notes_primary_color'        => 'lightgray',
				)
			);

			return $defaults;

		}

		/**
		 * Define your fields here, in the same way you would normally do within LearnDash_Settings_Section::load_settings_fields()
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  array  Key=>Field Args
		 */
		public static function get_settings_fields() {

			$fields = apply_filters(
				'learndash_notes_styling_settings_fields',
				array(
					'add_new_note_floating_icon' => array(
						'name'              => 'add_new_note_floating_icon',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the Font Awesome floating icon for the [llms_add_new_note] and [llms_add_new_note_popup] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'floating_icon_color'        => array(
						'name'              => 'floating_icon_color',
						'type'              => 'colorpicker',
						'label'             => esc_html__( 'Select a color for the floating Notes icon', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_hex_color',
					),
					'floating_icon_size'         => array(
						'name'              => 'floating_icon_size',
						'type'              => 'text',
						'label'             => esc_html__( 'Select a size in px, pt, or em for the floating Notes icon', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'bell_icon'                  => array(
						'name'              => 'bell_icon',
						'type'              => 'text',
						'label'             => esc_html__(
							'Change the Font Awesome icon in the [llms_full_notes_list] shortcode
					',
							'learndash_student_notes'
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'bell_icon_color'            => array(
						'name'              => 'bell_icon_color',
						'type'              => 'colorpicker',
						'label'             => esc_html__( 'Select a color for the notification icon', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_hex_color',
					),
					'notes_primary_color'        => array(
						'name'              => 'notes_primary_color',
						'type'              => 'colorpicker',
						'label'             => esc_html__( 'Select primary color for the Notes shortcode headers', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_hex_color',
					),
				)
			);

			return $fields;

		}

	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			LearnDash_Notes_Settings_Section_Styling::add_section_instance();
		}
	);

	LearnDash_Notes_Settings_Section_Styling::load_hooks();


}
