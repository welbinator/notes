<?php
/**
 * Handles plugin upgrades.
 *
 * @since 1.0.0
 * @package learndash_notes\admin
 */

defined( 'ABSPATH' ) || die();

/**
 * Class LearnDash_Notes_Upgrade
 *
 * Handles plugin upgrades.
 *
 * @since 1.0.0
 */
class LearnDash_Notes_Upgrade {

	/**
	 * LearnDash_Notes_Upgrade constructor.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'check_upgrades' ) );

		$get_data = wp_unslash( $_GET );

		if ( isset( $get_data['nonce'] ) &&
			wp_verify_nonce( $get_data['nonce'], 'learndash_student_notes_upgrade' ) &&
			isset( $get_data['learndash_notes_upgrade'] ) &&
			! empty( $get_data['learndash_notes_upgrade'] )
		) {

			add_action( 'admin_init', array( $this, 'do_upgrades' ) );
		}
	}

	/**
	 * Checks for upgrades and migrations.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function check_upgrades() {

		$version = get_option( 'learndash_notes_version', 0 );

		if ( version_compare( $version, LEARNDASH_NOTES_VERSION ) === - 1 ) {
			update_option( 'learndash_notes_version', LEARNDASH_NOTES_VERSION );
		}

		$last_upgrade = get_option( 'learndash_notes_last_upgrade', 0 );

		foreach ( $this->get_upgrades() as $upgrade_version => $upgrade_callback ) {

			if ( version_compare( $last_upgrade, $upgrade_version ) === - 1 ) {

				add_action( 'admin_notices', array( $this, 'show_upgrade_nag' ) );
				break;
			}
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['learndash_notes_upgraded'] ) && ! empty( $_REQUEST['learndash_notes_upgraded'] ) ) {

			add_action( 'admin_notices', array( $this, 'show_upgraded_message' ) );

		}

	}

	/**
	 * Runs upgrades.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function do_upgrades() {

		$last_upgrade = get_option( 'learndash_notes_last_upgrade', 0 );

		foreach ( $this->get_upgrades() as $upgrade_version => $upgrade_callback ) {

			if ( version_compare( $last_upgrade, $upgrade_version ) === - 1 ) {

				call_user_func( $upgrade_callback );
				update_option( 'learndash_notes_last_upgrade', $upgrade_version );
			}
		}

		$url = remove_query_arg(
			array(
				'learndash_notes_upgrade',
				'nonce',
			)
		);

		$url = add_query_arg(
			array(
				'learndash_notes_upgraded' => true,
			),
			$url
		);

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * Returns an array of all versions that require an upgrade.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  array  Upgrade Version => Callback
	 */
	public function get_upgrades() {

		return array(
			'1.0.0' => array( $this, 'upgrade_1_0_0' ),
		);
	}

	/**
	 * Displays upgrade nag.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function show_upgrade_nag() {
		?>
		<div class="notice notice-warning">
			<p>
				<?php esc_html_e( 'Notes by LearnDash needs to upgrade the database. It is strongly recommended you backup your database first.', 'learndash_student_notes' ); ?>
				<a href="
					<?php
					echo esc_attr(
						add_query_arg(
							array(
								'learndash_notes_upgrade' => true,
								'nonce'                   => wp_create_nonce( 'learndash_student_notes_upgrade' ),
							)
						)
					);
					?>
				"
					class="button button-primary">
					<?php echo esc_html_x( 'Upgrade', 'Run database upgrade button text', 'learndash_student_notes' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Displays the upgraded complete message.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function show_upgraded_message() {
		?>
		<div class="notice notice-success">
			<p>
				<?php esc_html_e( 'Notes by LearnDash has successfully upgraded!', 'learndash_student_notes' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Upgrade script for v1.0.0
	 *
	 * Handles migrating some options from the old "Disable to enable" logic some toggles had before the re-release under LearnDash
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function upgrade_1_0_0() {

		$toggle_fields = array(
			'remtoolbarcheck' => 'ld_student_notes_add_editor_toolbar',
			'sumofcharscheck' => 'ld_student_notes_show_editor_character_count',
			'notifinstcheck'  => 'ld_student_notes_add_notify_instructor',
		);

		foreach ( $toggle_fields as $old_key => $new_key ) {

			if ( empty( get_option( $old_key ) ) ) {

				update_option( $new_key, 'on' );

			} else {

				delete_option( $old_key );

			}
		}

	}

}
