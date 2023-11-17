<?php
/**
 * Handles Shortcode Output
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the New Note shortcode
 *
 * @since   1.0.0
 * @return  string  Shortcode output
 */
function learndash_notes_add_new_note() {

	// condition fix for not conflicting wih tinymce classes.
	if ( is_admin() ) {
		return '';
	}

	$post_data    = wp_unslash( $_POST );
	$request_data = wp_unslash( $_REQUEST );

	ob_start();
	if ( isset( $post_data['ld_student_notes_add_note_nonce'], $post_data['related_post_id'] )
		&& wp_verify_nonce( $post_data['ld_student_notes_add_note_nonce'], 'ld_student_notes_add_note' )
	) {
		if ( isset( $request_data['llms_note_text'] ) ) {
			$llms_note_text    = $request_data['llms_note_text'];
			$related_post_id   = $request_data['related_post_id'];
			$related_post_type = $request_data['related_post_type'];
			$llms_notify_admin = '';
			$llms_notify_admin = isset( $request_data['llms_notify_admin'] ) ? $request_data['llms_notify_admin'] : '0';
			if ( $llms_notify_admin === 'on' ) {
				$llms_notify_admin = 1;
			}
			$note_id = 0;
			if ( ! empty( $llms_note_text ) ) {
				$user_id = get_current_user_id();
				$v       = array(
					'post_title'   => __( 'notes default title', 'learndash_student_notes' ),
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
				if ( (int) $llms_notify_admin === 1 ) {

					// email to admin/instructor.
					$site = get_bloginfo( 'name' );
					$from = get_bloginfo( 'admin_email' );
					$to   = get_bloginfo( 'admin_email' );

					// check for leader.
					$leader_id = learndash_notes_get_group_leader_id( $related_post_id );

					if ( $leader_id ) {
						$inst_data  = get_userdata( $leader_id );
						$inst_email = $inst_data->user_email;
						$to         = $inst_email;
					}

					$subject   = 'A learner has left you a note on ' . $site;
					$edit_link = get_edit_post_link( $note_id );
					$body      = "Hello Leader, <br/>	
					A learner has left a note for you.<br/><br/>
					You can view and respond to the note <a href='$edit_link'>here</a><br/><br/>
					Thanks,<br/>	$site ";
					$headers[] = "From: $site <$from>";
					$headers[] = 'Content-Type: text/html; charset=UTF-8';

					wp_mail( $to, $subject, $body, $headers );

				}

				// success message.
				if ( $note_id ) {
					include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/success_message.php';
				}
			}
		}
	}

	include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/add_new_note_form_legacy.php';
	return ob_get_clean();

}

add_shortcode( 'llms_add_new_note', 'learndash_notes_add_new_note' );

/**
 * Outputs the New Note via popup shortcode
 *
 * @since   1.0.0
 * @return  string  Shortcode output
 */
function learndash_notes_add_new_note_popup() {

	// condition fix for not conflicting wih tinymce classes.
	if ( is_admin() ) {
		return '';
	}

	$post_data    = wp_unslash( $_POST );
	$request_data = wp_unslash( $_REQUEST );

	ob_start();
	if ( isset( $post_data['ld_student_notes_add_note_nonce'], $post_data['related_post_id'] ) &&
		wp_verify_nonce( $post_data['ld_student_notes_add_note_nonce'], 'ld_student_notes_add_note' )
	) {

		if ( isset( $request_data['llms_note_text'] ) ) {

			$llms_note_text    = $request_data['llms_note_text'];
			$related_post_id   = $request_data['related_post_id'];
			$related_post_type = $request_data['related_post_type'];
			$llms_notify_admin = '';
			$llms_notify_admin = isset( $request_data['llms_notify_admin'] ) ? $request_data['llms_notify_admin'] : '0';
			if ( $llms_notify_admin === 'on' ) {
				$llms_notify_admin = 1;
			}

			$note_id = 0;
			if ( ! empty( $llms_note_text ) ) {
				$user_id = get_current_user_id();
				$v       = array(
					'post_title'   => __( 'notes default title', 'learndash_student_notes' ),
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
				if ( (int) $llms_notify_admin === 1 ) {

					// email to admin/instructor.
					$site = get_bloginfo( 'name' );
					$from = get_bloginfo( 'admin_email' );
					$to   = get_bloginfo( 'admin_email' );

					// check for leader.
					$leader_id = learndash_notes_get_group_leader_id( $related_post_id );

					if ( $leader_id ) {
						$inst_data  = get_userdata( $leader_id );
						$inst_email = $inst_data->user_email;
						$to         = $inst_email;
					}

					$subject   = 'A learner has left you a note on ' . $site;
					$edit_link = get_edit_post_link( $note_id );
					$body      = "Hello Leader, <br/>	
						A learner has left a note for you.<br/><br/>
						You can view and respond to the note <a href='$edit_link'>here</a><br/><br/>
						Thanks,<br/>	$site ";
					$headers[] = "From: $site <$from>";
					$headers[] = 'Content-Type: text/html; charset=UTF-8';

					wp_mail( $to, $subject, $body, $headers );

				}

				// success message.
				if ( $note_id ) {
					include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/success_message.php';
				}
			}
		}
	}

	include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'notes/add_new_note_form.php';
	return ob_get_clean();

}

add_shortcode( 'llms_add_new_note_popup', 'learndash_notes_add_new_note_popup' );

/**
 * Outputs the Notes List for the current Post
 *
 * @since   1.0.0
 * @return  string  Shortcode output
 */
function learndash_notes_list() {

	if ( is_admin() ) {
		return '';
	}

	ob_start();

	$user_id = get_current_user_id();
	if ( $user_id ) {

		$request_data = wp_unslash( $_REQUEST );
		$post_data    = wp_unslash( $_POST );

		$post_id_del = isset( $request_data['post_id_del'] ) ? $request_data['post_id_del'] : '';

		$current_user = get_current_user_id();
		if ( ! empty( $post_id_del ) &&
			isset( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ] ) &&
			wp_verify_nonce( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ], "ld_student_notes_delete_note_{$post_id_del}" )
		) {
			$c_user = get_post( $post_id_del );
			if ( (int) $current_user === (int) $c_user->post_author ) {

				// move to trash so admin can still check note.
				$trashed = wp_trash_post( $post_id_del );
				if ( $trashed ) {

					$success_message = esc_html( get_option( 'notedelsucc' ) );

					echo wp_kses_post( "<div class='alert alert-danger'>{$success_message}</div>" );
				}
			}
		}

		$args = array(
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
					'value' => get_the_ID(),
				),
			),
			'suppress_filters' => true,
		);

		$posts_array = get_posts( $args );

		include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'loop.php';
	}

	return ob_get_clean();
}

add_shortcode( 'llms_notes_list', 'learndash_notes_list' );

/**
 * Outputs all Notes for the current user
 *
 * @since   1.0.0
 * @return  string  Shortcode output
 */
function learndash_notes_full_notes_list() {

	if ( is_admin() ) {
		return '';
	}

	ob_start();

	$user_id = get_current_user_id();
	if ( $user_id ) {

		$request_data = wp_unslash( $_REQUEST );
		$post_data    = wp_unslash( $_POST );

		// delete code if ajax not work it will workon page reload.
		$post_id_del = isset( $request_data['post_id_del'] ) ? $request_data['post_id_del'] : '';

		$current_user = get_current_user_id();

		if ( ! empty( $post_id_del ) &&
			isset( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ] ) &&
			wp_verify_nonce( $post_data[ "ld_student_notes_delete_note_{$post_id_del}_nonce" ], "ld_student_notes_delete_note_{$post_id_del}" )
		) {
			$c_user = get_post( $post_id_del );
			if ( (int) $current_user === (int) $c_user->post_author ) {
				// move to trash so admin can still check note.
				$trashed = wp_trash_post( $post_id_del );
				if ( $trashed ) {

					$success_message = esc_html( get_option( 'notedelsucc' ) );

					echo wp_kses_post( "<div class='alert alert-danger'>{$success_message}</div>" );
				}
			}
		}

		$args = array(
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
			'suppress_filters' => true,
		);

		$posts_array = get_posts( $args );

		include_once LEARNDASH_NOTES_TEMPLATE_DIR . 'full-loop.php';
	}

	return ob_get_clean();

}

add_shortcode( 'llms_full_notes_list', 'learndash_notes_full_notes_list' );
