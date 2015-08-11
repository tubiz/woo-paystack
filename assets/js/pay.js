jQuery( function() {

    /* Checkout Form */
    jQuery('form.checkout').on('checkout_place_order_paystack', function( event ) {
        return payStackFormHandler();
    });

    /* Pay Page Form */
    jQuery('form#order_review').submit(function(){
        return payStackFormHandler();
    });

    /* Both Forms */
    jQuery("form.checkout, form#order_review").on('change', '#paystack-cc-form #cardnumber, #paystack-cc-form #exp, #paystack-cc-form #ccv', function( event ) {
        jQuery('.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message, .paystack_token').remove();
        jQuery('.paystack_token').remove();
    });
} );


function payStackFormHandler() {

    if ( jQuery('#payment_method_paystack').is(':checked') ) {

        if ( jQuery( 'input.paystack_token' ).size() == 0 ) {

            var $form   = jQuery("form.checkout, form#order_review");

            $form.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var args = {
                paymentForm: $form,
                publishableKey: wc_paystack_params.key
            };

            console.log( args );

            Paystack.createToken( args, payStackResponseHandler );

            return false;
        }

    }
    return true;
}


function payStackResponseHandler( status, response ){

    var $form   = jQuery("form.checkout, form#order_review");

    console.log( 'Status is ' + status );

    if ( response.error ) {
        console.log( response );

        console.log( response.error.message );

        // show the errors on the form
        jQuery('.woocommerce_error, .woocommerce-error, .woocommerce-message, .woocommerce_message, .paystack_token').remove();
        jQuery('#paystack-cc-form #cardnumber').closest('p').before( '<ul class="woocommerce_error woocommerce-error"><li>' + response.error.message + '</li></ul>' );
        $form.unblock();
    }
    else{
        console.log( response );

        // response contains id and card, which contains additional card details
        var token = response.token;

        // insert the token into the form so it gets submitted to the server
        $form.append("<input type='hidden' class='paystack_token' name='paystack_token' value='" + token + "'/>");
        $form.submit();
    }
}
