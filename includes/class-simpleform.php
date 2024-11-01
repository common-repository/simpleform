<?php
/**
 * File that takes care of implementing all the necessary functions to manage the plugin.
 *
 * @package    SimpleForm
 * @subpackage SimpleForm/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class used to define admin-area hooks and public-facing site hooks.
 */
class SimpleForm {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    SimpleForm_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The plugin's unique identifier
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The plugin's current version
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string    $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the plugin's core functionality.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'simpleform';
		$this->version     = SIMPLEFORM_VERSION;

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_block_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the plugin.
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-simpleform-loader.php';
		// The class responsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-simpleform-admin.php';
		// The class responsible for defining all actions that occur in the public-facing side of the site.
		require_once plugin_dir_path( __DIR__ ) . 'public/class-simpleform-public.php';
		// The class responsible for defining all actions that deals with the plugin block.
		require_once plugin_dir_path( __DIR__ ) . 'admin/block/class-simpleform-block.php';
		// The class responsible for defining all actions that occur in both sides.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-util.php';
		// The base class for displaying a list of forms in an ajaxified HTML table.
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		// The customized class that extends the base class.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-forms-list.php';
		// The core base class extended to register widgets.
		if ( ! class_exists( 'WP_Widget' ) ) {
			require_once ABSPATH . 'wp-includes/class-wp-widget.php';
		}
		// The class responsible for defining the plugin widget.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-widget.php';
		// The class responsible for the form display.
		require_once plugin_dir_path( __DIR__ ) . 'public/includes/class-simpleform-display.php';
		// The class responsible for the emails management.
		require_once plugin_dir_path( __DIR__ ) . 'public/includes/class-simpleform-emails.php';
		// The class responsible for the fields validation.
		require_once plugin_dir_path( __DIR__ ) . 'public/includes/class-simpleform-errors.php';
		// The class responsible for the notifications management.
		require_once plugin_dir_path( __DIR__ ) . 'public/includes/class-simpleform-processing.php';
		// The class responsible for the form validation.
		require_once plugin_dir_path( __DIR__ ) . 'public/includes/class-simpleform-validation.php';
		// The class responsible for the admin options validation.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-admin-validation.php';
		// The class responsible for the admin options filtering.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-admin-errors.php';
		// The class responsible for the admin options management.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-admin-processing.php';
		// The class responsible for the forms deletion.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-forms-deleting.php';
		// The class responsible for the entries management.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-entries-management.php';
		// The class responsible for the admin pages management.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-admin-pages.php';
		// The class responsible for the forms management.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-forms-management.php';
		// The class responsible for the plugin management.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-plugin-management.php';

		$this->loader = new SimpleForm_Loader();
	}

	/**
	 * Register all hooks related to the admin area functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function define_admin_hooks() {

		$plugin_admin = new SimpleForm_Admin( $this->get_plugin_name(), $this->get_version() );

		// Register the stylesheets for the admin area.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 1 );
		// Register the scripts for the admin area.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 1 );
		// Register ajax callback for form editing.
		$this->loader->add_action( 'wp_ajax_form_editor', $plugin_admin, 'form_editor' );
		// Register ajax callback for settings editing.
		$this->loader->add_action( 'wp_ajax_form_settings', $plugin_admin, 'form_settings' );
		// Filter the tables to drop when a site into a network is deleted.
		$this->loader->add_filter( 'wpmu_drop_tables', $plugin_admin, 'on_delete_blog' );
		// Register ajax callback for include the link to privacy policy page into label.
		$this->loader->add_action( 'wp_ajax_privacy_page_setting', $plugin_admin, 'privacy_page_setting' );
		// Register ajax callback for include target attribute in the link to the privacy policy page.
		$this->loader->add_action( 'wp_ajax_privacy_page_opening', $plugin_admin, 'privacy_page_opening' );
		// Clean up the post content of any removed or duplicated form.
		$this->loader->add_action( 'forms_cleaning', $plugin_admin, 'forms_cleaning', 10, 4 );
		// Register ajax callback for change admin color scheme.
		$this->loader->add_action( 'wp_ajax_admin_color_scheme', $plugin_admin, 'admin_color_scheme' );
	}

	/**
	 * Register all hooks related to the public-facing functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function define_public_hooks() {

		$plugin_public = new SimpleForm_Public( $this->get_plugin_name(), $this->get_version() );

		// Register the stylesheets for the public-facing side of the site.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// Register the scripts for the public-facing side of the site.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		// Register shortcode via loader.
		$this->loader->add_shortcode( 'simpleform', $plugin_public, 'sform_shortcode' );
		// Register ajax callback for submitting form.
		$this->loader->add_action( 'wp_ajax_formdata_ajax_processing', $plugin_public, 'formdata_ajax_processing' );
		$this->loader->add_action( 'wp_ajax_nopriv_formdata_ajax_processing', $plugin_public, 'formdata_ajax_processing' );
		// Register callback for form data validation.
		$this->loader->add_filter( 'sform_validation', $plugin_public, 'formdata_validation', 12, 1 );
		// Register callback for form data processing.
		$this->loader->add_filter( 'sform_processing', $plugin_public, 'formdata_processing', 12, 3 );
	}

	/**
	 * Register all hooks related to the block functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function define_block_hooks() {

		$plugin_block = new SimpleForm_Block( $this->get_plugin_name(), $this->get_version() );

		// Load the standard widget if the current theme is not a block-based theme.
		if ( false === wp_is_block_theme() ) {
			add_action( 'widgets_init', array( $this, 'register_widget' ) );
		}

		// Register the block.
		$this->loader->add_action( 'init', $plugin_block, 'register_block' );
		// Hide widget blocks if the form already appears in the page.
		$this->loader->add_filter( 'sidebars_widgets', $plugin_block, 'hide_widgets' );
		// Add the theme support to load the form's stylesheet in the editor.
		$editor_styles = get_theme_support( 'editor-styles' );
		if ( false === $editor_styles ) {
			$this->loader->add_action( 'after_setup_theme', $plugin_block, 'editor_styles_support' );
		}
		// Register the form stylesheet to use in the editor.
		$this->loader->add_action( 'admin_init', $plugin_block, 'add_editor_styles' );
		// Add additional functionality to the block editor.
		$this->loader->add_action( 'enqueue_block_editor_assets', $plugin_block, 'add_editor_features' );
	}

	/**
	 * Register the standard widget.
	 *
	 * @since 2.2.0
	 *
	 * @return void
	 */
	public function register_widget() {

		register_widget( 'SimpleForm_Widget' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {

		$this->loader->run();
	}

	/**
	 * Retrieve the plugin's name
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin
	 *
	 * @since 1.0.0
	 *
	 * @return SimpleForm_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;
	}

	/**
	 * Retrieve the plugin's version number
	 *
	 * @since 1.0.0
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {

		return $this->version;
	}
}
