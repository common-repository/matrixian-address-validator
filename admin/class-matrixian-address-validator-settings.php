<?php

/**
 * The settings-specific functionality of the plugin.
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */

/**
 * The settings-specific functionality of the plugin.
 *
 * Defines a new section for the WooCommerce Settings to control
 * the plugin API settings.
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */
class Matrixian_Address_Validator_Settings {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		add_filter( 'woocommerce_get_sections_advanced', array( $this, 'add_settings_tab' ), 99 );
		add_filter( 'woocommerce_get_settings_advanced', array( $this, 'get_settings' ), 10, 2 );

	}

	/**
	 * Add settings tab to the Advanced tab in WooCommerce Settings page.
	 *
	 * @since    1.0.0
	 * @param    array $settings_tab  Active settings tab sections.
	 * @return   array $settings_tab  Settings tab sections with our section included.
	 */
	public function add_settings_tab( $settings_tab ) {

		$settings_tab['matrixian_address_validator'] = __( 'International Address Checker', 'matrixian-address-validator' );
		return $settings_tab;

	}

	/**
	 * Get settings fields by current section
	 *
	 * @since    1.0.0
	 * @param    array  $settings         Section form settings fields.
	 * @param    string $current_section  Current section ID.
	 * @return   array  $settings         Default settings or Matrixian settingss.
	 */
	public function get_settings( $settings, $current_section ) {

		if ( 'matrixian_address_validator' === $current_section ) {

			$settings = array();
			$settings = array(
				array(
					'name' => __( 'International Address Checker Settings', 'matrixian-address-validator' ),
					'type' => 'title',
					'desc' => __( 'Fill out the API details to enable address validation.', 'matrixian-address-validator' ),
					'id'   => 'matrixian_title',
				),
				array(
					'name' => __( 'Address Validation', 'matrixian-address-validator' ),
					'type' => 'checkbox',
					'desc' => __( 'Enables the International Address Checker', 'matrixian-address-validator' ),
					'id'   => 'matrixian_enabled',
				),
				array(
					'name'     => __( 'Username', 'matrixian-address-validator' ),
					'type'     => 'text',
					'desc'     => __( 'Username for the Matrixian API', 'matrixian-address-validator' ),
					'desc_tip' => true,
					'id'       => 'matrixian_api_user',
				),
				array(
					'name'     => __( 'Password', 'matrixian-address-validator' ),
					'type'     => 'password',
					'desc'     => __( 'Password for the Matrixian API', 'matrixian-address-validator' ),
					'desc_tip' => true,
					'id'       => 'matrixian_api_password',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'matrixian_address_end',
				),
			);

		}

		return $settings;

	}
}
