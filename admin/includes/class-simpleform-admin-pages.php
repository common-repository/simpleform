<?php
/**
 * File delegated to update the form locks.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class that deals with the form locks management.
 */
class SimpleForm_Admin_Pages {

	/**
	 * Class constructor
	 *
	 * @since 2.2.0
	 */
	public function __construct() {

		// Register the administration menu for this plugin into the WordPress Dashboard menu.
		add_action( 'admin_menu', array( $this, 'sform_admin_menu' ) );
		// Show the parent menu active for hidden sub-menu item.
		add_filter( 'parent_file', array( $this, 'contacts_menu_open' ), 1, 1 );
		// Display additional action links in the plugins list table.
		add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );
		// Add support links in the plugin meta row.
		add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), 10, 2 );
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function sform_admin_menu() {

		$contacts        = __( 'Contacts', 'simpleform' );
		$contacts_bubble = apply_filters( 'sform_notification_bubble', $contacts );
		add_menu_page( $contacts, $contacts_bubble, 'manage_options', 'sform-entries', array( $this, 'admin_page' ), 'dashicons-email-alt', 24 );

		global $sform_entries;
		$submissions   = __( 'Entries', 'simpleform' );
		$sform_entries = add_submenu_page( 'sform-entries', $submissions, $submissions, 'manage_options', 'sform-entries', array( $this, 'admin_page' ) );

		global $sform_forms;
		$forms       = __( 'Forms', 'simpleform' );
		$sform_forms = add_submenu_page( 'sform-entries', $forms, $forms, 'manage_options', 'sform-forms', array( $this, 'admin_page' ) );
		// Add screen option tab.
		add_action( "load-$sform_forms", array( $this, 'forms_screen_options' ) );

		global $sform_form;
		$form       = __( 'Form', 'simpleform' );
		$sform_form = add_submenu_page( '', $form, $form, 'manage_options', 'sform-form', array( $this, 'admin_page' ) );

		global $sform_new;
		$new       = __( 'Add New', 'simpleform' );
		$sform_new = add_submenu_page( '', $new, $new, 'manage_options', 'sform-new', array( $this, 'admin_page' ) );

		global $sform_editor;
		/* translators: Used to indicate the form editor not user role */
		$editor       = __( 'Editor', 'simpleform' );
		$sform_editor = add_submenu_page( 'sform-entries', $editor, $editor, 'manage_options', 'sform-editor', array( $this, 'admin_page' ) );

		global $sform_settings;
		$settings       = __( 'Settings', 'simpleform' );
		$sform_settings = add_submenu_page( 'sform-entries', $settings, $settings, 'manage_options', 'sform-settings', array( $this, 'admin_page' ) );

		global $sform_support;
		$support       = __( 'Support', 'simpleform-contact-form-submissions' );
		$sform_support = add_submenu_page( 'sform-entries', $support, $support, 'manage_options', 'sform-support', array( $this, 'admin_page' ) );

		do_action( 'load_entries_screen_options' );
	}

	/**
	 * Render the simpleform admin page.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function admin_page() {

		$admin_pages = array(
			'sform-entries'  => 'partials/entries.php',
			'sform-forms'    => 'partials/forms.php',
			'sform-form'     => 'partials/form.php',
			'sform-new'      => 'partials/new.php',
			'sform-editor'   => 'partials/editor.php',
			'sform-settings' => 'partials/settings.php',
			'sform-support'  => 'partials/support.php',
		);

		foreach ( $admin_pages as $admin_page => $path ) {
			if ( isset( $_GET['page'] ) && $_GET['page'] === $admin_page ) {  // phpcs:ignore WordPress.Security.NonceVerification
				include_once plugin_dir_path( __DIR__ ) . $path;
			}
		}
	}

	/**
	 * Show the parent menu active for hidden sub menus.
	 *
	 * @since 2.1.0
	 *
	 * @param string $parent_file The parent file.
	 *
	 * @return string The parent file that must be used.
	 */
	public function contacts_menu_open( $parent_file ) {

		global $plugin_page;

		if ( 'sform-form' === $plugin_page || 'sform-new' === $plugin_page ) {
			$plugin_page = 'sform-forms'; // phpcs:ignore
		}

		return $parent_file;
	}

	/**
	 * Setup function that registers the screen option.
	 *
	 * @since 2.1.0
	 *
	 * @return void
	 */
	public function forms_screen_options() {

		global $sform_forms;
		$screen = get_current_screen();

		if ( ! is_object( $screen ) || $screen->id !== $sform_forms ) {
			return;
		}
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Number of forms per page', 'simpleform' ),
			'default' => 10,
			'option'  => 'forms_per_page',
		);

		add_screen_option( $option, $args );
	}

	/**
	 * Add support links in the plugin meta row
	 *
	 * @since 1.10.0
	 *
	 * @param string[] $plugin_meta Array of the plugin's metadata.
	 * @param string   $file        Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of the plugin's metadata.
	 */
	public function plugin_meta( $plugin_meta, $file ) {

		if ( strpos( $file, 'simpleform/simpleform.php' ) !== false ) {
			$plugin_meta[] = '<a href="https://wordpress.org/support/plugin/simpleform/" target="_blank">' . __( 'Support', 'simpleform' ) . '</a>';
		}

		return $plugin_meta;
	}

	/**
	 * Display additional action links in the plugins list table
	 *
	 * @since 1.10.0
	 *
	 * @param string[] $plugin_actions Array of plugin action links.
	 * @param string   $plugin_file    Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[] Array of plugin action links.
	 */
	public function plugin_links( $plugin_actions, $plugin_file ) {

		$new_actions = array();
		if ( 'simpleform/simpleform.php' === $plugin_file ) {

			if ( is_multisite() ) {
				$url = network_admin_url( 'plugin-install.php?tab=search&type=tag&s=simpleform-addon' );
			} else {
				$url = admin_url( 'plugin-install.php?tab=search&type=tag&s=simpleform-addon' );
			}

			$new_actions['sform_settings'] = '<a href="' . menu_page_url( 'sform-editor', false ) . '">' . __( 'Editor', 'simpleform' ) . '</a> | <a href="' . menu_page_url( 'sform-settings', false ) . '">' . __( 'Settings', 'simpleform' ) . '</a> | <a href="' . $url . '" target="_blank">' . __( 'Addons', 'simpleform' ) . '</a>';

		}

		return array_merge( $new_actions, $plugin_actions );
	}
}

new SimpleForm_Admin_Pages();
