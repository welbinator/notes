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

if ( ( class_exists( 'LearnDash_Notes_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section_Labels' ) ) ) {

	/**
	 * Class LearnDash Settings Section for Learner Notes Metabox.
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Section_Labels extends LearnDash_Notes_Settings_Section {

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_notes_labels_settings';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_notes_labels';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'Labels', 'learndash_student_notes' );

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

			$options[ __CLASS__ ] = 'learndash_notes_labels_settings';

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
				'learndash_notes_labels_default_values',
				array(
					'new_note_title'       => esc_html__( 'Type note title', 'learndash_student_notes' ),
					'sumofchars'           => esc_html__( 'Sum of Characters', 'learndash_student_notes' ),
					'notifinst'            => esc_html__( 'Notify Instructor', 'learndash_student_notes' ),
					'instwasnotif'         => esc_html__( 'Instructor was notified', 'learndash_student_notes' ),
					'noteaddedsucc'        => esc_html__( 'Note Added Successfully', 'learndash_student_notes' ),
					'nonotesadded'         => esc_html__( 'No Notes Added', 'learndash_student_notes' ),
					'addedtext'            => esc_html__( 'Added', 'learndash_student_notes' ),
					'notessubmitbutton'    => esc_html__( 'Submit', 'learndash_student_notes' ),
					'myhistnotes'          => esc_html__( 'My Historical Notes', 'learndash_student_notes' ),
					'allmynotes'           => esc_html__( 'All My Notes', 'learndash_student_notes' ),
					'nonotesaddedanywhere' => esc_html__( 'No Notes Added Anywhere', 'learndash_student_notes' ),
					'deletetext'           => esc_html__( 'Delete', 'learndash_student_notes' ),
					'notedelsucc'          => esc_html__( 'Note Deleted', 'learndash_student_notes' ),
					'delwarning'           => esc_html__( 'Are you sure? This can not be undone.', 'learndash_student_notes' ),
					'downloadtext'         => esc_html__( 'Download', 'learndash_student_notes' ),
					'relatedtext'          => esc_html__( 'Related', 'learndash_student_notes' ),
					'opentext'             => esc_html__( 'Open', 'learndash_student_notes' ),
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
				'learndash_notes_labels_settings_fields',
				array(
					'new_note_title'       => array(
						'name'              => 'new_note_title',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the placeholder text in the Note title area in the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'sumofchars'           => array(
						'name'              => 'sumofchars',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Sum of Characters" text located below the rich text area in the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'notifinst'            => array(
						'name'              => 'notifinst',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Nofiy Instructor" text located below the new note editor in the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'instwasnotif'         => array(
						'name'              => 'instwasnotif',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Instructor was notified" message', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'noteaddedsucc'        => array(
						'name'              => 'noteaddedsucc',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Note Added Successfully" message text for the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'nonotesadded'         => array(
						'name'              => 'nonotesadded',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "No Notes Added" message text for the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'addedtext'            => array(
						'name'              => 'addedtext',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Added" text for the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'notessubmitbutton'    => array(
						'name'              => 'notessubmitbutton',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Save Note" button text for the [llms_add_new_note] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'myhistnotes'          => array(
						'name'              => 'myhistnotes',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "My Historical Notes" text header for the [llms_notes_list] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'allmynotes'           => array(
						'name'              => 'allmynotes',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "All My Notes" text header for the [llms_full_notes_list] shortcode', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'nonotesaddedanywhere' => array(
						'name'              => 'nonotesaddedanywhere',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "No Notes Added Anywhere" message text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'deletetext'           => array(
						'name'              => 'deletetext',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Delete" link text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'notedelsucc'          => array(
						'name'              => 'notedelsucc',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Note Deleted" message text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'delwarning'           => array(
						'name'              => 'delwarning',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Are you sure? This can not be undone." message text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'downloadtext'         => array(
						'name'              => 'downloadtext',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Download" link text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'relatedtext'          => array(
						'name'              => 'relatedtext',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Related" text for the [llms_notes_list] and the [llms_full_notes_list] shortcodes', 'learndash_student_notes' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
					'opentext'             => array(
						'name'              => 'opentext',
						'type'              => 'text',
						'label'             => esc_html__( 'Change the "Open" text for the [llms_full_notes_list] shortcode', 'learndash_student_notes' ),
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
			LearnDash_Notes_Settings_Section_Labels::add_section_instance();
		}
	);

	LearnDash_Notes_Settings_Section_Labels::load_hooks();


}
