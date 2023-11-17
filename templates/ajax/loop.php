<?php
/**
 * Ajax Current Post Notes List Output
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $posts_array ) :

	foreach ( $posts_array as $post1 ) : ?>

		<div class="single-note">

			<div class="year">
				<?php echo esc_html( get_option( 'addedtext' ) ); ?>
				<?php echo esc_html( date_i18n( 'F d Y H:i:s', strtotime( $post1->post_date ) ) ); ?>
			</div>

			<div class="title">
				<?php
					$post_title = wp_strip_all_tags( $post1->post_title );
					echo esc_html( $post_title );
				?>
			</div>

			<div class="content">
				<?php
					$content = wpautop( $post1->post_content );
					echo wp_kses_post( $content );
				?>
			</div>

			<?php $was_notified = get_post_meta( $post1->ID, 'llms_notify_admin', true ); ?>
			<?php if ( (int) $was_notified === 1 ) : ?>

				<div class="inst-notif">
					<?php echo esc_html( get_option( 'instwasnotif' ) ); ?>
				</div>

			<?php endif; ?>

			<?php $admin_response = get_post_meta( $post1->ID, 'admin_response', true ); ?>
			<?php if ( $admin_response ) : ?>
				<div class="admin_response">
					<?php echo wp_kses_post( wpautop( $admin_response ) ); ?>
				</div>
			<?php endif; ?>

			<?php
			$llms_note_unread = get_post_meta( $post1->ID, 'llms_note_unread', true );

			if ( (int) $user_id === (int) $post1->post_author ) :
				?>

				<div class="del">

					<?php wp_nonce_field( "ld_student_notes_delete_note_{$post1->ID}", "ld_student_notes_delete_note_{$post1->ID}_nonce" ); ?>

					<?php if ( $llms_note_unread ) : ?>

						<?php wp_nonce_field( "ld_student_notes_read_note_{$post1->ID}", "ld_student_notes_read_note_{$post1->ID}_nonce" ); ?>

						<a class="mark-read" data-note-id="<?php echo esc_attr( $post1->ID ); ?>">
							<?php esc_html_e( 'Mark as read', 'learndash_student_notes' ); ?>
						</a>&nbsp;|&nbsp;

					<?php endif; ?>

					<a class="del-note" data-note-id="<?php echo esc_attr( $post1->ID ); ?>" onclick=" return (confirm('<?php echo esc_attr( get_option( 'delwarning' ) ); ?>'))" href="?post_id_del=<?php echo esc_attr( $post1->ID ); ?>">
						<?php echo esc_html( get_option( 'deletetext' ) ); ?>
					</a>&nbsp;|&nbsp;

					<a class="del-note-download" href="?note_id=<?php echo esc_attr( $post1->ID ); ?>&download_note=1&ld_student_notes_download_note_<?php echo esc_attr( $post1->ID ); ?>_nonce=<?php echo esc_attr( wp_create_nonce( "ld_student_notes_download_note_{$post1->ID}" ) ); ?>">
						<?php echo esc_html( get_option( 'downloadtext' ) ); ?>
					</a>

				</div>

			<?php endif; ?>

		</div>

		<?php
	endforeach;

else :
	?>
		<div class="alert alert-info">
			<?php echo esc_html( get_option( 'nonotesadded' ) ); ?>
		</div>
	<?php
endif;

// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
