<?php
/**
 * Notes list for the Current Post
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="all-notes-container all-my-post-notes">

	<div id="accordion-Historical">

		<div class="student-notes-layer">
			<img src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" />
		</div>

		<h3>
			<?php echo esc_html( get_option( 'myhistnotes' ) ); ?>
			<span class="llmssn-icon-arrow"></span>
		</h3>

		<div class="accordian-content">
			<?php require_once LEARNDASH_NOTES_TEMPLATE_DIR . 'ajax/loop.php'; ?>
		</div>
	</div>
</div>
