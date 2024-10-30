<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */
class Matrixian_Address_Validator_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name   The name of the plugin.
	 * @param    string $version       The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/matrixian-address-validator-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/matrixian-address-validator-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers a link to the settings section of the plugin in the admin plugin list.
	 *
	 * @since    1.0.0
	 * @param    array $links  Current list of plugin links.
	 * @return   array $links  Modified list of plugin links.
	 */
	public function add_plugin_settings_link( $links ) {
		// Build and escape the URL.
		$url = esc_url(
			add_query_arg(
				array(
					'page'    => 'wc-settings',
					'tab'     => 'advanced',
					'section' => 'matrixian_address_validator',
				),
				get_admin_url() . 'admin.php'
			)
		);

		// Create the link.
		$settings_link = "<a href='$url'>" . __( 'Settings', 'matrixian-address-validator' ) . '</a>';

		// Adds the link to the end of the array.
		array_push(
			$links,
			$settings_link
		);

		return $links;
	}
}
