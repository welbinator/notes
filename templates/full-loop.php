<?php
/**
 * Full Notes list
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

<div class="all-notes-container">

	<div id="accordion-Historical">

		<div class="student-notes-layer">
			<img src="https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif" />
		</div>

		<?php

		$icon_class = get_option( 'bell_icon', 'fa-bell' );
		if ( empty( $icon_class ) ) {
			$icon_class = 'fa-bell';
		}

		$bell_icon_color = get_option( 'bell_icon_color', 'red' );
		if ( empty( $icon_class ) ) {
			$bell_icon_color = 'color: red';
		} else {
			$bell_icon_color = 'color: ' . $bell_icon_color;
		}

		?>

		<h3>

			<?php echo esc_html( get_option( 'allmynotes', 'learndash_student_notes' ) ); ?> <span class=" show-unread-bell"><i class="fa <?php echo esc_attr( $icon_class ); ?>" style="<?php echo esc_attr( $bell_icon_color ); ?>"></i></span>

			<span class="llmssn-icon-arrow"></span>

		</h3>

		<div class="accordian-content">
			<?php require_once LEARNDASH_NOTES_TEMPLATE_DIR . 'ajax/full-loop.php'; ?>
		</div>

	</div>

</div>

<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
