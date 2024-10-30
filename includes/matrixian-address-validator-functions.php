<?php

/**
 * The file that defines the core plugin functions
 *
 * Function definitions that are used across both the public-facing side
 * of the site and the admin area.
 *
 * @link       https://www.matrixiangroup.com/en/
 * @since      1.0.0
 *
 * @package    Matrixian_Address_Validator
 * @subpackage Matrixian_Address_Validator/includes
 */

/**
 * Split full address into components
 *
 * @since   1.0.0
 * @param   string $address Full street address.
 * @return  array  $result  Address components (street, number, number addition)
 */
function matrixian_get_address_components( $address ) {
    $splitted = array();

    try {
        if ( ! preg_match('/[0-9]/', $address ) ) {
            // No house number found. Use single address line only.
            $splitted = array(
                'streetName' => $address,
            );
        } else {
            // House number exists. Try to split up address.
            $splitted = Matrixian_Address_Validator_Splitter::splitAddress( $address );
        }
    } catch ( \Exception $e ) {
        matrixian_handle_exceptions( $e );
    }

    return $splitted;
}

/**
 * Handle possible exceptions
 *
 * @param \Exception $exception Exception that occurred.
 * @throws \Exception Exception that occurred.
 */
function matrixian_handle_exceptions( $exception, $is_single = true ) {
    if ( $is_single ) {
        $message = matrixian_get_prettified_exception_message( $exception );
    } else {
        $message = '';
        foreach ( $exception as $e ) {
            $message .= matrixian_get_prettified_exception_message( $e );
        }
    }

    if ( ! class_exists( 'Sentry' ) ) {
        // Sentry not attached.
        $email   = get_option( 'admin_email' );
        $subject = 'Fout in koppeling LogicTrade op ' . get_option( 'blogname' );

        if ( '' !== $email ) {
            wp_mail(
                $email,
                $subject,
                $message
            );
        }
    } else {
        // Let Sentry handle the exception.
        if ( $is_single ) {
            \Sentry\captureException( $exception );
        } else {
            foreach ( $exception as $e ) {
                \Sentry\captureException( $e );
            }
        }
    }
}

/**
 * Get readable error messages for fast debug.
 *
 * @param \Exception $e   Occurred exception.
 * @return string $message
 */
function matrixian_get_prettified_exception_message( Exception $e ) {
    $result  = 'Exception: "';
    $result .= $e->getMessage();
    $result .= '<br />';
    $result .= $e->getTraceAsString();

    return $result;
}
