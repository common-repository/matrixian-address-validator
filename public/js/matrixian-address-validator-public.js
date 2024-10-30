(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 */

    var address_list = null;
    var is_supported_country = false;
    var number_before_address = [ 'FR', 'GB', 'LU', 'US' ];

    /**
     * Bind address change listeners.
     * 
     * @param string type  Address type.
     */
    function bind_address_listeners( type ) {
        var $country = $('#' + type + '_country'),
            $address = $('#' + type + '_address_1'),
            $address_ext = $('#' + type + '_address_2'),
            $postal_code = $('#' + type + '_postcode'),
            $city = $('#' + type + '_city');

        $country.on('change', function() {
            is_supported_country = VALIDATOR.countries.includes( $country.val() );
            $('.woocommerce-' + type + '-fields .field-message').remove();

            if ( is_supported_country ) {
                if ( $address.val() && $postal_code.val() ) {
                    validate_address( type );
                }
            }
        });

        $address.on('change', function() {
            if ( is_supported_country ) {
                if ( $address.val() && ( $postal_code.val() || $city.val() ) ) {
                    validate_address( type );
                }
            }
        });

        $address_ext.on('change', function() {
            if ( is_supported_country ) {
                if ( $address.val() && ( $postal_code.val() || $city.val() ) ) {
                    validate_address( type );
                }
            }
        });

        $postal_code.on('change', function() {
            if ( is_supported_country ) {
                if ( $address.val() || $city.val() ) {
                    validate_address( type );
                }
            }
        });

        $city.on('change', function() {
            if ( is_supported_country ) {
                if ( $address.val() && $postal_code.val() ) {
                    validate_address( type );
                }
            }
        });
    }

    /**
     * Bind address change listeners.
     * 
     * @param string type  Address type.
     */
    function is_address_filled_out( type ) {
        if ( type != 'billing' && type != 'shipping' ) {

            console.error( VALIDATOR_MSG.address_invalid_type );

        }

        if ( $('#' + type + '_country').val() ) {
            is_supported_country = VALIDATOR.countries.includes( $('#' + type + '_country').val() );
        }

        return ( $('#' + type + '_country').val() && $('#' + type + '_address_1').val() && ( $('#' + type + '_postcode').val() || $('#' + type + '_city').val() ) );
    }
    
    /**
     * Validate address on server side.
     * 
     * @param string type  Address type.
     */
    function validate_address( type ) {

        if ( type != 'billing' && type != 'shipping' ) {

            console.error( VALIDATOR_MSG.address_invalid_type );

        }

        $.ajax({
            type: 'POST',
            url: VALIDATOR.ajax_url,
            data: {
                action: 'validate_address',
                country_code: $('#' + type + '_country').val() || '',
                address: $('#' + type + '_address_1').val() || '',
                address_ext: $('#' + type + '_address_2').val() || '',
                postal_code: $('#' + type + '_postcode').val() || '',
                city: $('#' + type + '_city').val() || '',
            },
            beforeSend: function () {

                $('.woocommerce-' + type + '-fields__field-wrapper').addClass('loading');

            },
            success: function( address ) {

                if ( address == '0' ) {
                    console.error( 'Something went wrong. Please check the error log for details.' );
                    $('.woocommerce-' + type + '-fields__field-wrapper').removeClass('loading');
                    return;
                }

                address_list = JSON.parse( address.data );
                var country_id = $('#' + type + '_country').val();

                if ( address_list.length > 1 ) {

                    var address_select = build_select( address_list );

                    set_notice( country_id, type, 'warning', 'address-multiple', address_select );

                    $('.field-message select' ).select2();

                    $('.field-message select' ).on('change', function() {

                        complete_address( type, $(this).val(), address_list, true );

                        set_notice( country_id, type, 'success', 'address-valid' );

                    });

                } else if ( address_list.length ) {

                    if ( address_list[0].houseNumber || address_list[0].houseNumberExt ) {

                        var is_matching_address = compare_address( type, address_list[0] );
                        if ( is_matching_address.matches ) {
                            // Address exact match.

                            complete_address( type, address_list[0].resultNumber, address_list, true );

                            set_notice( country_id, type, 'success', 'address-valid' );
        
                        } else {
                            // Address does not match. Show found one to give suggestion.

                            delete is_matching_address.matches;

                            set_notice( country_id, type, 'warning', 'address-mismatch', ' <span id="suggestion" data-resultnumber="' + address_list[0].resultNumber + '">' + object_to_string( is_matching_address, ',' ) + '</span>' );
                        }
                    } else {

                        // No house number filled.
                        set_notice( country_id, type, 'warning', 'address-housenumber-empty' );
                    }
                } else {

                    // Invalid address.
                    set_notice( country_id, type, 'error', 'address-invalid' );
                }
            },
            error: function( err ) {

                console.error( err );

                var country_id = $('#' + type + '_country').val();

                set_notice( country_id, type, 'error', 'address-error' );

            },
            complete: function() {

                $('.woocommerce-' + type + '-fields__field-wrapper').removeClass('loading');

            }
        });
    }

    /**
     * Get base address components
     *
     * @param string type      Address field type.
     * @param array  selected  To be used address.
     */
    function compare_address( type, selected ) {

        if ( selected == '' ) {
            return false;
        }

        var address_line = get_formatted_address_line( get_address_components( selected.countryIso2, selected ) );
        var address_line_ext = get_formatted_address_line( get_address_ext_components( selected ) );

        var street = $('#' + type + '_address_1').val();
        var street_ext = $('#' + type + '_address_2').val();
        var postcode = $('#' + type + '_postcode').val();
        var city = $('#' + type + '_city').val();

        var matches = ( street === address_line && street_ext === address_line_ext && postcode === selected.postalCode && city === selected.city );

        return {
            matches: matches,
            address: address_line,
            address_ext: address_line_ext,
            postcode: selected.postalCode,
            city: selected.city,
        }
    }

    /**
     * Get base address components
     *
     * @param string country_code   Current country.
     * @param array  address        Given address by API.
     */
    function get_address_components( country_code, address ) {
        if ( number_before_address.includes( country_code ) ) {
            return [ address.houseNumber, address.houseNumberExt, address.street ];
        } else {
            return [ address.street, address.houseNumber, address.houseNumberExt ];
        }
    }

    /**
     * Get address extension components
     *
     * @param array address   Given address by API.
     */
    function get_address_ext_components( address ) {
        return [ address.apartment, address.block, address.door, address.flat, address.floor, address.stair ];
    }

    /**
     * Transform object to string.
     *
     * @param object object  Given object.
     * @param string glue    Optional glue for to string transformation.
     */
    function object_to_string(object, glue) {
        var str = '';

        for (var k in object) {
            if (object.hasOwnProperty(k) && object[k] != '') {
                str += glue + ' ' + object[k];
            }
        }

        return str.slice(2);
    }

    /**
     * Mark given address as completed
     * 
     * @param string  type         Address type.
     * @param integer selected     Selected address ID
     * @param array   options      List of address options.
     * @param boolean fill_address Fill out address fields.
     */
    function complete_address( type, selected, options, fill_address ) {

        options.forEach(function(item, index) {

            if ( item.resultNumber === parseInt( selected ) ) {

                if ( fill_address ) {

                    var address_line = get_formatted_address_line( get_address_components( item.countryIso2, item ) );
                    var address_line_ext = get_formatted_address_line( get_address_ext_components( item ) );

                    $('#' + type + '_address_1').val( address_line );
                    $('#' + type + '_address_2').val( address_line_ext );
                    $('#' + type + '_postcode').val( item.postalCode );
                    $('#' + type + '_city').val( item.city );

                }

                mark_address_field_valid( type, 'address_1' );
                mark_address_field_valid( type, 'address_2' );
                mark_address_field_valid( type, 'postcode' );
                mark_address_field_valid( type, 'city' );

                return;

            }

        });

    }

    /**
     * Get formatted address line
     *
     * @param array address_items   Format to single line address on sorted array.
     */
    function get_formatted_address_line( address_items ) {
        var formatted_line = '';

        address_items.forEach((item, index) => {
            if ( item == null ) {
                return;
            }

            if ( formatted_line == '' ) {
                formatted_line += item;
            } else {
                formatted_line += ' ' + item;
            }
        });

        return formatted_line;
    }

    /**
     * Mark given field valid.
     *
     * @param string type   Field address type.
     * @param string field  Field address name.
     */
    function mark_address_field_valid( type, field ) {
        if ( $('#' + type + '_' + field).val() ) {
            if ( $('#' + type + '_' + field).parents('.form-row').hasClass('woocommerce-invalid') ) {
                $('#' + type + '_' + field).parents('.form-row').removeClass('woocommerce-invalid').addClass('woocommerce-validated');
            } else if ( ! $('#' + type + '_' + field).parents('.form-row').hasClass('woocommerce-validated') ) {
                $('#' + type + '_' + field).parents('.form-row').addClass('woocommerce-validated');
            }
        }
    }

    /**
     * Build an input select based on given address options.
     *
     * @param array   options   List of address options.
     */
    function build_select( options ) {
        var html = '<div class="form-row"><label for="address_suggestions">' + VALIDATOR_MSG.address_select_label + '</label><select name="address_suggestions">';

        html += '<option value="">' + VALIDATOR_MSG.address_select_placeholder + '</option>';

        options.forEach(function(item, index) {

            html += '<option value="' + item.resultNumber + '">' + item.address.join( ', ' ) + '</option>';

        });

        return html += '</select></div>';
    }

    /**
     * Build an input select based on given address options.
     *
     * @param string country_code   Selected country
     * @param string address_type   Address type
     * @param string notice_type    Notice type
     * @param string notice_code    Notice code
     * @param string html           Additional html to include in notice.
     */
    function set_notice( country_code, address_type, notice_type, notice_code, html ) {

        var field_notice = get_notice_wrapper( country_code );
        var notice = get_notice_message( notice_code );
        var extras = html ?? '';

        $('.woocommerce-' + address_type + '-fields .field-message').remove();
        $('#' + address_type + '_' + field_notice ).append('<div class="field-message field-message-' + notice_type + '"><span>' + notice + '</span>' + extras + '</div>');

    }

    /**
     * Get field code for defining notice location in checkout
     *
     * @param string country_code   Selected country
     */
    function get_notice_wrapper( country_code ) {

        var city_codes = [ 'AX', 'BE', 'DK', 'DE', 'EE', 'FI', 'FR', 'IS', 'IL', 'IT', 'NL', 'NO', 'AT', 'PL', 'SI', 'SK', 'VN', 'SE' ];
        var state_codes = [ 'AO', 'BS', 'BO', 'BA', 'CW', 'ES', 'GT', 'HU', 'LI', 'MZ', 'NG', 'UG', 'WS', 'ST', 'SR', 'TR', 'AE', 'ZW', 'CH' ];
        var address_ext_codes = [ 'JP' ];

        if ( city_codes.includes( country_code ) ) {
            return 'city_field';
        }

        if ( state_codes.includes( country_code ) ) {
            return 'state_field';
        }

        if ( address_ext_codes.includes( country_code ) ) {
            return 'address_2_field';
        }

        return 'postcode_field';
    }

    /**
     * Get notice message by notice code
     *
     * @param string notice_code   Notice code.
     */
    function get_notice_message( notice_code ) {
        var message = '';

        switch ( notice_code ) {
            case 'address-valid':
                message = VALIDATOR_MSG.address_valid;
                break;
            case 'address-multiple':
                message = VALIDATOR_MSG.address_multiple;
                break;
            case 'address-mismatch':
                message = VALIDATOR_MSG.address_mismatch;
                break;
            case 'address-housenumber-empty':
                message = VALIDATOR_MSG.address_housenumber_empty;
                break;
            case 'address-invalid':
                message = VALIDATOR_MSG.address_invalid;
                break;
            case 'address-error':
                message = VALIDATOR_MSG.address_error;
                break;
        }

        return message;
    }

    /*
    * Note: It has been assumed you will write jQuery code here, so the
    * $ function reference has been prepared for usage within the scope
    * of this function.
    *
    * This enables you to define handlers, for when the DOM is ready:
    */
	$(function() {

        bind_address_listeners( 'billing' );
        bind_address_listeners( 'shipping' );

        if ( is_address_filled_out( 'billing' ) ) {
            validate_address( 'billing' );
        }

        if ( is_address_filled_out( 'shipping' ) ) {
            validate_address( 'shipping' );
        }

        $(document).on('click', '.field-message #suggestion', function() {
            if ( address_list ) {
                var type = $(this).parents('.form-row').attr('id').indexOf('billing') !== -1 ? 'billing' : 'shipping';
                var country_id = $('#' + type + '_country').val();
                var resultNumber = $(this).attr('data-resultnumber');

                complete_address( type, resultNumber, address_list, true );

                set_notice( country_id, type, 'success', 'address-valid' );
            } 
        });

	});

	 /*
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
