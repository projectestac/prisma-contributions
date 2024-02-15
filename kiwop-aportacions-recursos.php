<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kiwop.com
 * @since             1.0.0
 * @package           Kiwop_Aportacions_Recursos
 *
 * @wordpress-plugin
 * Plugin Name:       Prisma Contributions
 * Plugin URI:        https://kiwop.com
 * Description:       Recull informació d´un formulari al front i el converteix en un post en estat draft.
 * Version:           1.0.0
 * Author:            Antonio Sanchez (kiwop)
 * Author URI:        https://kiwop.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kiwop-aportacions-recursos
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KIWOP_APORTACIONS_RECURSOS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kiwop-aportacions-recursos-activator.php
 */
function activate_kiwop_aportacions_recursos() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-aportacions-recursos-activator.php';
	Kiwop_Aportacions_Recursos_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kiwop-aportacions-recursos-deactivator.php
 */
function deactivate_kiwop_aportacions_recursos() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-aportacions-recursos-deactivator.php';
	Kiwop_Aportacions_Recursos_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kiwop_aportacions_recursos' );
register_deactivation_hook( __FILE__, 'deactivate_kiwop_aportacions_recursos' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-aportacions-recursos.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kiwop_aportacions_recursos() {

	$plugin = new Kiwop_Aportacions_Recursos();
	$plugin->run();

}
run_kiwop_aportacions_recursos();
