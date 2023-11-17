<?php
/**
 * LearnDash Settings Section for the Learner Notes Email Metabox
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section_Email' ) ) ) {

	/**
	 * Class LearnDash Settings Section for the Learner Notes Email Metabox
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Section_Email extends LearnDash_Notes_Settings_Section {

		/**
		 * Holds the default values for each Setting in this Section
		 *
		 * @var array $defaults Setting Defaults
		 */
		public $defaults = array();

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

			$options[ __CLASS__ ] = 'llms_s_n_email_template';

			return $options;

		}

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'llms_s_n_email_template';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_notes_email';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = 'llms_s_n_email_template';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Email', 'learndash_student_notes' );

			$this->defaults = self::get_default_values();

			parent::__construct();

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
				'learndash_notes_email_default_values',
				array(
					'instructor_subject' => '',
					'instructor_body'    => '',
					'student_subject'    => '',
					'student_body'       => '',
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
					'instructor_subject' => array(
						'name'              => 'instructor_subject',
						'name_wrap'         => true,
						'type'              => 'text',
						'label'             => esc_html__( 'Instructor Email Subject', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'instructor_body'    => array(
						'name'              => 'instructor_body',
						'name_wrap'         => true,
						'type'              => 'wpeditor',
						'label'             => esc_html__( 'Instructor Email Body', 'learndash_student_notes' ),
						'editor_args'       => array(
							'media_buttons' => false,
						),
						'label_description' => '' .
						esc_html__( 'Available merge codes:', 'learndash_student_notes' ) .
						'<ul>' .
						implode(
							'',
							array_map(
								function( $merge_code ) {
									return '<li><code>{' . $merge_code . '}</code></li>';
								},
								array(
									'instructor_first_name',
									'instructor_last_name',
									'instructor_user_name',
									'instructor_post_link',
									'student_first_name',
									'student_last_name',
									'student_user_name',
								)
							)
						) .
						'</ul>',
						'sanitize_callback' => 'wp_kses_post',
					),
					'student_subject'    => array(
						'name'              => 'student_subject',
						'name_wrap'         => true,
						'type'              => 'text',
						'label'             => esc_html__( 'Learner Email Subject', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'student_body'       => array(
						'name'              => 'student_body',
						'name_wrap'         => true,
						'type'              => 'wpeditor',
						'label'             => esc_html__( 'Learner Email Body', 'learndash_student_notes' ),
						'editor_args'       => array(
							'media_buttons' => false,
						),
						'label_description' => '' .
						esc_html__( 'Available merge codes:', 'learndash_student_notes' ) .
						'<ul>' .
						implode(
							'',
							array_map(
								function( $merge_code ) {
									return '<li><code>{' . $merge_code . '}</code></li>';
								},
								array(
									'student_first_name',
									'student_last_name',
									'student_user_name',
									'student_post_link',
									'instructor_first_name',
									'instructor_last_name',
									'instructor_user_name',
								)
							)
						) .
						'</ul>',
						'sanitize_callback' => 'wp_kses_post',
					),
				)
			);

			return $fields;

		}

	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			LearnDash_Notes_Settings_Section_Email::add_section_instance();
		}
	);

	LearnDash_Notes_Settings_Section_Email::load_hooks();

}
