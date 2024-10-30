<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/public
 */
class Matrixian_Address_Validator_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The supported list of countries.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $supported_countries    The current list of supported countries.
	 */
	private $supported_countries;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name   The name of the plugin.
	 * @param    string $version       The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->supported_countries = array( 'AT', 'BE', 'CH', 'DE', 'DK', 'ES', 'FR', 'GB', 'IT', 'LU', 'NL', 'SE', 'US' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/matrixian-address-validator-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/matrixian-address-validator-public.js', array( 'jquery' ), $this->version, false );

		wp_add_inline_script(
			$this->plugin_name,
			'const VALIDATOR = ' . json_encode(
				array(
					'ajax_url'  => admin_url( '/admin-ajax.php' ),
					'countries' => $this->supported_countries,
				)
			)
			. '; const VALIDATOR_MSG = ' . json_encode(
				array(
					'address_invalid_type'       => __( 'Invalid address type. Only billing and shipping are supported.', 'matrixian-address-validator' ),
					'address_select_label'       => __( 'Suggestions:', 'matrixian-address-validator' ),
					'address_select_placeholder' => __( '-- Select an address --', 'matrixian-address-validator' ),
					'address_valid'              => __( 'Address is valid.', 'matrixian-address-validator' ),
					'address_multiple'           => __( 'Address is incomplete. Complete your address or select from given suggestions.', 'matrixian-address-validator' ),
					'address_mismatch'           => __( 'Given address does not match with our records. Did you mean:', 'matrixian-address-validator' ),
					'address_housenumber_empty'  => __( 'Please add a house number to your address.', 'matrixian-address-validator' ),
					'address_invalid'            => __( 'Address is invalid.', 'matrixian-address-validator' ),
					'address_error'              => __( 'Address could not be validated. Please be sure the given address is valid.', 'matrixian-address-validator' ),
				)
			),
			'before'
		);

	}

}
