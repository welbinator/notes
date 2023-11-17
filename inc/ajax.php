<?php
/**
 * Student notes ajax functions
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_student_notes_ajax', 'learndash_notes_ajax_receiver' );

/**
 * Handles AJAX Requests
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_ajax_receiver() {

	$posted_data  = wp_unslash( $_POST );
	$request_data = wp_unslash( $_REQUEST );

	if ( ! isset( $posted_data['ld_student_notes_add_note_nonce'] ) || ! wp_verify_nonce( $posted_data['ld_student_notes_add_note_nonce'], 'ld_student_notes_add_note' ) ) {

		echo wp_json_encode(
			array(
				'success' => false,
			)
		);
		wp_die(
			'',
			'',
			array(
				'response' => 403,
			)
		);

	}

	global $wpdb;
	$related_post_id = intval( $posted_data['related_post_id'] );
	// insert note.
	if ( isset( $request_data['note'] ) ) {
		$llms_note_title   = $request_data['title'];
		$llms_note_text    = $request_data['note'];
		$related_post_id   = $request_data['related_post_id'];
		$related_post_type = $request_data['related_post_type'];
		$llms_notify_admin = '';
		$llms_notify_admin = isset( $request_data['llms_notify_admin'] ) ? $request_data['llms_notify_admin'] : '0';
		if ( $llms_notify_admin === 'true' ) {
			$llms_notify_admin = 1;
		}
		$note_id = 0;
		if ( ! empty( $llms_note_text ) ) {
			$user_id = get_current_user_id();
			$v       = array(
				'post_title'   => wp_strip_all_tags( $llms_note_title ),
				'post_content' => wp_kses_post( $llms_note_text ),
				'post_status'  => 'publish',
				'post_type'    => 'llms_student_notes',
				'post_author'  => $user_id,
			);
			// insert note.
			$note_id = wp_insert_post( $v );
			// add post meta.
			add_post_meta( $note_id, 'related_post_id', $related_post_id );
			add_post_meta( $note_id, 'related_post_type', $related_post_type );
			add_post_meta( $note_id, 'related_user_id', $user_id );
			add_post_meta( $note_id, 'llms_notify_admin', $llms_notify_admin );
			add_post_meta( $note_id, 'admin_response', '' );
			// notify instructor.
			$inst_data = false;
			if ( (int) $llms_notify_admin === 1 ) {
				// email to admin/instructor.
				$site      = get_bloginfo( 'name' );
				$from      = get_bloginfo( 'admin_email' );
				$to        = get_bloginfo( 'admin_email' );
				$leader_id = learndash_notes_get_group_leader_id( (int) $related_post_id );
				if ( $leader_id ) {
					$inst_data  = get_userdata( $leader_id );
					$inst_email = $inst_data->user_email;
					$to         = $inst_email;
				}
				$subject     = 'The learner has left you a note on ' . $site;
				$edit_link   = get_edit_post_link( $note_id );
				$post        = get_post( $note_id );
				$learner_obj = get_user_by( 'id', $user_id );

				if ( ! $post ) {
					return;
				}
				if ( 'revision' === $post->post_type ) {
					$action = '';
				} elseif ( 'display' === $context ) {
					$action = '&amp;action=edit';
				} else {
					$action = '&action=edit';
				}
				$post_type_object = get_post_type_object( $post->post_type );
				if ( ! $post_type_object ) {
					return;
				}
				if ( $post_type_object->_edit_link ) {
					$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
				} else {
					$link = '';
				}
				$edit_link               = $link;
				$llms_s_n_email_template = get_option( 'llms_s_n_email_template' );

				if ( $inst_data && ! empty( $llms_s_n_email_template['instructor_subject'] ) && ! empty( $llms_s_n_email_template['instructor_body'] ) ) {
					$subject               = $llms_s_n_email_template['instructor_subject'];
					$instructor_email_body = $llms_s_n_email_template['instructor_body'];

					$find                         = array(
						'{instructor_first_name}',
						'{instructor_last_name}',
						'{instructor_user_name}',
						'{instructor_post_link}',
						'{student_first_name}',
						'{student_last_name}',
						'{student_user_name}',
					);
					$replace                      = array(
						$inst_data->first_name ?? '',
						$inst_data->last_name ?? '',
						$inst_data->user_login,
						$edit_link,
						$learner_obj->first_name ?? '',
						$learner_obj->last_name ?? '',
						$learner_obj->user_login ?? '',
					);
					$filter_instructor_email_body = str_replace( $find, $replace, $instructor_email_body );

					$headers   = array();
					$headers[] = "From: $site <$from>";
					$headers[] = 'Content-Type: text/html; charset=UTF-8';
					wp_mail( $to, $subject, $filter_instructor_email_body, $headers );
				}
			}
		}
	}

	$return = array(
		'llms_notify_admin' => $llms_notify_admin,
		'list'              => learndash_notes_list_ajax( $related_post_id ),
		'post'              => $posted_data,
		'cuser_id'          => get_current_user_id(),
		'note_id'           => $note_id,
		'success_msg'       => learndash_notes_success_message(),
	);

	echo wp_json_encode( $return );
	wp_die();

}

/**
 * Retrieves the Notes list for the Current User
 *
 * @param   integer $related_post_id  Related Post ID.
 *
 * @since   1.0.0
 * @return  string                    List HTML
 */
function learndash_notes_list_ajax( $related_post_id ) {

	ob_start();
	$user_id = get_current_user_id();
	if ( $user_id ) {
		// set arguments.
		$args        = array(
			'posts_per_page'   => -1,
			'offset'           => 0,
			'category'         => '',
			'orderby'          => 'meta_value_num',
			'order'            => 'DESC',
			'include'          => '',
			'author'           => $user_id,
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'llms_student_notes',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'meta_query'       => array(
				array(
					'key'   => 'related_post_id',
					'value' => $related_post_id,
				),
			),
			'suppress_filters' => true,
		);
		$posts_array = get_posts( $args );
		include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'ajax/loop.php';
	}

	return ob_get_clean();
}

/**
 * Outputs a success message for an AJAX Response
 *
 * @since   1.0.0
 * @return  string  Success Message HTML
 */
function learndash_notes_success_message() {
	ob_start();
	include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/success_message.php';
	return ob_get_clean();
}

/**
 * Outputs a Deletion Successful message for an AJAX Response
 *
 * @since   1.0.0
 * @return  string  Success Message HTML
 */
function learndash_notes_success_del_message() {
	ob_start();
	include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/success_delete_message.php';
	return ob_get_clean();
}

add_action( 'wp_ajax_student_notes_ajax_del', 'learndash_notes_ajax_receiver_del' );

/**
 * Handles deleting a Note via AJAX
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_ajax_receiver_del() {

	$request_data = wp_unslash( $_REQUEST );
	$post_data    = wp_unslash( $_POST );

	$post_id_del = isset( $request_data['note_id'] ) ? $request_data['note_id'] : '';

	if ( ! isset( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ] ) || ! wp_verify_nonce( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ], "ld_student_notes_delete_note_{$post_id_del}" ) ) {

		echo wp_json_encode(
			array(
				'success' => false,
			)
		);
		wp_die(
			'',
			'',
			array(
				'response' => 403,
			)
		);

	}

	global $wpdb;
	$return = array();

	$current_user = get_current_user_id();
	if ( ! empty( $post_id_del ) ) {
		$c_user = get_post( $post_id_del );
		if ( (int) $current_user === (int) $c_user->post_author ) {
			// move to trash so admin can still check note.
			$trashed = wp_trash_post( $post_id_del );
			if ( $trashed ) {

				$success_message = esc_html( get_option( 'notedelsucc' ) );

				$msg = "<div class='alert alert-danger'>{$success_message}</div>";
			}
		}
	}
	$return = array(
		'trashed'     => $trashed,
		'success_msg' => learndash_notes_success_del_message(),
	);
	echo wp_json_encode( $return );
	wp_die();
}

add_action( 'wp_ajax_student_notes_ajax_mark_read', 'learndash_notes_ajax_mark_read' );

/**
 * Handles marking a Note as Read via AJAX
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_ajax_mark_read() {

	$request_data = wp_unslash( $_REQUEST );
	$post_data    = wp_unslash( $_POST );

	$post_id = isset( $request_data['note_id'] ) ? $request_data['note_id'] : '';

	if ( ! isset( $post_data[ "ld_student_notes_read_note_{$post_id}_nonce" ] ) || ! wp_verify_nonce( $post_data[ "ld_student_notes_read_note_{$post_id}_nonce" ], "ld_student_notes_read_note_{$post_id}" ) ) {

		echo wp_json_encode(
			array(
				'success' => false,
			)
		);
		wp_die(
			'',
			'',
			array(
				'response' => 403,
			)
		);

	}

	global $wpdb;
	$return = array();

	$current_user = get_current_user_id();
	if ( ! empty( $post_id ) ) {
		$c_user = get_post( $post_id );
		if ( (int) $current_user === (int) $c_user->post_author ) {
			update_post_meta( $post_id, 'llms_note_unread', false );
		}
	}
	$return = array(
		'trashed'     => '',
		'success_msg' => learndash_notes_success_del_message(),
	);
	echo wp_json_encode( $return );
	wp_die();
}
