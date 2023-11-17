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

if ( ( class_exists( 'LearnDash_Settings_Section' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Section_General' ) ) ) {

	/**
	 * Class LearnDash Settings Section for Learner Notes Metabox.
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Section_General extends LearnDash_Settings_Section {

		/**
		 * Protected constructor for class
		 *
		 * @since 1.0.0
		 */
		protected function __construct() {

			$this->settings_page_id = 'learndash-notes';

			// This is the 'option_name' key used in the wp_options table.
			$this->setting_option_key = 'learndash_notes_settings';

			// This is the HTML form field prefix used.
			$this->setting_field_prefix = '';

			// Used within the Settings API to uniquely identify this section.
			$this->settings_section_key = 'learndash_notes_general';

			// Section label/header.
			$this->settings_section_label = esc_html__( 'How To', 'learndash_student_notes' );

			parent::__construct();

		}

		/**
		 * Initialize the metabox settings fields.
		 *
		 * @since 1.0.0
		 */
		public function load_settings_fields() {

			$this->setting_option_fields = array();

			ob_start();
			include LEARNDASH_NOTES_DIR . 'admin/views/how-to/shortcodes.php';
			$html = ob_get_clean();

			$html = $this->escape_shortcodes( $html );

			$this->setting_option_fields['how_to_shortcodes'] = array(
				'name'      => 'how_to_shortcodes',
				'name_wrap' => false,
				'type'      => 'html',
				'label'     => esc_html__( 'How to use the Learner Notes shortcodes in any posts and pages', 'learndash_student_notes' ),
				'value'     => $html,
			);

			ob_start();
			include LEARNDASH_NOTES_DIR . 'admin/views/how-to/enable-notification-icon.php';
			$html = ob_get_clean();

			$html = $this->escape_shortcodes( $html );

			$this->setting_option_fields['how_to_enable_notification_icon'] = array(
				'name'      => 'how_to_enable_notification_icon',
				'name_wrap' => false,
				'type'      => 'html',
				'label'     => esc_html__( 'How to enable the Learner Notes Notification Icon', 'learndash_student_notes' ),
				'value'     => $html,
			);

			ob_start();
			include LEARNDASH_NOTES_DIR . 'admin/views/how-to/video-tutorial.php';
			$html = ob_get_clean();

			$html = $this->escape_shortcodes( $html );

			$this->setting_option_fields['how_to_video_tutorial'] = array(
				'name'      => 'how_to_video_tutorial',
				'name_wrap' => false,
				'type'      => 'html',
				'label'     => esc_html__( 'Watch this video playlist to understand how it all works and see demonstrations for each version update', 'learndash_student_notes' ),
				'value'     => $html,
			);

			/** This filter is documented in sfwd-lms/includes/settings/settings-metaboxes/class-ld-settings-metabox-course-access-settings.php */
			$this->setting_option_fields = apply_filters( 'learndash_settings_fields', $this->setting_option_fields, $this->settings_section_key );

			parent::load_settings_fields();

		}

		/**
		 * Escapes any registered shortcodes within the content
		 *
		 * @param   string $content  HTML Content.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  string            HTML Content
		 */
		public function escape_shortcodes( $content ) {

			global $shortcode_tags;

			preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
			$tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

			$pattern = get_shortcode_regex( $tagnames );

			$content = preg_replace( "/{$pattern}/", '[$0]', $content );

			return $content;

		}

	}

	add_action(
		'learndash_settings_sections_init',
		function() {
			LearnDash_Notes_Settings_Section_General::add_section_instance();
		}
	);

}
