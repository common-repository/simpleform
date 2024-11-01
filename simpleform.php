<?php
/**
 *
 * Plugin Name:       SimpleForm
 * Description:       Create a basic contact form for your website. Lightweight and very simple to manage, SimpleForm is immediately ready to use.
 * Version:           2.2.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            SimpleForm Team
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simpleform
 *
 * @package           SimpleForm
 */

defined( 'WPINC' ) || exit;

/**
 * Plugin constants.
 *
 * @since 1.0.0
 */

define( 'SIMPLEFORM_NAME', 'SimpleForm' );
define( 'SIMPLEFORM_VERSION', '2.2.0' );
define( 'SIMPLEFORM_DB_VERSION', '2.2.0' );
define( 'SIMPLEFORM_PATH', plugin_dir_path( __FILE__ ) );
define( 'SIMPLEFORM_URL', plugin_dir_url( __FILE__ ) );
define( 'SIMPLEFORM_BASENAME', plugin_basename( __FILE__ ) );
define( 'SIMPLEFORM_BASEFILE', __FILE__ );
define( 'SIMPLEFORM_DIR', __DIR__ );
define( 'SIMPLEFORM_ROOT', dirname( plugin_basename( __FILE__ ) ) );

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 *
 * @param bool $network_wide Whether to enable the plugin for all sites in the network
 *                           or just the current site. Multisite only. Default false.
 *
 * @return void
 */
function activate_simpleform( $network_wide ) {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-activator.php';
	SimpleForm_Activator::activate( $network_wide );
}

/**
 * Create table when a new site into a network is created.
 *
 * @since 1.2.0
 *
 * @param WP_Site $new_site New site object.
 *
 * @return void
 */
function sform_on_create_blog( $new_site ) {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-activator.php';
	SimpleForm_Activator::on_create_blog( $new_site );
}

add_action( 'wp_insert_site', 'sform_on_create_blog' );

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function deactivate_simpleform() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-deactivator.php';
	SimpleForm_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simpleform' );
register_deactivation_hook( __FILE__, 'deactivate_simpleform' );

/**
 * The core plugin class.
 *
 * @since 1.0.0
 */

require plugin_dir_path( __FILE__ ) . 'includes/class-simpleform.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function run_simpleform() {
	$plugin = new SimpleForm();
	$plugin->run();
}

run_simpleform();
