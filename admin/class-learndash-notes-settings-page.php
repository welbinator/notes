<?php
/**
 * Notes by LearnDash Settings Page
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ( class_exists( 'LearnDash_Settings_Page' ) ) && ( ! class_exists( 'LearnDash_Notes_Settings_Page' ) ) ) {

	/**
	 * Class Notes by LearnDash Settings Page
	 *
	 * @since 1.0.0
	 */
	class LearnDash_Notes_Settings_Page extends LearnDash_Settings_Page {

		/**
		 * Public constructor for class
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->parent_menu_page_url = 'admin.php?page=learndash_lms_settings';
			$this->menu_page_capability = LEARNDASH_ADMIN_CAPABILITY_CHECK;
			$this->settings_page_id     = 'learndash-notes';

			$this->settings_page_title     = esc_html_x( 'Notes', 'Notes Tab Label', 'learndash_student_notes' );
			$this->settings_columns        = 2;
			$this->show_quick_links_meta   = false;
			$this->settings_metabox_as_sub = true;

			$this->settings_tab_priority = 40;
			parent::__construct();

		}

		/**
		 * Action hook to handle admin_tabs processing from LearnDash.
		 *
		 * @since 1.0.0
		 *
		 * @param string $admin_menu_section Current admin menu section.
		 */
		public function admin_tabs( $admin_menu_section ) {

			if ( $admin_menu_section !== $this->parent_menu_page_url ) {
				return;
			}

			parent::admin_tabs( $admin_menu_section );

		}

	}

}

add_action(
	'learndash_settings_pages_init',
	function() {
		LearnDash_Notes_Settings_Page::add_page_instance();
	}
);
