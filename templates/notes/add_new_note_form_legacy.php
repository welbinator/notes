<?php
/**
 * Outputs the New Note Form
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<style>
	#learndash-notes-form .checkbox{margin-left:10px;margin-top:5px;}
	#notes-submit-btn{margin-top:10px;margin-bottom:10px;}
</style>

<form action="#"  id="learndash-notes-form" method="post" enctype="multipart/form-data">

	<div class="student-notes-layer">
		<img src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" />
	</div>

	<div class="form-group">

		<span class="title">
			<input type="text" name="llms_note_title" class="llms_note_title" placeholder="<?php echo esc_attr( get_option( 'new_note_title' ) ); ?>"/>
		</span>

	</div>

	<div class="form-group">

		<?php

			$content   = '';
			$editor_id = 'llms_note_text';
			$settings  = array(
				'media_buttons' => false,
				'textarea_rows' => 10,
				'required'      => 'required',
				'quicktags'     => false,
			);

			wp_editor( $content, $editor_id, $settings );

			?>

		<div id="post-status-info"></div>

	</div>

	<div class="checkbox">

		<?php

			$course_id   = 0;
			$groups      = array();
			$user_groups = array();

		if ( function_exists( 'learndash_get_course_id' ) ) {
			$course_id = learndash_get_course_id( get_the_ID() );
		}

		if ( function_exists( 'learndash_get_course_groups' ) ) {
			$groups = learndash_get_course_groups( $course_id );
		}

		if ( function_exists( 'learndash_get_users_group_ids' ) ) {
			$user_groups = learndash_get_users_group_ids( get_current_user_id() );
		}

			$groups = array_intersect( $groups, $user_groups );

		if ( ! empty( get_option( 'ld_student_notes_add_notify_instructor' ) ) && $course_id && ! empty( $groups ) ) :

			?>
				<label>
					<input class="" type="checkbox" name="llms_notify_admin" />
				<?php echo esc_html( get_option( 'notifinst' ) ); ?>
				</label>

		<?php endif; ?>

	</div>

	<input type="hidden" name="related_post_id"  value="<?php echo esc_attr( get_the_ID() ); ?>" />

	<input type="hidden" name="related_post_type"  value="<?php echo esc_attr( get_post_type( get_the_ID() ) ); ?>" />

	<input type="hidden" name="related_user_id"  value="<?php echo esc_attr( get_current_user_id() ); ?>" />

	<?php wp_nonce_field( 'ld_student_notes_add_note', 'ld_student_notes_add_note_nonce' ); ?>

	<center>
		<button type="submit" id="notes-submit-btn" class="btn btn-primary notes-submit-btn">
			<?php echo esc_html( get_option( 'notessubmitbutton' ) ); ?>
		</button>
	</center>

</form>

<script type="text/javascript">

	jQuery(document).ready(function ($) {
		// inject the html
		jQuery("#post-status-info").after("<div class='llms_note_charcount' style='border: 1px solid #e5e5e5; border-top:0; display: block; background-color: #F7F7F7; padding: 0.3em 0.7em;'><?php echo esc_html( get_option( 'sumofchars' ) ); ?> <b id=\"ilc_excerpt_counter\"></b> <div id=\"ilc_excerpt_counter_1\"><?php echo esc_html( get_option( 'sumofchars' ) ); ?>: <b id=\"ilc_live_counter\">()</b></div></div>");

	});

	// count on load
	window.addEventListener("load", tiny_text_data_counter ) ;
	function tiny_text_data_counter() {
			//setTimeout(function() {
			setInterval(function() {
				//var tiny_text_data	=	tinymce.get('content');
				var tiny_text_data	=	tinymce.get('llms_note_text').getContent();
				if(tiny_text_data){
					//cont = tiny_text_data.getContent().replace(/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])|(\s+)/ig,'');
					cont = tiny_text_data.replace(/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])|(\s+)/ig,'');
					jQuery("#learndash-notes-form #ilc_excerpt_counter").text(cont.length);
				}

			}, 300);
		}

</script>

<?php
	$rmtbchk      = get_option( 'ld_student_notes_add_editor_toolbar' );
	$rmcharcntchk = get_option( 'ld_student_notes_show_editor_character_count' );
?>

<style>

	<?php if ( empty( $rmtbchk ) ) : ?>
		#wp-llms_note_text-editor-container .mce-top-part{display:none !important;}
	<?php endif; ?>

	<?php if ( empty( $rmcharcntchk ) ) : ?>
		.llms_note_charcount{display:none !important;}
	<?php endif; ?>

	#ilc_excerpt_counter_1{display:none;}

</style>

<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
