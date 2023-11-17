<?php
/**
 * Notes Post Table Columns
 *
 * @since    3.9.6
 *
 * @package learndash_student_notes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notes Post Table Columns
 */
class LearnDash_Notes_Admin_Post_Table_Notes {

	/**
	 * Constructor
	 *
	 * @return  void
	 * @since    3.9.6
	 */
	public function __construct() {
		add_filter( 'manage_llms_student_notes_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_student_notes_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'filters' ), 10 );
		add_action( 'pre_get_posts', array( $this, 'query_posts_filter' ), 10, 1 );
		add_filter( 'months_dropdown_results', array( $this, 'default_date_filter' ), 10, 2 );
	}

	/**
	 * Add Custom lesson Columns
	 *
	 * @param   array $columns  array of default columns.
	 * @return  array
	 * @since    3.9.6
	 */
	public function add_columns( $columns ) {
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'excerpt'           => __( 'Note Excerpt', 'learndash_student_notes' ),
			'author'            => __( 'Learner Name', 'learndash_student_notes' ),
			'related_post_type' => __( 'Type', 'learndash_student_notes' ),
			'related_post'      => __( 'Title', 'learndash_student_notes' ),
			'date'              => __( 'Date', 'learndash_student_notes' ),
			'actions'           => __( 'Actions', 'learndash_student_notes' ),
		);
		return $columns;
	}

	/**
	 * Manage content of custom lesson columns
	 *
	 * @param  string $column   column key/name.
	 * @param  int    $post_id  WP Post ID of the lesson for the row.
	 * @return void
	 * @since   3.9.6
	 * @version  3.9.6
	 */
	public function manage_columns( $column, $post_id ) {

		switch ( $column ) {
			case 'excerpt':
				echo wp_kses_post( wp_trim_words( get_the_content( $post_id ), 40, '...' ) );
				break;
			case 'related_post_type':
				$related_post_type = get_post_meta( $post_id, 'related_post_type', true );
				echo esc_html( $related_post_type );
				break;
			case 'related_post':
				$related_post_id = absint( get_post_meta( $post_id, 'related_post_id', true ) );
				$edit_link       = get_the_permalink( $related_post_id );
				if ( ! empty( $related_post_id ) ) {
					echo wp_kses_post( sprintf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $related_post_id ) ) );
				}
				break;
			case 'actions':
				$edit_link  = '#';
				$edit_link  = get_edit_post_link( $post_id );
				$edit_link .= '#Respond';
				$edit_link2 = admin_url( 'post.php?post_type=llms_student_notes&post=' );

				/* Show Responded if instructor has responded */
				$admin_response = get_post_meta( $post_id, 'admin_response', true );
				$content        = $admin_response;
				if ( ! empty( $content ) ) {
					echo wp_kses_post( sprintf( '<a class="button primary" style="background-color:green; color: #ffffff;" href="%1$s">%2$s</a>', $edit_link, '<b>' . __( 'Responded', 'learndash_student_notes' ) . '</b>' ) );
				} else {
					echo wp_kses_post( sprintf( '<a class="button primary" style="padding-left:18px;padding-right:18px;" href="%1$s">%2$s</a>', $edit_link, __( 'Respond', 'learndash_student_notes' ) ) );
				}
				break;

		}

	}

	/**
	 * Add filters
	 *
	 * @param   string $post_type  Current Post Type.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function filters( $post_type ) {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$get_data = wp_unslash( $_GET );

		if ( 'llms_student_notes' === $post_type ) {

			wp_nonce_field( 'learndash_notes_admin_filter', 'learndash_notes_admin_filter_nonce' );

			?>
			<?php $selected_course_id = isset( $get_data['filter_course_id'] ) ? sanitize_text_field( $get_data['filter_course_id'] ) : ''; ?>
			<select name="filter_course_id" id="filter_course_id">
				<option value="">
				<?php
				echo esc_html(
					sprintf(
					// translators: %s is the LearnDash custom label for Courses.
						__( 'All %s', 'learndash_student_notes' ),
						learndash_get_custom_label( 'courses' )
					)
				);
				?>
				</option>
				<?php foreach ( $this->get_posts() as $course_id ) : ?>
					<option value="<?php echo esc_attr( $course_id ); ?>" <?php selected( $course_id, $selected_course_id ); ?> ><?php echo esc_html( get_the_title( $course_id ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<script>
			/* auto submit on course filter change */
			jQuery( document ).ready(function($) {
				$('#filter_course_id').change(function(){
					$('#filter_lesson_id').val('');
					$('#posts-filter').submit();
				});
			});
			</script>

			<?php

			$all_less           = $this->get_posts( 'sfwd-lessons' );
			$filter_all_lessons = array();
			foreach ( $all_less as $lesson_id ) {
				$parent_id = absint( get_post_meta( $lesson_id, 'course_id', true ) );
				if ( (int) $selected_course_id === (int) $parent_id ) {

					$filter_all_lessons[] = $lesson_id;

				}
			}

			?>
			<?php $selected_lesson_id = isset( $get_data['filter_lesson_id'] ) ? sanitize_text_field( $get_data['filter_lesson_id'] ) : ''; ?>
			<select name1="filter_lesson_id" id="filter_lesson_id" style='display:none;'>
				<option value=""><?php esc_html_e( 'All Lessons ', 'learndash_student_notes' ); ?></option>
			<?php foreach ( $filter_all_lessons as $lesson_id ) { ?>
					<option value="<?php echo esc_attr( $lesson_id ); ?>" <?php selected( $lesson_id, $selected_lesson_id ); ?> ><?php echo esc_html( get_the_title( $lesson_id ) ); ?></option>
				<?php } ?>
			</select>

			<?php
			// date filter.
			global $wpdb ,$wp_locale;
			$months = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
					FROM $wpdb->posts
					WHERE post_type = %s
						AND post_status != 'auto-draft'
					ORDER BY post_date DESC
					",
					$post_type
				)
			);

			$month_count = count( $months );
			if ( ! $month_count || ( 1 === (int) $month_count && 0 === (int) $months[0]->month ) ) {
				return;
			}

			$m = isset( $get_data['m'] ) ? (int) $get_data['m'] : 0;
			?>
			<label for="filter-by-date" class="screen-reader-text"><?php esc_html_e( 'Filter by date', 'learndash_student_notes' ); ?></label>

			<select name="m" id="filter-by-date">
				<option <?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'learndash_student_notes' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {

				if ( 0 === (int) $arc_row->year ) {
					continue;
				}

				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;
				echo sprintf(
					"<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					sprintf( '%1$s %2$d', esc_html( $wp_locale->get_month( $month ) ), esc_html( $year ) )
				);

			}
			?>
			</select>
					<?php

		}
	}

	/**
	 * Get posts
	 *
	 * @param   string $post_type  Post Type.
	 *
	 * @since   1.0.0
	 * @return  array               Array of the first Column for the query results. Given this is wp_posts, this will be ID
	 */
	public function get_posts( $post_type = 'sfwd-courses' ) {

		global $wpdb;

		$sql = $wpdb->prepare(
			'SELECT * FROM %s WHERE post_status = %s AND post_type = %s ORDER BY ID DESC',
			$wpdb->posts,
			'publish',
			(string) $post_type
		);

		$results = $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT * FROM $wpdb->posts WHERE post_status = %s AND post_type = %s ORDER BY ID DESC
				",
				'publish',
				(string) $post_type
			)
		);

		return $results;
	}

	/**
	 * Change query on filter submit
	 *
	 * @param   WP_Query $query  Current WP_Query object.
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function query_posts_filter( $query ) {

		$get_data = wp_unslash( $_GET );

		global $pagenow;
		$type = 'post';

		if ( isset( $get_data['post_type'] ) ) {
			$type = $get_data['post_type'];
		}

		if ( 'llms_student_notes' === $type && is_admin() && wp_verify_nonce( 'learndash_notes_admin_filter_nonce', 'learndash_notes_admin_filter' ) && $pagenow === 'edit.php' && isset( $get_data['filter_course_id'] ) && ! empty( $get_data['filter_course_id'] ) ) {

			$selected_course_id = isset( $get_data['filter_course_id'] ) ? sanitize_text_field( $get_data['filter_course_id'] ) : '';

			$all_notes = $this->get_posts( 'llms_student_notes' );

			$note_ids = array();

			// create array of matched notes.
			foreach ( $all_notes  as $single_note ) {
				$s_n = absint( get_post_meta( $single_note, 'related_post_id', true ) );

				// check for any related id has parent course of current course.
				$cid_rt_post = get_post_meta( $s_n, 'ld_course_' . $selected_course_id, true );

				if ( (int) $cid_rt_post === (int) $selected_course_id ) {
					$note_ids[] = $single_note;
				}

				// check if note is added to main course.
				if ( (int) $s_n === (int) $selected_course_id ) {
					$note_ids[] = $single_note;
				}
			}

			// Update WP_Query accordingly.
			if ( ! empty( $note_ids ) ) {

				$note_ids = array_unique( $note_ids );

				$l_id = $note_ids;
				$l_id = 'novalue';

				if ( is_array( $note_ids ) ) {
					$l_id = implode( ',', $note_ids );
				}

				if ( $l_id ) {
					$query->query_vars['post__in'] = $note_ids;
				}

				if ( empty( $l_id ) ) {
					$query->query_vars['post__in'] = array( 0 );
				}
			} else {
				// if no lesson on course, set to no quiz found.
				$query->query_vars['post__in'] = array( 0 );
			}
		}
	}

	/**
	 * Hide default date filter  only on llms_student_notes post types
	 *
	 * @param   array  $months     Array of the months drop-down query results.
	 * @param   string $post_type  Current Post Type.
	 *
	 * @since   1.0.0
	 * @return  array              Array of the months drop-down query results
	 */
	public function default_date_filter( $months, $post_type ) {
		if ( $post_type === 'llms_student_notes' ) {
			return array();
		}
		return $months;
	}
}

return new LearnDash_Notes_Admin_Post_Table_Notes();
