<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://shawn-crigger.herokuapp.com
 * @since             1.0.0
 * @package           Find_A_Provider
 *
 * @wordpress-plugin
 * Plugin Name:       Find a Provider
 * Plugin URI:        https://github.com/shawn-crigger/find-a-provider
 * Description:       Shows all the provider locations on a google map from the ZohoCRM accounts feed
 * Version:           1.0.0
 * Author:            Shawn Crigger
 * Author URI:        https://shawn-crigger.herokuapp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       find-a-provider
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
define( 'FIND_A_PROVIDER_VERSION', '1.0.0' );
define( 'FIND_A_PROVIDER_DEVELOP', TRUE );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-find-a-provider-activator.php
 */
function activate_find_a_provider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-find-a-provider-activator.php';
	Find_A_Provider_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-find-a-provider-deactivator.php
 */
function deactivate_find_a_provider() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-find-a-provider-deactivator.php';
	Find_A_Provider_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_find_a_provider' );
register_deactivation_hook( __FILE__, 'deactivate_find_a_provider' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-find-a-provider.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_find_a_provider() {

	$plugin = new Find_A_Provider();
	$plugin->run();

}
run_find_a_provider();
