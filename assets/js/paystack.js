jQuery( function( $ ) {

    var paystack_submit = false;

    /* Pay Page Form */
    jQuery( '#paystack-payment-button' ).click( function() {
        return payStackFormHandler();
    });

    jQuery( '#paystack_form form#order_review' ).submit( function() {
        return payStackFormHandler();
    });

    function payStackFormHandler() {

        if ( paystack_submit ) {
            paystack_submit = false;
            return true;
        }

        var $form            = $( 'form#payment-form, form#order_review' ),
            paystack_txnref  = $form.find( 'input.paystack_txnref' );

        paystack_txnref.val( '' );

        var paystack_callback = function( response ) {
            $form.append( '<input type="hidden" class="paystack_txnref" name="paystack_txnref" value="' + response.trxref + '"/>' );
            paystack_submit = true;

            $form.submit();

            $.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        };

        var handler = PaystackPop.setup({
            key: wc_paystack_params.key,
            email: wc_paystack_params.email,
            amount: wc_paystack_params.amount,
            ref: wc_paystack_params.txnref,
            callback: paystack_callback,
            onClose: function() {
                $( this.el ).unblock();
            }
        });
        handler.openIframe();

        return false;

    }

} );


