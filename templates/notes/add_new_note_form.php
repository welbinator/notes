<?php
/**
 * Outputs the New Note Popup Form
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

?>

<style type="text/css">
	/*Draggable*/
	@media only screen and (max-width: 600px){
		.learndash_notes_draggable,#learndash-notes-form-popup,#learndash_notes_draggable_icon_view{
			top:15vh;
			right:5vw;
		}
	}
	.learndash_notes_draggable,#learndash-notes-form-popup{
		max-width:90vw;
		max-height:90vh;
	}
	@media only screen and (max-height: 600px){
		.learndash_notes_draggable,#learndash-notes-form-popup,#learndash_notes_draggable_icon_view{
			top:15vh;
			right:5vw;
		}
	}
	.learndash_notes_draggable{
		z-index:9999;
		text-align:center;
		position:fixed;
		width:100%;
	}
	.learndash_notes_draggable_header{
		padding:10px;
		cursor:move;
		z-index:10;
		background-color: <?php echo esc_attr( get_option( 'notes_primary_color' ) ); ?>;
		color:#fff;
		border-top-left-radius:10px;
		border-top-right-radius:10px;
		display: flex;
		justify-content: space-between;
		gap: 10px;
	}
	.learndash_notes_draggable_header .title {
		flex-grow: 1;
	}
	.learndash_notes_draggable_header .title input[type=text]{
		padding:5px 10px;
	}
	#learndash_notes_draggable_content{
		position:relative;
	}
	#learndash_notes_draggable_content,#learndash-notes-form-popup{
		border-bottom-left-radius:10px;
		border-bottom-right-radius:10px;
	}
	/*NOSELECT*/
	.learndash_notes_draggable_wrapper{
		position:absolute;
		top:0px;
		left:0px;
	}
	.learndash_notes_draggable *{
		-webkit-touch-callout:none;
		-webkit-user-select:none;
		-khtml-user-select:none;
		-moz-user-select:none;
		-ms-user-select:none;
		user-select:none;
		z-index:9999;
	}
	#learndash_notes_draggable_icon_view{
		z-index:9999;
		display:none;
	}
	#learndash_notes_draggable_icon_view i.fa{
		color: <?php echo esc_attr( get_option( 'floating_icon_color' ) ); ?>;
		font-size: <?php echo esc_attr( get_option( 'floating_icon_size' ) ); ?>;
	}
	.learndash_notes_draggable_header .icons{
		float:right;
		cursor:pointer;
		z-index:9999;
	}
	.bubble{
		position:absolute;
		right:0px;
		top:0px;
		z-index:9999;
		-moz-border-radius:50%;
		-webkit-border-radius:50%;
		border-radius:50%;
		cursor:pointer;
		text-align:center;
		line-height:24px;
		padding:10px;
	}
	.fa.fa-window-minimize{
		position:relative;
		top:0px;
		z-index:9999;
	}
	div#mceu_33-body{
		display:flex;
		flex-wrap:wrap;
	}
	#learndash-notes-form-popup{
		border:dashed 1px #CCC;
		width:600px;
		height:417px;
		padding:5px;
		margin:5px;
		font:13px Arial;
		cursor:move;
		float:left;
		position:relative;
		z-index:9999;
		min-width:300px;
	}
	.notes-bottom{
		position:absolute;
		bottom:0px;
		height:90px;
		width:100%;
	}
	.ui-resizable-e,.ui-resizable-s{
		display:none !important;
	}
	.ui-resizable-se{
		z-index:9999 !important;
		right:5px !important;
		bottom:5px !important;
	}
	.wp-editor-wrap{
		cursor:default;
	}
</style>

<div id="learndash_notes_draggable_wrapper" class="learndash_notes_draggable_wrapper">

	<div id="learndash_notes_draggable" class="learndash_notes_draggable">

		<form action="#" id="learndash-notes-form-popup" method="post" enctype="multipart/form-data">

			<div id="student-notes-layer" class="student-notes-layer">
				<img src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" />
			</div>

			<div id="learndash_notes_draggable_icon_view">

				<div class="bubble">

					<?php

						$icon_class = get_option( 'add_new_note_floating_icon', 'fa-window-maximize' );

					if ( empty( $icon_class ) ) {
						$icon_class = 'fa-window-maximize';
					}

					?>

					<i class="fa <?php echo esc_attr( $icon_class ); ?>"></i>

				</div>

			</div>

			<div id="learndash_notes_draggable_header" class="learndash_notes_draggable_header">

				<span class="title">

					<input type="text" name="llms_note_title" placeholder="<?php echo esc_attr( get_option( 'new_note_title' ) ); ?>" />

				</span>

				<span class="icons minimize" id="learndash_notes_minimize">
					<i class="fa fa-window-minimize" style="font-size:24px"></i>
				</span>

			</div>

			<div id="notes-form-group" class="form-group">

				<?php

					$content   = '';
					$editor_id = 'llms_note_text_popup';
					$settings  = array(
						'media_buttons' => false,
						'textarea_rows' => 1,
						'required'      => 'required',
						'quicktags'     => false,
					);

					wp_editor( $content, $editor_id, $settings );

					?>

				<div id="post-status-info-popup"></div>

				<div id="notes-bottom" class="notes-bottom">

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

					<input type="hidden" name="related_post_id" value="<?php echo esc_attr( get_the_ID() ); ?>" />

					<input type="hidden" name="related_post_type" value="<?php echo esc_attr( get_post_type( get_the_ID() ) ); ?>" />

					<input type="hidden" name="related_user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />

					<?php wp_nonce_field( 'ld_student_notes_add_note', 'ld_student_notes_add_note_nonce' ); ?>

					<button type="submit" id="notes-submit-btn" class="btn btn-primary notes-submit-btn">
						<?php echo esc_html( get_option( 'notessubmitbutton' ) ); ?>
					</button>

				</div>

			</div>

		</form> 

	</div>

</div>

<script type="text/javascript">
	var element_pos = 0; // POSITION OF THE NEWLY CREATED ELEMENTS.
	var learndash_notes_draggable_mode = localStorage.getItem('learndash_notes_draggable_mode');
	if (learndash_notes_draggable_mode != null && learndash_notes_draggable_mode != "" && learndash_notes_draggable_mode == "minimize") {
		jQuery(".learndash_notes_draggable_header").css("visibility", "hidden");
		jQuery("#learndash-notes-form-popup .form-group").css("visibility", "hidden");
		jQuery("#learndash_notes_draggable_icon_view").css("display", "block");
		jQuery("#learndash-notes-form-popup").css("visibility", "hidden");
		jQuery("#learndash_notes_draggable_icon_view").css("visibility", "visible");
		jQuery(".mce-resizehandle").css("visibility", "hidden");
		jQuery(".learndash_notes_draggable").css("visibility", "hidden");
	} else {
		jQuery(".learndash_notes_draggable_header").css("visibility", "visible");
		jQuery("#learndash-notes-form-popup").css("visibility", "visible");
		jQuery("#learndash-notes-form-popup .form-group").css("visibility", "visible");
		jQuery("#learndash_notes_draggable_icon_view").css("display", "none");
		jQuery(".mce-container-body .mce-resizehandle").css("visibility", "visible");
		jQuery(".learndash_notes_draggable").css("visibility", "hidden");
	}
	var learndash_notes_draggable_points = JSON.parse(localStorage.getItem('learndash_notes_draggable_points'));
	if (learndash_notes_draggable_points != null && learndash_notes_draggable_points.x && learndash_notes_draggable_points.y) {
		var elmnt = jQuery("#learndash-notes-form-popup");
		elmnt.css("top", learndash_notes_draggable_points.x);
		elmnt.css("left", learndash_notes_draggable_points.y);
	}
	var learndash_notes_draggable_resize = JSON.parse(localStorage.getItem('learndash_notes_draggable_resize'));
	if (learndash_notes_draggable_resize != null && learndash_notes_draggable_resize.width && learndash_notes_draggable_resize.height) {
		var elmnt = jQuery("#learndash-notes-form-popup");
		elmnt.css("width", learndash_notes_draggable_resize.width);
		elmnt.css("height", learndash_notes_draggable_resize.height);
	}

	jQuery(document).ready(function($) {
		var llms_note_text_popup_iframe_loaded = false;
		var llms_note_text_popup_ifr_interval = setInterval(function() {
			if ($('#llms_note_text_popup_ifr').length) {
				$("#llms_note_text_popup_ifr").css("height", localStorage.getItem('learndash_notes_draggable_iframe_height'));
				$('#llms_note_text_popup_ifr').on("load", function() {
					console.log("in timeout");
					var learndash_notes_draggable_h = $("#learndash_notes_draggable").outerHeight();
					var learndash_notes_draggable_header_h = $("#learndash_notes_draggable_header").outerHeight();
					var wp_llms_note_text_popup_wrap_h = $("#wp-llms_note_text_popup-wrap").outerHeight();
					var llms_note_charcount_h = $("#notes-form-group .llms_note_charcount").outerHeight();
					var notes_bottom_h = $("#notes-bottom").outerHeight();
					var ifr_max_height = learndash_notes_draggable_h - (learndash_notes_draggable_header_h + llms_note_charcount_h + notes_bottom_h);
					console.log(ifr_max_height);
					console.log("in if");
					llms_note_text_popup_iframe_loaded = true;
					var frm_min_height = (learndash_notes_draggable_header_h + wp_llms_note_text_popup_wrap_h + llms_note_charcount_h + notes_bottom_h);
					console.log(frm_min_height);
					if (learndash_notes_draggable_mode != null && learndash_notes_draggable_mode != "" && learndash_notes_draggable_mode == "maximize") {
						jQuery(".mce-container-body .mce-resizehandle").css("visibility", "visible");
					}
				});
			}
			if (llms_note_text_popup_iframe_loaded) {
				console.log("in clear");
				clearInterval(llms_note_text_popup_ifr_interval);
			}
		}, 1000);

		$("#learndash-notes-form-popup").draggable({
				containment: 'body',
				cancel: '#notes-form-group, input[name="llms_note_title"], .ui-resizable-se',
				drag: function(event, ui) {
					var pos = ui.position;
					var $this = $(this);
					var thisPos = $this.position();
				},

				stop: function(e, ui) {
					var $this = $(this);
					var thisPos = $this.position();
					var percLeft = (ui.position.left / ui.helper.parent().width()) * 100;
					var percTop = ui.position.top / ui.helper.parents('html').height() * 100;
					ui.helper.css('left', percLeft + '%');
					localStorage.setItem('learndash_notes_draggable_points', JSON.stringify({
						x: thisPos.top,
						y: percLeft + '%'
					}));
				}
			})

			.resizable({
				resize: function(event, ui) {
					var learndash_notes_draggable_h = ui.size.height; //$("#learndash_notes_draggable").height();
					var learndash_notes_draggable_header_h = $("#learndash_notes_draggable_header").outerHeight();
					var wp_llms_note_text_popup_wrap_h = $("#wp-llms_note_text_popup-wrap").outerHeight();
					var llms_note_charcount_h = $("#notes-form-group .llms_note_charcount").outerHeight();
					var notes_bottom_h = $("#notes-bottom").outerHeight();
					var ifr_max_height = learndash_notes_draggable_h - (learndash_notes_draggable_header_h + llms_note_charcount_h + notes_bottom_h);
					$("#llms_note_text_popup_ifr").css("height", (ifr_max_height - (wp_llms_note_text_popup_wrap_h / 2)) + "px");
					localStorage.setItem('learndash_notes_draggable_iframe_height', (ifr_max_height - (wp_llms_note_text_popup_wrap_h / 2)) + "px");
					var frm_min_height = (learndash_notes_draggable_header_h + wp_llms_note_text_popup_wrap_h + llms_note_charcount_h + notes_bottom_h);
					frm_min_height += 30;
					$("#learndash-notes-form-popup").css("min-height", frm_min_height + "px");
				},
				stop: function(e, ui) {
					localStorage.setItem('learndash_notes_draggable_resize', JSON.stringify({
						width: ui.size.width,
						height: ui.size.height
					}));
				}
			});

		$("#learndash_notes_minimize").click(function() {
			$("#learndash-notes-form-popup").css("visibility", "hidden");
			$(".learndash_notes_draggable_header").css("visibility", "hidden");
			$("#learndash-notes-form-popup .form-group").css("visibility", "hidden");
			$("#learndash_notes_draggable_icon_view").css("display", "block");
			$("#learndash_notes_draggable_icon_view").css("visibility", "visible");
			$(".mce-resizehandle").css("visibility", "hidden");
			$(".learndash_notes_draggable").css("visibility", "hidden");
			localStorage.setItem('learndash_notes_draggable_mode', 'minimize');
		});

		$("#learndash_notes_draggable_icon_view").click(function() {
			$(".learndash_notes_draggable_header").css("visibility", "visible");
			$("#learndash-notes-form-popup .form-group").css("visibility", "visible");
			$("#learndash_notes_draggable_icon_view").css("display", "none");
			$("#learndash-notes-form-popup").css("visibility", "visible");
			$("#learndash_notes_draggable_icon_view").css("visibility", "visible");
			$(".mce-resizehandle").css("visibility", "visible");
			$(".learndash_notes_draggable").css("visibility", "hidden");
			localStorage.setItem('learndash_notes_draggable_mode', 'maximize');
		});

		// inject the html
		$("#post-status-info-popup").after("<div class='llms_note_charcount' style='border: 1px solid #e5e5e5; border-top:0; display: block; background-color: #F7F7F7; padding: 0.3em 0.7em;'><?php echo esc_html( get_option( 'sumofchars' ) ); ?> <b id=\"ilc_excerpt_counter\"></b> <div id=\"ilc_excerpt_counter_1\"><?php echo esc_html( get_option( 'sumofchars' ) ); ?>: <b id=\"ilc_live_counter\">()</b></div></div>");

		// count on load
		window.addEventListener("load", tiny_text_data_popup_counter);

		function tiny_text_data_popup_counter() {
			setInterval(function() {
				var tiny_text_data_popup = tinymce.get('llms_note_text_popup').getContent();
				if (tiny_text_data_popup) {
					cont = tiny_text_data_popup.replace(/(<[a-zA-Z\/][^<>]*>|\[([^\]]+)\])|(\s+)/ig, '');
					$("#learndash-notes-form-popup #ilc_excerpt_counter").text(cont.length);
				}
			}, 300);
		}

		$(window).resize(function() {
			var windowsize = $(window).width();
			if (windowsize < 768) {
				$("#learndash-notes-form-popup").css("right", 0).css("left", 'unset');
				var learndash_notes_draggable_points = JSON.parse(localStorage.getItem('learndash_notes_draggable_points'));
				localStorage.setItem('learndash_notes_draggable_points', JSON.stringify({
					x: learndash_notes_draggable_points.x,
					y: 'unset'
				}));
			}
		});
	});
</script>

<?php
	$rmtbchk      = get_option( 'ld_student_notes_add_editor_toolbar' );
	$rmcharcntchk = get_option( 'ld_student_notes_show_editor_character_count' );
?>

<style>

	<?php if ( empty( $rmtbchk ) ) : ?>
		#wp-llms_note_text-editor-container .mce-top-part, #mceu_26-body{display:none !important;}
	<?php endif; ?>

	<?php if ( empty( $rmcharcntchk ) ) : ?>
		.llms_note_charcount{display:none !important;}
	<?php endif; ?>

	#ilc_excerpt_counter_1{display:none;}

</style>

<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
