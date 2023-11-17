<?php
/**
 * Modifies the Post Meta for our Post Type
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'learndash_notes_cd_meta_box_add' );

/**
 * Outputs the the Meta Boxes for our Post Type
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_cd_meta_box_add() {

	if ( ! learndash_notes_backend_student_note_editing_allowed() ) {

		add_meta_box( 'learndash-student-notes-student-note', __( "Learner's Note", 'learndash_student_notes' ), 'learndash_notes_student_note_meta_box_cb', 'llms_student_notes', 'normal', 'high' );

	}

	add_meta_box( 'learndash-student-notes-instructor-reply', __( 'Instructor reply', 'learndash_student_notes' ), 'learndash_notes_instructor_reply_meta_box_cb', 'llms_student_notes', 'normal', 'high' );

}

/**
 * Outputs the Learner's Note without providing the ability to edit it
 *
 * @param   WP_Post $post  The currently edited Post.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_student_note_meta_box_cb( $post ) {

	?>

	<h1><?php the_title(); ?></h1>

	<?php

	the_content();

}

/**
 * Callback for the Instructor Notes Meta Box
 *
 * @param   WP_Post $post  The currently edited Post.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_instructor_reply_meta_box_cb( $post ) {

	$post_id = $post->ID;
	// We'll use this nonce field later on when saving.
	wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
	?>
	<p>
		<label for="my_meta_box_text"><?php esc_html_e( 'Type:', 'learndash_student_notes' ); ?> </label>
		<?php
		$related_post_type = get_post_meta( $post_id, 'related_post_type', true );
		echo esc_html( $related_post_type );
		?>
	</p>
	<p>
		<label for="my_meta_box_text">Title :  </label>
		<?php
		$related_post_id = absint( get_post_meta( $post_id, 'related_post_id', true ) );
		$edit_link       = get_edit_post_link( $related_post_id );
		$edit_link       = get_the_permalink( $related_post_id );
		if ( ! empty( $related_post_id ) ) {
			echo wp_kses_post( sprintf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $related_post_id ) ) );
		}
		?>
	</p>
	<p>
		<label for="my_meta_box_text"><?php esc_html_e( 'Notify Instructor:', 'learndash_student_notes' ); ?> </label>
		<?php
		$notify = get_post_meta( $post_id, 'llms_notify_admin', true );
		if ( (int) $notify === 1 ) {
			esc_html_e( 'Yes', 'learndash_student_notes' );
		} else {
			esc_html_e( 'No', 'learndash_student_notes' );
		}
		?>
	</p> 
	<p>
		<label for="my_meta_box_text"><?php esc_html_e( 'Notify Learner:', 'learndash_student_notes' ); ?> </label>
		<?php
		$notify = get_post_meta( $post_id, 'llms_notify_to_student', true );
		?>
		<input type="checkbox" name="llms_notify_to_student" value="yes" <?php checked( $notify, 'yes' ); ?> />
	</p>
	<p>
		<label id="Respond" for="my_meta_box_text"><?php esc_html_e( 'Instructor Response:', 'learndash_student_notes' ); ?> </label><br/>
		<?php
		$admin_response = get_post_meta( $post_id, 'admin_response', true );
		$content        = $admin_response;
		$editor_id      = 'admin_response';
		$settings       = array(
			'media_buttons' => false,
			'textarea_rows' => 10,
			'required'      => 'required',
			'quicktags'     => false,
		);
		wp_editor( $content, $editor_id, $settings );
		?>
	</p>
	<?php
}

add_action( 'save_post', 'learndash_notes_meta_box_save' );

/**
 * Handles saving our Meta Data
 *
 * @param   integer $post_id  Current Post ID.
 *
 * @since   1.0.0
 * @return  void
 */
function learndash_notes_meta_box_save( $post_id ) {

	// Bail if we're doing an auto save.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_data = wp_unslash( $_POST );

	// if our nonce isn't there, or we can't verify it, bail.
	if ( ! isset( $post_data['meta_box_nonce'] ) || ! wp_verify_nonce( $post_data['meta_box_nonce'], 'my_meta_box_nonce' ) ) {
		return;
	}

	// Make sure your data is set before trying to save it.
	if ( isset( $post_data['my_meta_box_text'] ) ) {
		update_post_meta( $post_id, 'my_meta_box_text', $post_data['my_meta_box_text'] );
	}

	$admin_response  = get_post_meta( $post_id, 'admin_response', true );
	$related_post_id = get_post_meta( $post_id, 'related_post_id', true );

	// update admin response.
	update_post_meta( $post_id, 'admin_response', $post_data['admin_response'] );

	$notify_student = ( isset( $post_data['llms_notify_to_student'] ) ) ? 'yes' : false;

	// Notify to learner when the admin respond with contents...
	if ( $notify_student && empty( $admin_response ) ) {
		$post        = get_post( $post_id );
		$user_id     = get_post_meta( $post_id, 'related_user_id', true );
		$learner_obj = get_user_by( 'id', $user_id );
		if ( ! empty( $learner_obj->user_email ) ) {
			$to = $learner_obj->user_email;

			$link = get_permalink( $related_post_id );

			$site = get_bloginfo( 'name' );
			$from = get_bloginfo( 'admin_email' );

			$llms_s_n_email_template = get_option( 'llms_s_n_email_template' );

			if ( ! empty( $llms_s_n_email_template['student_subject'] ) && ! empty( $llms_s_n_email_template['student_body'] ) ) {

				$leader_id = learndash_notes_get_group_leader_id( $related_post_id, $user_id );

				$instructor_obj = false;

				if ( $leader_id ) {
					$instructor_obj = get_userdata( $leader_id );
				}

				$subject            = $llms_s_n_email_template['student_subject'];
				$learner_email_body = $llms_s_n_email_template['student_body'];

				$find    = array(
					'{student_first_name}',
					'{student_last_name}',
					'{student_user_name}',
					'{student_post_link}',
				);
				$replace = array(
					$learner_obj->first_name ?? '',
					$learner_obj->last_name ?? '',
					$learner_obj->user_login,
					$link,
				);

				if ( $instructor_obj ) {

					$find = array_merge(
						$find,
						array(
							'{instructor_first_name}',
							'{instructor_last_name}',
							'{instructor_user_name}',
						)
					);

					$replace = array_merge(
						$replace,
						array(
							$instructor_obj->first_name,
							$instructor_obj->last_name,
							$instructor_obj->user_login,
						)
					);

				}

				$filter_student_email_body = str_replace( $find, $replace, $learner_email_body );

				$headers   = array();
				$headers[] = "From: $site <$from>";
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				wp_mail( $to, $subject, $filter_student_email_body, $headers );
			}
		}
	}

	update_post_meta( $post_id, 'llms_note_unread', true );
	update_post_meta( $post_id, 'llms_notify_to_student', $notify_student );

}
