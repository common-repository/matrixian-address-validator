<?php

/**
 * The api-specific functionality of the plugin.
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */

/**
 * The api-specific functionality of the plugin.
 *
 * Defines the API endpoints to connect to the Matrixian API.
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/admin
 */
class Matrixian_Address_Validator_API {

	/**
	 * The API base endpoint of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $base_endpoint_api    The host of this plugin's API.
	 */
	private $base_endpoint_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->base_endpoint_api = 'https://api.matrixiangroup.com';

	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $username    Username for Matrixian API.
	 * @param    string $password    Password for Matrixian API.
	 * @return
	 */
	public function get_auth_token( $username, $password ) {

		try {

			// Check if API has been enabled.
			if ( get_option( 'matrixian_enabled' ) !== 'yes' ) {

				throw new Exception( __( 'You must enable the Matrixian API to validate addresses. Go to WooCommerce -> Advanced -> International Address Checker to enable the API.', 'matrixian-address-validator' ) );

			}

			// Check if retrieved token exists.
			$matrixian_auth_token = get_transient( 'matrixian_auth_token' );

			if ( false !== $matrixian_auth_token ) {
				$token_json = json_decode( $matrixian_auth_token, true );

				// Check if token has not been expired.
				if ( date( 'Ymd' ) < date( 'Ymd', strtotime( $token_json['.expires'] ) ) ) {

					return $token_json['access_token'];

				}
			}

			if ( $username === '' || $password === '' ) {

				throw new Exception( __( 'Username and/or password for API not set. Go to WooCommerce -> Advanced -> International Address Checker to provide the API credentials.', 'matrixian-address-validator' ) );

			}

			$args     = array(
				'body'        => array(
					'username' => $username,
					'password' => $password,
				),
				'timeout'     => '5',
				'redirection' => '5',
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(),
				'cookies'     => array(),
			);
			$response = wp_remote_post( $this->base_endpoint_api . '/token', $args );

			/**
			 * Request not successful.
			 */
			if ( 200 !== $response['response']['code'] ) {
				$error_message = sprintf(
					__(
						'HTTP Status %1$s encountered while attempting to retrieve an access key. Error: %2$s.',
						'matrixian-address-validator'
					),
					$response['response']['code'],
					$response['response']['message']
				);

				throw new \Exception( $error_message );
			}

			// Save request.
			set_transient( 'matrixian_auth_token', $response['body'], DAY_IN_SECONDS );

			$json_token = json_decode( $response['body'], true );

			return $json_token['access_token'];

		} catch ( \Exception $e ) {

			// Handle occurred exception.
			matrixian_handle_exceptions( $e );

		}
	}

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function validate_address() {

		try {

			// Check if API has been enabled.
			if ( get_option( 'matrixian_enabled' ) !== 'yes' ) {

				throw new Exception( __( 'You must enable the Matrixian API to validate addresses. Go to WooCommerce -> Advanced -> International Address Checker to enable the API.', 'matrixian-address-validator' ) );

			}

			// Check if user credentials are filled out.
			if ( ! get_option( 'matrixian_api_user' ) || ! get_option( 'matrixian_api_password' ) ) {

				throw new Exception( __( 'Username and/or password for API not set. Go to WooCommerce -> Advanced -> International Address Checker to provide the API credentials.', 'matrixian-address-validator' ) );

			}

			// Get access token.
			$access_token  = $this->get_auth_token( get_option( 'matrixian_api_user' ), get_option( 'matrixian_api_password' ) );
			$address_items = matrixian_get_address_components( sanitize_text_field( $_POST['address'] ) );

			$url_params = array(
				'countryCode'    => 'country_code',
				'street'         => 'streetName',
				'houseNumber'    => 'houseNumberParts',
				'houseNumberExt' => 'address_ext',
				'postalCode'     => 'postal_code',
				'city'           => 'city',
			);

			$url_pairs = array();
			foreach ( $url_params as $key => $param ) {
				if ( $key === 'street' ) {
					$url_pairs[ $key ] = ( isset( $address_items[ $param ] ) ? $address_items[ $param ] : '****' );
				} elseif ( $key === 'houseNumber' ) {
					$housenumber       = ( isset( $address_items['houseNumberParts']['base'] ) ? $address_items['houseNumberParts']['base'] : '' );
					$housenumber      .= ( isset( $address_items['houseNumberParts']['extension'] ) ? $address_items['houseNumberParts']['extension'] : '' );
					$url_pairs[ $key ] = $housenumber ?: '****';
				} else {
					$url_pairs[ $key ] = ( sanitize_text_field( $_POST[ $param ] ) ?: '****' );
				}
			}

			if ( ! $_POST['address_ext'] ) {
				unset( $url_pairs['houseNumberExt'] );
			}

			/**
			 * Start request.
			 */
			$url = $this->base_endpoint_api . '/address/check?' . http_build_query( $url_pairs, '', '&' );

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
			);

			$response = wp_remote_get( $url, $args );

			/**
			 * Request not successful.
			 */
			if ( 200 !== $response['response']['code'] ) {
				$error_message = sprintf(
					__(
						'HTTP Status %1$s encountered while attempting to validate a given address. Error: %2$s.',
						'matrixian-address-validator'
					),
					$response['response']['code'],
					$response['response']['message']
				);

				throw new \Exception( $error_message );
			}

			wp_send_json_success( $response['body'] );
			die();

		} catch ( \Exception $e ) {

			// Handle occurred exception.
			matrixian_handle_exceptions( $e );

		}
	}
}
