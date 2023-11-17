<?php
/**
 * HTMl for the Notification Icon How To
 *
 * @since 1.0.0
 * @package learndash_notes\admin\views
 */

defined( 'ABSPATH' ) || die();
?>

<p>
	<?php esc_html_e( 'Notes by LearnDash has a notification icon that you can add to your websites header menu.', 'learndash_student_notes' ); ?>
</p>

<p>
	<?php esc_html_e( 'To create this menu item in your website header, perform the following:', 'learndash_student_notes' ); ?>
</p>

<ol>
	<li>
		<?php esc_html_e( 'Visit your dashboard > Appearance > Menus page choose the menu you wold like to add the icon into.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Select the "Custom Links" menu item located in your Add menu items column.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php
			printf(
				// translators: %s is the [llms_full_notes_list] shortcode wrapped in <code>.
				esc_html__( 'Add the link for the page that you added the %s shortcode to into the URL field.', 'learndash_student_notes' ),
				'<code>[llms_full_notes_list]</code>'
			);
			?>
	</li>
	<li>
		<?php esc_html_e( 'Give this menu item a Navigation label to be used when the icon is hovered over with a mouse cursor.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Click on the [Add to Menu] button to add this to th ebottom of your menu.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Click on the down-arrow of this menu item to open its properties.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php
			printf(
				// translators: %s is the llmssn-bell-icon class used to identify the Menu Item, surrounded by <code>.
				esc_html__( 'Add %s into the CSS Classes (optional) field.', 'learndash_student_notes' ),
				'<code>llmssn-bell-icon</code>'
			);
			?>
		<ul style="list-style: disc; margin-left: 1.5em;">
			<li>
				<?php esc_html_e( 'If this field does not show, enable it by clicking "Screen Options" at the top of your screen and checking the box for "CSS Classes".', 'learndash_student_notes' ); ?>
			</li>
		</ul>
	</li>
	<li>
		<?php esc_html_e( 'Collapse this menu item and drag it to your desired location in the menu.', 'learndash_student_notes' ); ?>
	</li>
	<li>
		<?php esc_html_e( 'Click on the [Save Menu] button to complete this operation.', 'learndash_student_notes' ); ?>
	</li>
</ol>

<p>
	<?php esc_html_e( 'With this completed, you can now visit the Styling tab above to change the default icon and its color.', 'learndash_student_notes' ); ?>
</p>
