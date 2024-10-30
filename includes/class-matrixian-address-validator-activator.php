<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/includes
 */
class Matrixian_Address_Validator_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Require WooCommerce plugin.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
			// Stop activation redirect and show error.
			wp_die(
				__( 'Sorry, this plugin requires the WooCommerce Plugin to be installed and active.', 'matrixian-address-validator' )
				. '<br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; ' . __( 'Return to Plugins', 'matrixian-address-validator' ) . '</a>'
			);
		}

	}

}
