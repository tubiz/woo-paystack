jQuery( function() {

    var paystack_submit = false;

    /* Pay Page Form */
    jQuery( '#paystack-payment-button' ).click( function() {
        return payStackFormHandler();
    });


    function payStackFormHandler() {

        if ( paystack_submit ) {
            paystack_submit = false;
            return true;
        }

        var handler = PaystackPop.setup({
            key: wc_paystack_params.key,
            email: wc_paystack_params.email,
            amount: wc_paystack_params.amount,
            ref: wc_paystack_params.txnref,
            callback: function(response) {
                console.log(response);
                alert('Payment was successful. Transaction reference is ' + response.trxref);
            },
            onClose: function() {
                console.log('Closed window');
                //suppressed
            }
        });
        handler.openIframe();

        return false;
    }

} );


