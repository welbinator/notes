<?php
/**
 * LearnDash Settings Section for the Learner Notes Features Metabox
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section_Features' ) ) ) {

	/**
	 * Class LearnDash Settings Section for the Learner Notes Features Metabox
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Section_Features extends LearnDash_Notes_Settings_Section {

		/**
		 * Holds the default values for each Setting in this Section
		 *
		 * @var array $defaults Setting Defaults
		 */
		public $defaults = array();

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_notes_features';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_notes_features';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Features', 'learndash_student_notes' );

			$this->defaults = self::get_default_values();

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

			$options[ __CLASS__ ] = 'learndash_notes_features';

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
				'learndash_notes_features_default_values',
				array(
					'ld_student_notes_add_editor_toolbar' => '',
					'ld_student_notes_show_editor_character_count' => '',
					'ld_student_notes_add_notify_instructor' => '',
					'ld_student_notes_auto_include_add_note' => '',
					'ld_student_notes_auto_include_add_note_popup' => '',
					'ld_student_notes_auto_include_notes_list' => '',
					'ld_student_notes_auto_include_all_notes' => '',
					'ld_student_notes_show_private_notes' => '',
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
				'learndash_notes_email_setting_fields',
				array(
					'ld_student_notes_add_editor_toolbar' => array(
						'name'              => 'ld_student_notes_add_editor_toolbar',
						'type'              => 'checkbox-switch',
						'label'             => esc_html__( 'Add editor toolbar', 'learndash_student_notes' ),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_show_editor_character_count' => array(
						'name'              => 'ld_student_notes_show_editor_character_count',
						'type'              => 'checkbox-switch',
						'label'             => esc_html__( 'Show character count in the rich text area', 'learndash_student_notes' ),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_add_notify_instructor' => array(
						'name'              => 'ld_student_notes_add_notify_instructor',
						'type'              => 'checkbox-switch',
						'label'             => esc_html__( 'Add option to notify the Instructor', 'learndash_student_notes' ),
						'label_description' => esc_html__( 'Ensure that you first configure your email templates under the "Email" tab above before enabling this feature.', 'learndash_student_notes' ),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_auto_include_add_note' => array(
						'name'              => 'ld_student_notes_auto_include_add_note',
						'type'              => 'checkbox-switch',
						'label'             => sprintf(
							// translators: %s is the Custom Label for Course.
							esc_html__( 'Automatically include the [llms_add_new_note] shortcode when viewing %s content', 'learndash_student_notes' ),
							learndash_get_custom_label( 'course' )
						),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_auto_include_add_note_popup' => array(
						'name'              => 'ld_student_notes_auto_include_add_note_popup',
						'type'              => 'checkbox-switch',
						'label'             => sprintf(
							// translators: %s is the Custom Label for Course.
							esc_html__( 'Automatically include the [llms_add_new_note_popup] shortcode when viewing %s content', 'learndash_student_notes' ),
							learndash_get_custom_label( 'course' )
						),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_auto_include_notes_list' => array(
						'name'              => 'ld_student_notes_auto_include_notes_list',
						'type'              => 'checkbox-switch',
						'label'             => sprintf(
							// translators: %s is the Custom Label for Course.
							esc_html__( 'Automatically include the [llms_notes_list] shortcode when viewing %s content', 'learndash_student_notes' ),
							learndash_get_custom_label( 'course' )
						),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_auto_include_all_notes' => array(
						'name'              => 'ld_student_notes_auto_include_all_notes',
						'type'              => 'checkbox-switch',
						'label'             => sprintf(
							// translators: %s is the Custom Label for Course.
							esc_html__( 'Automatically include the [llms_full_notes_list] shortcode when viewing %s content', 'learndash_student_notes' ),
							learndash_get_custom_label( 'course' )
						),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'ld_student_notes_show_private_notes' => array(
						'name'              => 'ld_student_notes_show_private_notes',
						'type'              => 'checkbox-switch',
						'label'             => esc_html__( 'Allow Instructors the ability to view Notes that were not sent to them', 'learndash_student_notes' ),
						'options'           => array(
							'on' => 1,
						),
						'sanitize_callback' => 'sanitize_text_field',
					),
				)
			);

			return $fields;
		}

	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			LearnDash_Notes_Settings_Section_Features::add_section_instance();
		}
	);

	LearnDash_Notes_Settings_Section_Features::load_hooks();

}
