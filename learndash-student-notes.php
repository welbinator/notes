<?php
/**
 * Plugin Name: Notes by LearnDash
 * Version: 1.0.3
 * Plugin URI: https://www.learndash.com/notes-by-learndash/
 * Author: LearnDash
 * Author URI: https://www.learndash.com/
 * Description: Add Learner Notes to Learndash and communicate with your students
 * text domain: learndash_student_notes
 *
 * @package learndash_student_notes
 */

// define plugin root directory.
define( 'LEARNDASH_NOTES_VERSION', '1.0.3' );
define( 'LEARNDASH_NOTES_DIR', plugin_dir_path( __FILE__ ) );
define( 'LEARNDASH_NOTES_TEMPLATE_DIR', LEARNDASH_NOTES_DIR . 'templates/' );
define( 'LEARNDASH_NOTES_LICENSING_SITE_URL', 'https://checkout.learndash.com/wp-json/learndash/v1/site/auth_token' );

// include some scripts and styles.
define( 'LEARNDASH_NOTES_URL', plugin_dir_url( __FILE__ ) );

require_once LEARNDASH_NOTES_DIR . 'inc/Dependency_Checker.php';

use LearnDash_Notes\Utility\Dependency_Checker;

$learndash_notes_dependency_checker = new Dependency_Checker();

$learndash_notes_dependency_checker->set_dependencies(
	array(
		'sfwd-lms/sfwd_lms.php' => array(
			'label'            => '<a href="https://www.learndash.com" target="_blank">' . __( 'LearnDash LMS', 'learndash_student_notes' ) . '</a>',
			'class'            => 'SFWD_LMS',
			'version_constant' => 'LEARNDASH_VERSION',
		),
	)
);

$learndash_notes_dependency_checker->set_message(
	esc_html__( 'Notes by LearnDash requires the following plugin(s) to be active:', 'learndash_student_notes' )
);

add_action(
	'plugins_loaded',
	function() use ( $learndash_notes_dependency_checker ) {

		$learndash_notes_dependency_checker->check_inactive_plugin_dependency();

		// If plugin requirements aren't met, don't run anything else to prevent possible fatal errors.
		if ( ! $learndash_notes_dependency_checker->check_dependency_results() || php_sapi_name() === 'cli' ) {
			return;
		}

		// Integrates into LearnDash's Settings API.
		require_once LEARNDASH_NOTES_DIR . 'admin/class-learndash-notes-settings-page.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section-general.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section-features.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section-labels.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section-styling.php';

		require_once LEARNDASH_NOTES_DIR . 'admin/sections/class-learndash-notes-settings-section-email.php';

		require_once LEARNDASH_NOTES_DIR . 'inc/functions.php';
		require_once LEARNDASH_NOTES_DIR . 'inc/shortcodes.php';

		// columns and filters.
		require_once LEARNDASH_NOTES_DIR . 'inc/columns-filters.php';
		// meta boxes.
		require_once LEARNDASH_NOTES_DIR . 'inc/post-meta.php';
		// FOR AJAX.
		require_once LEARNDASH_NOTES_DIR . 'inc/ajax.php';
		require_once LEARNDASH_NOTES_DIR . 'admin/class-learndash-notes-upgrade.php';

		add_action( 'admin_init', 'learndash_notes_initialize_upgrades', 9 );

		add_action( 'wp_head', 'learndash_notes_scripts_styles' );

		add_action( 'admin_print_styles', 'learndash_notes_admin_scripts' );

		add_action( 'admin_menu', 'learndash_notes_license_menu' );

		add_action( 'learndash_admin_tabs_set', 'learndash_notes_admin_tabs_set', 10, 2 );

		add_filter( 'learndash_header_tab_menu', 'learndash_notes_header_tab_menu', 10, 3 );

		add_filter( 'wp_nav_menu_objects', 'learndash_notes_nav_menu_objects_inject_unread_icon', 30, 2 );

		add_action( 'init', 'learndash_notes_load_auto_inject_shortcode_hooks', 11 );

		add_filter( 'block_core_navigation_render_inner_blocks', 'learndash_notes_navigation_block_inject_unread_icon' );

	}
);

register_activation_hook( __FILE__, 'learndash_notes_activation' );

/**
 * Runs on activation to set "hard default" values for our Options
 * This is particularly important for toggle fields, which would otherwise default to "off" with an empty value
 * All other fields have a default dynamically populated for them and are not an issue in this case.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_activation() {

	try {

		if ( ! defined( 'LEARNDASH_VERSION' ) ) {

			throw new Exception( __( 'Notes by LearnDash requires that LearnDash LMS is installed and activated.', 'learndash_student_notes' ) );

		}

		// Attempt to verify the Auth Token. On failure, deactivate the plugin and output an error message.
		learndash_notes_check_auth_token();

	} catch ( Exception $exception ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );
		die( esc_html( $exception ) );

	}

}

/**
 * Verifies the included Auth-Token against the licensing server
 *
 * @throws  Exception  On failure.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_check_auth_token() {

	$message = __( 'Unfortunately, it appears that the authentication token you have provided is invalid. We kindly request that you re-download the package and attempt to install again. If the issue persists, please do not hesitate to contact our support team for further assistance.', 'learndash_student_notes' );

	if ( ! file_exists( LEARNDASH_NOTES_DIR . '/auth-token.php' ) ) {
		throw new Exception( $message );
	}

	$auth_token = include_once LEARNDASH_NOTES_DIR . '/auth-token.php';
	if ( empty( $auth_token ) ) {
		throw new Exception( $message );
	}
	$response = wp_remote_post(
		LEARNDASH_NOTES_LICENSING_SITE_URL,
		array(
			'body' => array(
				'site_url'   => site_url(),
				'auth_token' => $auth_token,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		throw new Exception( $response->get_error_message() );
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
		update_option( 'learndash_notes_license', $data );
	} else {
		$error_message = $data['message'] ?? $message;
		throw new Exception( $error_message );
	}

}

/**
 * Initializes our Database Upgrade class
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_initialize_upgrades() {

	$upgrade = new LearnDash_Notes_Upgrade();

}

/**
 * Enqueues Frontend CSS/JS used by the plugin
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_scripts_styles() {
	// include js.
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-accordion' );
	wp_enqueue_script( 'jquery-effects-shake' );
	wp_register_script( 'LEARNDASH_NOTES_URL_js_j', LEARNDASH_NOTES_URL . 'dist/scripts.js', array( 'jquery' ), LEARNDASH_NOTES_VERSION, false );
	wp_enqueue_script( 'LEARNDASH_NOTES_URL_js_j' );
	wp_register_script( 'LEARNDASH_NOTES_ajax_js', LEARNDASH_NOTES_URL . 'dist/scripts-ajax.js', array( 'jquery', 'jquery-effects-shake' ), LEARNDASH_NOTES_VERSION, false );
	$translation_array = array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'LEARNDASH_NOTES_ajax_js', 'sn_object', $translation_array );
	wp_enqueue_script( 'LEARNDASH_NOTES_ajax_js' );
	wp_register_style( 'student-notes-style', LEARNDASH_NOTES_URL . 'dist/styles.css', array(), LEARNDASH_NOTES_VERSION );
	wp_enqueue_style( 'student-notes-style' );
	wp_style_add_data( 'student-notes-style', 'rtl', 'replace' );
	// jquery ui style.
	global $wp_scripts;
	// get registered script object for jquery-ui.
	$ui = $wp_scripts->query( 'jquery-ui-core' );

	// tell WordPress to load the Smoothness theme from Google CDN.
	$protocol = is_ssl() ? 'https' : 'http';
	$url      = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
	wp_enqueue_style( 'jquery-ui-smoothness', $url, array(), $ui->ver );

	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '4.7.0' );
}

/**
 * Enqueues Admin CSS/JS used by the plugin
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_admin_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'student-notes-admin-style', $plugin_url . '/dist/admin-styles.css', array(), LEARNDASH_NOTES_VERSION );
	wp_style_add_data( 'student-notes-admin-style', 'rtl', 'replace' );
}

/**
 * Adds Menu Items for accessing the Notes page and its settings
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_license_menu() {

	global $submenu;

	if ( isset( $submenu['learndash-lms'] ) ) {

		remove_menu_page( 'edit.php?post_type=llms_student_notes' );

		add_submenu_page(
			'learndash-lms',
			'',
			__( 'Notes', 'learndash_student_notes' ),
			'edit_student_notes',
			'edit.php?post_type=llms_student_notes',
			'',
			8
		);

	} else {

		add_submenu_page(
			'edit.php?post_type=llms_student_notes',
			'',
			__( 'Settings', 'learndash_student_notes' ),
			defined( 'LEARNDASH_ADMIN_CAPABILITY_CHECK' ) && LEARNDASH_ADMIN_CAPABILITY_CHECK ? LEARNDASH_ADMIN_CAPABILITY_CHECK : 'manage_options',
			'admin.php?page=learndash_student_notes',
			''
		);

	}

}

/**
 * Get LearnDash to recognize our Posts page as somewhere it should puts its header
 *
 * @param   string                    $current_screen_parent_file  Parent File.
 * @param   LearnDash_Admin_Menu_Tabs $admin_tabs                  LearnDash Admin Menu Tabs Object.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_admin_tabs_set( $current_screen_parent_file, $admin_tabs ) {

	if ( ! current_user_can( 'edit_student_notes' ) ) {
		return;
	}

	global $submenu;

	if ( ! isset( $submenu['learndash-lms'] ) ) {
		return;
	}

	$menu_item = array();
	foreach ( $submenu['learndash-lms'] as $submenu_item ) {

		if ( $submenu_item[2] === 'edit.php?post_type=llms_student_notes' ) {
			$menu_item = $submenu_item;
			break;
		}
	}

	$admin_tabs->add_admin_tab_item(
		'edit.php?post_type=llms_student_notes',
		$menu_item
	);

}

/**
 * Add our own tabs to the LearnDash Header
 *
 * @param   array  $tabs          Tabs array.
 * @param   string $menu_tab_key  Parent File.
 * @param   string $post_type     Current Post Type.
 *
 * @since   1.0.0
 * @return  array                  Tabs array.
 */
function learndash_notes_header_tab_menu( $tabs, $menu_tab_key, $post_type ) {

	if ( $post_type !== 'llms_student_notes' ) {
		return $tabs;
	}

	global $submenu;

	if ( ! isset( $submenu['learndash-lms'] ) ) {
		return $tabs;
	}

	$tabs = array(
		array(
			'id'         => 'post-body-content',
			'name'       => esc_html__( 'Notes', 'learndash_student_notes' ),
			'link'       => admin_url( 'edit.php?post_type=llms_student_notes' ),
			'isExternal' => 'true',
			'actions'    => array(),
			'metaboxes'  => array(
				'learndash-student-notes-student-note',
				'learndash-student-notes-instructor-reply',
			),
		),
	);

	$capability = defined( 'LEARNDASH_ADMIN_CAPABILITY_CHECK' ) && LEARNDASH_ADMIN_CAPABILITY_CHECK ? LEARNDASH_ADMIN_CAPABILITY_CHECK : 'manage_options';

	if ( current_user_can( $capability ) ) {

		$tabs[] = array(
			'id'         => 'notes-settings',
			'name'       => esc_html__( 'Settings', 'learndash_student_notes' ),
			'link'       => admin_url( 'admin.php?page=learndash-notes' ),
			'isExternal' => 'true',
			'actions'    => array(),
			'metaboxes'  => array(),
		);

	}

	return $tabs;

}

/**
 * Inject the Unread Icon into Nav Menus when the Full Site Editor is not enabled
 *
 * @param   array  $menu_items  Array of WP_Post objects for the Nav Menu.
 * @param   object $menu_args   Object containing wp_nav_menu() arguments.
 *
 * @since   1.0.0
 * @return  array               Array of WP_Post objects for the Nav Menu
 */
function learndash_notes_nav_menu_objects_inject_unread_icon( $menu_items, $menu_args ) {

	foreach ( $menu_items as $menu_key => &$menu_item ) {

		if ( ! property_exists( $menu_item, 'attr_title' ) ) {
			$menu_item->attr_title = '';
		}

		if ( ! property_exists( $menu_item, 'url' ) ) {
			$menu_item->classes = array();
		}

		if ( ! in_array( 'llmssn-bell-icon', $menu_item->classes, true ) ) {
			continue;
		}

		$icon_class      = esc_attr( get_option( 'bell_icon' ) );
		$bell_icon_color = esc_attr( get_option( 'bell_icon_color' ) );

		$args = array(
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'author'         => get_current_user_id(),
			'post_type'      => 'llms_student_notes',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'llms_note_unread',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {

			unset( $menu_items[ $menu_key ] );
			continue;

		}

		if ( empty( $menu_item->attr_title ) ) {

			$menu_item->attr_title = $menu_item->title;

		}

		$menu_item->title = '<span class="show-unread-bell-header">' .
			"<i class=\"fa {$icon_class}\" style=\"color: {$bell_icon_color};\" aria-hidden=\"true\"></i>" .
		'</span>';

	}

	return $menu_items;

}

/**
 * If Focus Mode is enabled, we need to switch up the hooks we use to auto-inject our Shortcodes on Course Content
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_load_auto_inject_shortcode_hooks() {

	add_action( 'learndash-course-after', 'learndash_notes_auto_inject_shortcodes', 10, 3 );
	add_action( 'learndash-quiz-after', 'learndash_notes_auto_inject_shortcodes', 10, 3 );

	$in_focus_mode = LearnDash_Settings_Section::get_section_setting( 'LearnDash_Settings_Theme_LD30', 'focus_mode_enabled' );

	if ( $in_focus_mode !== 'yes' ) {

		add_action( 'learndash-all-course-steps-before', 'learndash_notes_auto_inject_shortcodes', 10, 3 );

	} else {

		add_action( 'learndash-lesson-after', 'learndash_notes_auto_inject_shortcodes', 10, 3 );
		add_action( 'learndash-topic-after', 'learndash_notes_auto_inject_shortcodes', 10, 3 );

	}

}

/**
 * Automatically include Learner Notes shortcodes on all Course Content if enabled
 *
 * @param   integer $post_id    Current Post ID.
 * @param   integer $course_id  Current Course ID.
 * @param   integer $user_id    Current User ID.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_auto_inject_shortcodes( $post_id, $course_id, $user_id ) {

	if ( get_option( 'ld_student_notes_auto_include_add_note' ) ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo learndash_notes_add_new_note();

	}

	if ( get_option( 'ld_student_notes_auto_include_add_note_popup' ) ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo learndash_notes_add_new_note_popup();

	}

	if ( get_option( 'ld_student_notes_auto_include_notes_list' ) ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo learndash_notes_list();

	}

	if ( get_option( 'ld_student_notes_auto_include_all_notes' ) ) {

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo learndash_notes_full_notes_list();

	}

}

/**
 * Inject the Unread Icon into Nav Menus when the Full Site Editor is enabled
 *
 * @param   WP_Block_List $inner_blocks  Inner Blocks for the Navigation Block.
 *
 * @since   1.0.0
 * @return  WP_Block_List                Inner Blocks for the Navigation Block
 */
function learndash_notes_navigation_block_inject_unread_icon( $inner_blocks ) {

	if ( ! is_user_logged_in() ) {
		return $inner_blocks;
	}

	foreach ( $inner_blocks as $index => $block ) {

		if ( $block->inner_blocks ) {

			// Recursively set any Submenu Items if needed.
			$block->inner_blocks = learndash_notes_navigation_block_inject_unread_icon( $block->inner_blocks );

		}

		$block->parsed_block = wp_parse_args(
			$block->parsed_block,
			array(
				'attrs' => array(),
			)
		);

		$block->parsed_block['attrs'] = wp_parse_args(
			$block->parsed_block['attrs'],
			array(
				'className' => '',
				'title'     => '',
			)
		);

		if ( strpos( $block->parsed_block['attrs']['className'], 'llmssn-bell-icon' ) === false ) {
			continue;
		}

		$icon_class      = esc_attr( get_option( 'bell_icon' ) );
		$bell_icon_color = esc_attr( get_option( 'bell_icon_color' ) );

		$args = array(
			'posts_per_page' => -1,
			'offset'         => 0,
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'author'         => get_current_user_id(),
			'post_type'      => 'llms_student_notes',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'llms_note_unread',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {

			$inner_blocks->offsetUnset( $index );
			continue;

		}

		if ( empty( $block->parsed_block['attrs']['title'] ) ) {

			$block->parsed_block['attrs']['title'] = $block->parsed_block['attrs']['label'];

		}

		$block->parsed_block['attrs']['label'] = '<span class="show-unread-bell-header">' .
			"<i class=\"fa {$icon_class}\" style=\"color: {$bell_icon_color};\" aria-hidden=\"true\"></i>" .
		'</span>';

		// You cannot loop by reference with Iterable objects, so this is our next-best option.
		$inner_blocks->offsetSet( $index, $block );

	}

	return $inner_blocks;

}
