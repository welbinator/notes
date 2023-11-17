<?php
/**
 * HTML for the Shortcodes How To
 *
 * @since 1.0.0
 * @package learndash_notes\admin\views
 */

defined( 'ABSPATH' ) || die();
?>

<ul style="list-style: disc; margin-left: 1.5em;">

	<li>

		<?php
			printf(
				// translators: %s is the [llms_add_new_note] shortcode wrapped in <code>.
				esc_html__( 'Add the %s shortcode into any post or page to let the user add a note.', 'learndash_student_notes' ),
				'<code>[llms_add_new_note]</code>'
			);
			?>
		<ul>

			<li>
				<?php esc_html_e( 'This shortcode will place the Add New Note form into the page or post content.', 'learndash_student_notes' ); ?>
			</li>

		</ul>

	</li>

	<li>

		<?php
			printf(
				// translators: %s is the [llms_add_new_note_popup] shortcode wrapped in <code>.
				esc_html__( 'Add the %s shortcode into any post or page to let the user add a note using a popup.', 'learndash_student_notes' ),
				'<code>[llms_add_new_note_popup]</code>'
			);
			?>
		<ul>

			<li>
				<?php esc_html_e( 'This shortcode will create a Add New Note icon into the page or post content. When clicked, that icon will open the Add New Note form in a popup.', 'learndash_student_notes' ); ?>
			</li>

		</ul>

	</li>

	<li>
		<?php
			printf(
				// translators: %s is the [llms_notes_list] shortcode wrapped in <code>.
				esc_html__( 'Add the %s shortcode into any post or page to let the user see and action their previous notes in that post or page.', 'learndash_student_notes' ),
				'<code>[llms_notes_list]</code>'
			);
			?>
	</li>

	<li>
		<?php
			printf(
				// translators: %s is the [llms_full_notes_list] shortcode wrapped in <code>.
				esc_html__( 'Add the %s shortcode into any post or page to let the user see and action their previous notes everywhere.', 'learndash_student_notes' ),
				'<code>[llms_full_notes_list]</code>'
			);
			?>
	</li>

</ul>
