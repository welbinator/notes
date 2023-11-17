<?php
/**
 * Deletion Success Message shown in AJAX Responses
 *
 * @since    1.0.0
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class='alert alert-danger'>
	<?php echo esc_html( get_option( 'notedelsucc' ) ); ?>
</div>
