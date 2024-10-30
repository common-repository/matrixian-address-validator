<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.matrixiangroup.com/en/
 * @since             1.0.0
 * @package           Matrixian_Address_Validator
 *
 * @wordpress-plugin
 * Plugin Name:       International Address Checker
 * Plugin URI:        https://wordpress.org/plugins/matrixian-address-validator/
 * Description:       International Address Checker for WooCommerce.
 * Version:           1.0.0
 * Author:            Matrixian
 * Author URI:        https://www.matrixiangroup.com/en/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       matrixian-address-validator
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
define( 'MATRIXIAN_ADDRESS_VALIDATOR_VERSION', '1.0.0' );
define( 'MATRIXIAN_ADDRESS_VALIDATOR_BASE', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-matrixian-address-validator-activator.php
 */
function activate_matrixian_address_validator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-matrixian-address-validator-activator.php';
	Matrixian_Address_Validator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-matrixian-address-validator-deactivator.php
 */
function deactivate_matrixian_address_validator() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-matrixian-address-validator-deactivator.php';
	Matrixian_Address_Validator_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_matrixian_address_validator' );
register_deactivation_hook( __FILE__, 'deactivate_matrixian_address_validator' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-matrixian-address-validator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_matrixian_address_validator() {

	$plugin = new Matrixian_Address_Validator();
	$plugin->run();

}
run_matrixian_address_validator();
