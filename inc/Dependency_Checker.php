<?php
/**
 * Plugin service provider class file.
 *
 * @package LearnDash_Notes\Utility
 */

namespace LearnDash_Notes\Utility;

/**
 * Plugin Dependency checking class.
 *
 * @since 1.0.3
 */
class Dependency_Checker {

	/**
	 * Instance of our class.
	 *
	 * @var object $instance
	 */
	private static $instance;

	/**
	 * The displayed message shown to the user on admin pages.
	 *
	 * @var string $admin_notice_message
	 */
	private $admin_notice_message = '';

	/**
	 * The array of plugin) to check Should be key => label paird. The label can be anything to display
	 *
	 * @var array $plugins_to_check
	 */
	private $plugins_to_check = array();

	/**
	 * Array to hold the inactive plugins. This is populated during the plugins_loaded action via the function call to check_inactive_plugin_dependency()
	 *
	 * @var array $plugins_inactive
	 */
	private $plugins_inactive = array();

	/**
	 * Plugin Dependency checking class.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  void
	 */
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 11 );

	}

	/**
	 * Returns the instance of this class or new one.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  LearnDash_Notes\Utility\Dependency_Checker Instance
	 */
	public static function get_instance() {

		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;

	}

	/**
	 * Check if required plugins are active.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  boolean  Passed/Failed check
	 */
	public function check_dependency_results() {

		if ( empty( $this->plugins_inactive ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Callback function for the plugins_loaded action.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  void
	 */
	public function plugins_loaded() {
		$this->check_inactive_plugin_dependency();
	}

	/**
	 * Function called during the plugins_loaded process to check if required plugins are present and active. Handles regular and Multisite checks.
	 *
	 * @param   boolean $set_admin_notice  Whether to set the Admin Notice or not. Defaults to true.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  array                      Inactive, required Plugins
	 */
	public function check_inactive_plugin_dependency( $set_admin_notice = true ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! empty( $this->plugins_to_check ) ) {

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			foreach ( $this->plugins_to_check as $plugin_key => $plugin_data ) {

				// Check if plugin is inactive directly.
				if ( ! is_plugin_active( $plugin_key ) ) {

					if ( is_multisite() ) {
						if ( ! is_plugin_active_for_network( $plugin_key ) ) {
							$this->plugins_inactive[ $plugin_key ] = $plugin_data;
						}
					} else {
						$this->plugins_inactive[ $plugin_key ] = $plugin_data;
					}
				} else {

					if ( ( isset( $plugin_data['class'] ) ) && ( ! empty( $plugin_data['class'] ) ) && ( ! class_exists( $plugin_data['class'] ) ) ) {
						$this->plugins_inactive[ $plugin_key ] = $plugin_data;
					}
				}

				// Version Checks if plugin is active.
				if ( ( ! isset( $this->plugins_inactive[ $plugin_key ] ) ) && ( isset( $plugin_data['min_version'] ) ) && ( ! empty( $plugin_data['min_version'] ) ) ) {

					if ( isset( $plugin_data['version_constant'] ) && ( defined( $plugin_data['version_constant'] ) ) ) {

						// Check against the Version Constant if we know it.
						if ( version_compare( constant( $plugin_data['version_constant'] ), $plugin_data['min_version'], '<' ) ) {
							$this->plugins_inactive[ $plugin_key ] = $plugin_data;
						}
					} else {

						// Otherwise attempt to parse the plugin header.
						if ( file_exists( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $plugin_key ) ) {
							$plugin_header = get_plugin_data( trailingslashit( wp_normalize_path( WP_PLUGIN_DIR ) ) . $plugin_key );
							if ( version_compare( $plugin_header['Version'], $plugin_data['min_version'], '<' ) ) {
								$this->plugins_inactive[ $plugin_key ] = $plugin_data;
							}
						}
					}
				}
			}

			if ( ( ! empty( $this->plugins_inactive ) ) && ( $set_admin_notice ) ) {
				add_action( 'admin_notices', array( $this, 'notify_required' ) );
			}
		}

		return $this->plugins_inactive;

	}

	/**
	 * Function to set custom admin motice message
	 *
	 * @param string $message Message.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  void
	 */
	public function set_message( $message = '' ) {
		if ( ! empty( $message ) ) {
			$this->admin_notice_message = $message;
		}
	}

	/**
	 * Set plugin required dependencies.
	 *
	 * @param array $plugins Array of of plugins to check.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  void
	 */
	public function set_dependencies( $plugins = array() ) {
		if ( is_array( $plugins ) ) {
			$this->plugins_to_check = $plugins;
		}
	}

	/**
	 * Notify user that missing plugins are required.
	 *
	 * @access  public
	 * @since   1.0.3
	 * @return  void
	 */
	public function notify_required(): void {
		if ( ( ! empty( $this->admin_notice_message ) ) && ( ! empty( $this->plugins_inactive ) ) ) {

			$required_plugins = array();
			foreach ( $this->plugins_inactive as $plugin ) {

				$required_plugin = $plugin['label'];

				if ( ( isset( $plugin['min_version'] ) ) && ( ! empty( $plugin['min_version'] ) ) ) {
					$required_plugin .= ' v' . $plugin['min_version'];
				}

				$required_plugins[] = $required_plugin;

			}

			if ( ! empty( $required_plugins ) ) {
				$admin_notice_message = sprintf(
					$this->admin_notice_message . '<ul style="list-style-type: disc; margin-left: 1.5em;">%s</ul>',
					implode(
						"\n",
						array_map(
							function( $required_plugin ) {
								return "<li>{$required_plugin}</li>";
							},
							$required_plugins
						)
					)
				);
				if ( ! empty( $admin_notice_message ) ) {
					?>
					<div class="notice notice-error ld-notice-error is-dismissible">
						<p><?php echo wp_kses_post( $admin_notice_message ); ?></p>
					</div>
					<?php
				}
			}
		}
	}
}
