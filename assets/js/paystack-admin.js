jQuery( function( $ ) {
	'use strict';

	/**
	 * Object to handle Paystack admin functions.
	 */
	var wc_paystack_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			// Toggle Custom Metadata settings.
			$( '.wc-paystack-metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '.wc-paystack-meta-order-id, .wc-paystack-meta-name, .wc-paystack-meta-email, .wc-paystack-meta-phone, .wc-paystack-meta-billing-address, .wc-paystack-meta-shipping-address, .wc-paystack-meta-products' ).closest( 'tr' ).show();
				} else {
					$( '.wc-paystack-meta-order-id, .wc-paystack-meta-name, .wc-paystack-meta-email, .wc-paystack-meta-phone, .wc-paystack-meta-billing-address, .wc-paystack-meta-shipping-address, .wc-paystack-meta-products' ).closest( 'tr' ).hide();
				}
			}).change();

			// Toggle Bank filters settings.
			$( '.wc-paystack-payment-channels' ).on( 'change', function () {

				var channels = $(".wc-paystack-payment-channels").val();

				if( $.inArray( 'card', channels ) != '-1' ) {
					$( '.wc-paystack-cards-allowed' ).closest( 'tr' ).show();
					$( '.wc-paystack-banks-allowed' ).closest( 'tr' ).show();
				}
				else{
					$( '.wc-paystack-cards-allowed' ).closest( 'tr' ).hide();
					$( '.wc-paystack-banks-allowed' ).closest( 'tr' ).hide();
				}

			}).change();

			$(".wc-paystack-payment-icons").select2({
				templateResult: formatPaystackPaymentIcons,
				templateSelection: formatPaystackPaymentIcons
			});

		}
	};

	function formatPaystackPaymentIcons (payment_method) {
		if (!payment_method.id) { return payment_method.text; }
		var $payment_method = $(
			'<span><img src=" ' + wc_paystack_admin_params.plugin_url + '/assets/images/' + payment_method.element.value.toLowerCase() + '.png" class="img-flag" style="height: 15px; weight:18px;" /> ' + payment_method.text + '</span>'
		);
		return $payment_method;
	};

	wc_paystack_admin.init();

});
