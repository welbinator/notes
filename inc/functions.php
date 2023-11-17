<?php
/**
 * Holds general functions
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper function to determine if the ability for Instructors to edit a Learner's Note from the backend is allowed. Defaults to false.
 *
 * @since   1.0.0
 * @return  boolean  Enabled/Disabled
 */
function learndash_notes_backend_student_note_editing_allowed() {

	return apply_filters( 'learndash_notes_backend_student_note_editing_allowed', ( defined( 'LEARNDASH_NOTES_INSTRUCTORS_CAN_EDIT_STUDENT_NOTES' ) && LEARNDASH_NOTES_INSTRUCTORS_CAN_EDIT_STUDENT_NOTES ) );

}

add_action( 'init', 'learndash_notes_create_post_type' );

/**
 * Creates the Notes Post Type
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_create_post_type() {

	$supports = array( 'author', 'thumbnail' );

	if ( learndash_notes_backend_student_note_editing_allowed() ) {

		$supports = array_merge( $supports, array( 'title', 'editor' ) );

	}

	register_post_type(
		'llms_student_notes',
		array(
			'labels'          => array(
				'name'          => __( 'Notes', 'learndash_student_notes' ),
				'singular_name' => __( 'Note', 'learndash_student_notes' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'has_archive'     => false,
			'menu_icon'       => 'dashicons-id-alt',
			'menu_position'   => 52,
			'supports'        => $supports,
			'capability_type' => array( 'student_note', 'student_notes' ),
			'capabilities'    => array(),
			'map_meta_cap'    => false,
		)
	);

}

add_action( 'admin_init', 'learndash_notes_grant_capabilities' );

/**
 * Adds necessary capabilities to access the Learners Notes section
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_grant_capabilities() {

	$roles = array();

	$admin_role = get_role( 'administrator' );
	if ( ( $admin_role ) && ( $admin_role instanceof WP_Role ) ) {

		$roles[] = $admin_role;

	}

	$group_leader_role = get_role( 'group_leader' );
	if ( ( $group_leader_role ) && ( $group_leader_role instanceof WP_Role ) ) {

		$roles[] = $group_leader_role;

	}

	$caps = array(
		'edit_student_note',
		'edit_student_notes',
		'edit_others_student_notes',
		'delete_published_student_notes',
		'delete_others_student_notes',
		'delete_student_note',
		'delete_student_notes',
	);

	foreach ( $roles as $role ) {

		foreach ( $caps as $cap ) {

			$role->add_cap( $cap );

		}
	}

}

add_filter( 'user_has_cap', 'learndash_notes_remove_cap', 10, 3 );

/**
 * Make it so that a user cannot create a new Note via the backend
 *
 * Utilizing map_meta_cap when instantiating the Post Type causes issues with users who do not have the actual edit_posts capability and cannot see the LearnDash LMS Top-level Menu Item from accessing the Notes list on the backend.
 *
 * Because of this, we need to restrict certain actions by filtering user_has_cap rather than simply being more explicit with our Capabilities (setting create_posts to do_not_allow, for instance)
 *
 * @param   array $allcaps  All the capabilities of the user.
 * @param   array $cap      [0] Required capability.
 * @param   array $args     [0] Requested capability.
 *                          [1] Current User ID.
 *                          [2] Associated object ID.
 *
 * @return  array            All the capabilities of the user.
 */
function learndash_notes_remove_cap( $allcaps, $cap, $args ) {

	$action = current_action();

	if ( ! isset( $cap[0] ) || $cap[0] !== 'edit_student_notes' ) {
		return $allcaps;
	}

	if ( isset( $args[2] ) ) {
		return $allcaps;
	}

	global $pagenow;

	if ( $pagenow !== 'post-new.php' ) {
		return $allcaps;
	}

	unset( $allcaps['edit_student_notes'] );

	return $allcaps;

}

add_filter( 'post_row_actions', 'learndash_notes_post_row_actions', 10, 2 );

/**
 * Removes the Quick Edit option for Learner Notes
 *
 * @param   array   $actions  Post Row Actions.
 * @param   WP_Post $post     Current Post Object.
 *
 * @since   1.0.0
 * @return  array              Post Row Actions
 */
function learndash_notes_post_row_actions( $actions, $post ) {

	if ( $post->post_type !== 'llms_student_notes' ) {
		return $actions;
	}

	unset( $actions['inline hide-if-no-js'] );

	return $actions;

}

add_action( 'pre_get_posts', 'learndash_notes_restrict_backend_results' );

/**
 * Only show relevant Learner Notes in the backend for the logged in User
 *
 * @param   WP_Query $query  WP_Query Object.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_restrict_backend_results( $query ) {

	if ( ! is_admin() ) {
		return;
	}

	if ( $query->get( 'post_type' ) !== 'llms_student_notes' ) {
		return;
	}

	if ( ! is_user_logged_in() || ! function_exists( 'learndash_is_admin_user' ) ) {

		// Don't show any Learner Notes.
		$query->set( 'author__in', array( 0 ) );
		return;

	}

	if ( learndash_is_admin_user() ) {
		return;
	}

	if ( ! function_exists( 'learndash_get_groups_administrators_users' ) ) {
		return;
	}

	$learner_ids = learndash_get_groups_administrators_users( get_current_user_id() );

	// Include yourself, just to prevent confusion if you have your own notes stored.
	$learner_ids[] = get_current_user_id();

	$query->set( 'author__in', $learner_ids );

	if ( get_option( 'ld_student_notes_show_private_notes' ) ) {
		return;
	}

	$meta_query = $query->get( 'meta_query' );

	if ( empty( $meta_query ) ) {
		$meta_query = array(
			'relation' => 'AND',
		);
	}

	$meta_query[] = array(
		'key'   => 'llms_notify_admin',
		'value' => 1,
	);

	$query->set( 'meta_query', $meta_query );

}

add_filter( 'wp_count_posts', 'learndash_notes_restrict_backend_counts', 10, 3 );

/**
 * Only count results for relevant Learner Notes in the backend for the logged in User
 *
 * @param   object $counts           Object containing the current Post Type's counts by status.
 * @param   string $post_type        Post Type.
 * @param   string $read_permission  The permission to determine if the posts are 'readable' by the current user.
 *
 * @since   1.0.0
 * @return  object                    Object containing the current Post Type's counts by status
 */
function learndash_notes_restrict_backend_counts( $counts, $post_type, $read_permission ) {

	if ( $post_type !== 'llms_student_notes' ) {
		return $counts;
	}

	if ( ! is_user_logged_in() || ! function_exists( 'learndash_is_admin_user' ) ) {
		return $counts;
	}

	if ( learndash_is_admin_user() ) {
		return $counts;
	}

	// This is already restricted to specific Authors above.
	$query = new WP_Query(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'all',
			'fields'         => 'ids',
		)
	);

	foreach ( get_object_vars( $counts ) as $key => $value ) {

		// Clear out all counts.
		$counts->{$key} = 0;

	}

	if ( $query->have_posts() ) {

		foreach ( $query->posts as $post_id ) {

			$post_status = get_post_status( $post_id );

			// Increment counts based on Post Status.
			$counts->{$post_status}++;

		}

		wp_reset_postdata();

	}

	return $counts;

}

add_filter( 'user_has_cap', 'learndash_notes_restrict_accessing_notes', 10, 3 );

/**
 * Ensure that no one can be sneaky and guess a Post ID to edit/reply to a Learner Note that they should not be able to
 *
 * @param   array $allcaps  All the capabilities of the user.
 * @param   array $cap      [0] Required capability.
 * @param   array $args     [0] Requested capability.
 *                          [1] Current User ID.
 *                          [2] Associated object ID.
 *
 * @return  array           All the capabilities of the user
 */
function learndash_notes_restrict_accessing_notes( $allcaps, $cap, $args ) {

	if ( ! isset( $args[2] ) ) {
		return $allcaps;
	}

	$post = get_post( $args[2] );

	if ( ! $post ) {
		return $allcaps;
	}

	if ( $post->post_type !== 'llms_student_notes' ) {
		return $allcaps;
	}

	if ( ! is_user_logged_in() || ! function_exists( 'learndash_is_admin_user' ) ) {
		return $allcaps;
	}

	// Prevent infinite loop.
	remove_filter( 'user_has_cap', 'learndash_notes_restrict_accessing_notes', 10, 3 );

	if ( learndash_is_admin_user() ) {
		return $allcaps;
	}

	add_filter( 'user_has_cap', 'learndash_notes_restrict_accessing_notes', 10, 3 );

	if ( ! function_exists( 'learndash_get_groups_administrators_users' ) ) {
		return $allcaps;
	}

	$learner_ids = learndash_get_groups_administrators_users( get_current_user_id() );

	$learner_ids = array_map( 'strval', $learner_ids );

	if ( in_array( $post->post_author, $learner_ids, true ) ) {
		return $allcaps;
	}

	if ( empty( $cap ) ) {
		return $allcaps;
	}

	// Don't allow this to occur.
	unset( $allcaps[ $cap[0] ] );

	return $allcaps;

}

/**
 * Gets the first found Group Leader User ID based on the viewed Content
 *
 * @param   integer $post_id     Currently viewed Post ID.
 * @param   integer $learner_id  Learner ID to check for. This defaults to the logged in user.
 *
 * @since   1.0.0
 * @return  integer               Group Leader User ID
 */
function learndash_notes_get_group_leader_id( $post_id, $learner_id = 0 ) {

	if ( ! $learner_id ) {
		$learner_id = get_current_user_id();
	}

	$leader_id = 0;

	// check for some learn dash functions.
	if ( function_exists( 'learndash_get_course_id' ) && function_exists( 'learndash_get_course_groups' ) && function_exists( 'learndash_get_users_group_ids' ) && function_exists( 'learndash_get_groups_administrator_ids' ) ) {

		global $post;

		$related_course_id = learndash_get_course_id( $post_id );

		$groups = learndash_get_course_groups( $related_course_id );

		$user_groups = learndash_get_users_group_ids( $learner_id );

		$groups = array_values( array_intersect( $groups, $user_groups ) );

		if ( ! empty( $groups ) ) {

			// get it from first group.
			$group_id = $groups[0];

			$leaders = learndash_get_groups_administrator_ids( $group_id );

			if ( ! empty( $leaders ) ) {

				// get first leader id.
				$leader_id = $leaders[0];

			}
		}
	}

	return $leader_id;

}

add_action( 'init', 'learndash_notes_download_note' );

/**
 * Handles downloading Notes as a Word Document file
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_download_note() {

	$get_data = wp_unslash( $_GET );

	if ( isset( $get_data['download_note'] ) &&
		isset( $get_data[ "ld_student_notes_download_note_{$get_data['note_id']}_nonce" ] )
		&& wp_verify_nonce( $get_data[ "ld_student_notes_download_note_{$get_data['note_id']}_nonce" ], "ld_student_notes_download_note_{$get_data['note_id']}" )
	) {
		$csv     = '<b>Downloading to file.';
		$note_id = $get_data['note_id'];
		if ( $note_id ) {
			$post1 = get_post( $note_id );

			// Load library.
			include_once LEARNDASH_NOTES_DIR . '/HtmlToDoc.class.php';

			// Initialize class.
			$htd          = new learndash_student_notes\HTML_TO_DOC();
			$html_content = $post1->post_content;
			$htd->create_doc( $html_content, 'note-' . $note_id, 1 );
			exit;
		}
	}

}
