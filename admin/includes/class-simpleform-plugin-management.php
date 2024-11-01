<?php
/**
 * File delegated to manage the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the form locks management.
 */
class SimpleForm_Plugin_Management {

	/**
	 * The error message.
	 *
	 * @since 2.2.0
	 *
	 * @access protected
	 * @var    object $error Array containing the list of errors.
	 */
	protected $error = null;

	/**
	 * Class constructor
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		$this->plugin_requirements();

		// Display an update message if there's a new release waiting.
		add_filter( 'sform_update', array( $this, 'admin_message' ) );
		// Display the upgrade notice at the end of the update message container in the plugins page.
		add_action( 'in_plugin_update_message-simpleform/simpleform.php', array( $this, 'update_message' ), 10, 2 );

		if ( is_admin() ) {
			// Fallback for database table updating if plugin is already active.
			add_action( 'plugins_loaded', array( $this, 'db_version_check' ) );
		}
	}

	/**
	 * Return an admin message if there's a release waiting or it necessary to save the preferences.
	 *
	 * @since 1.9.2
	 *
	 * @return string The admin message.
	 */
	public function admin_message() {

		$updates = (array) get_option( '_site_transient_update_plugins' );

		if ( isset( $updates['response'] ) && is_array( $updates['response'] ) && array_key_exists( 'simpleform/simpleform.php', $updates['response'] ) ) {

			$update_message = '<span class="admin-notices update"><a href="' . self_admin_url( 'plugins.php' ) . '" target="_blank">' . __( 'There is a new version of SimpleForm available. Get the latest features and improvements!', 'simpleform' ) . '</a></span>';

		} else {

			global $plugin_page;
			$util = new SimpleForm_Util();
			$form = isset( $_GET['form'] ) ? absint( $_GET['form'] ) : 1; // phpcs:ignore

			if ( 'sform-editor' === $plugin_page ) {

				$reference_attribute = $util->get_sform_option( $form, 'attributes', 'required_sign', true );

				if ( ! is_bool( $reference_attribute ) ) {

					$update_message = '<span class="admin-notices update alert">' . __( 'For the form to work properly, check your current settings and save changes.', 'simpleform' ) . '</span>';
				} else {
					$update_message = '';
				}
			} elseif ( 'sform-settings' === $plugin_page ) {

				$reference_setting = $util->get_sform_option( $form, 'settings', 'ajax_submission', false );

				if ( ! is_bool( $reference_setting ) ) {
					$update_message = '<span class="admin-notices update alert">' . __( 'For the form to work properly, check your current settings and save changes.', 'simpleform' ) . '</span>';
				} else {
					$update_message = '';
				}
			} else {
				$update_message = '';
			}
		}

		return $update_message;
	}

	/**
	 * Fallback for database table updating if plugin is already active.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function db_version_check() {

		$current_db_version = SIMPLEFORM_DB_VERSION;
		$installed_version  = get_option( 'sform_db_version' );

		if ( $installed_version !== $current_db_version ) {

			require_once SIMPLEFORM_DIR . '/includes/class-simpleform-activator.php';

			SimpleForm_Activator::create_db();
			SimpleForm_Activator::default_data_entry();
			SimpleForm_Activator::enqueue_additional_code();

		}
	}

	/**
	 * Display an admin notice if preferences need to be re-saved for the plugin to work properly.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	private function plugin_requirements() {

		global $pagenow;

		if ( 'plugins.php' === $pagenow || 'update-core.php' === $pagenow ) {

			$util                = new SimpleForm_Util();
			$reference_attribute = $util->get_sform_option( 1, 'attributes', 'required_sign', true );
			$reference_setting   = $util->get_sform_option( 1, 'settings', 'ajax_submission', false );

			if ( ! is_bool( $reference_attribute ) || ! is_bool( $reference_setting ) ) {

				$editor_page   = '<a href="' . admin_url( 'admin.php?page=sform-editor' ) . '" target="_blank">' . __( 'Editor', 'simpleform' ) . '</a>';
				$settings_page = '<a href="' . admin_url( 'admin.php?page=sform-settings' ) . '" target="_blank">' . __( 'Settings', 'simpleform' ) . '</a>';
				/* translators: %1$s: link to the Editor page, %2$s: link to the Settings page. */
				$message = sprintf( __( 'SimpleForm has undergone significant changes. For the plugin to work properly, you need to clear your website cache and check your previously saved preferences before using the latest version. Go to the %1$s and %2$s pages.', 'simpleform' ), $editor_page, $settings_page );

				$this->add_error( $message );

			}
		}

		if ( is_a( $this->error, 'WP_Error' ) ) {

			add_action( 'admin_footer', array( $this, 'display_error' ) );

		}
	}

	/**
	 * Add a new error to the WP_Error object and create the object if it doesn't exist yet.
	 *
	 * @param string $message The error message.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function add_error( $message ) {
		if ( ! is_object( $this->error ) || ! is_a( $this->error, 'WP_Error' ) ) {
			$this->error = new WP_Error();
		}
		$this->error->add( 'addon_error', $message );
	}

	/**
	 * Display error. Get all the error messages and display them in the admin notices.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function display_error() {
		if ( ! is_a( $this->error, 'WP_Error' ) ) {
			return;
		}
		$message = $this->error->get_error_messages(); ?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php
				if ( count( $message ) > 1 ) {
					echo '<ul>';
					foreach ( $message as $msg ) {
						echo '<li>' . wp_kses_post( $msg ) . '</li>';
					}
					echo '</li>';
				} else {
					echo wp_kses_post( $message[0] );
				}
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display the upgrade notice at the end of the update message container in the plugins page.
	 *
	 * @param mixed[] $plugin_data An array of plugin metadata.
	 * @param object  $new_data    An object of metadata about the available plugin update.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function update_message( $plugin_data, $new_data ) {

		if ( isset( $plugin_data['update'] ) && $plugin_data['update'] ) {

			$upgrade_notice = isset( $new_data->new_version ) ? $this->get_upgrade_notice() : '';

			if ( ! empty( $upgrade_notice ) ) {

				echo '<br><span id="update-message" style="color: #fff; background: #e35950; padding: 7px 26px; display: block; margin: 7px 0;">' . wp_kses_post( $upgrade_notice ) . '</span>';

			}
		}
	}

	/**
	 * Get the upgrade notice from readme.txt file in the WordPress plugin repository.
	 *
	 * @since 2.2.0
	 *
	 * @return string The admin notice.
	 */
	protected function get_upgrade_notice() {

		$upgrade_notice = get_transient( 'sform_upgrade_notice' );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/simpleform/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'] );
				set_transient( 'sform_upgrade_notice', $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		return strval( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @since 2.2.0
	 *
	 * @param string $content SimpleForm readme file content.
	 *
	 * @return string The admin notice.
	 */
	private function parse_update_notice( $content ) {

		$notice_regexp  = '~==\s*Upgrade Notice\s*==(.*)$~Uis';
		$matches        = null;
		$upgrade_notice = '';

		if ( preg_match( $notice_regexp, $content, $matches ) ) {

			if ( isset( explode( '=', $matches[1] )[1] ) ) {
				$upgrade_notice = version_compare( SIMPLEFORM_VERSION, trim( explode( '=', $matches[1] )[1] ), '<' ) ? trim( explode( '=', $matches[1] )[2] ) : '';
			} else {
				$upgrade_notice = $matches[1];
			}

			// Replace url markup.
			$upgrade_notice = preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a style="color: #fff; font-weight: 700;" href="${2}">${1}</a>', $upgrade_notice );

		}

		return wp_kses_post( (string) $upgrade_notice );
	}
}

new SimpleForm_Plugin_Management();
