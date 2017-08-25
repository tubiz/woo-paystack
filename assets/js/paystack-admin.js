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

		}
	};

	wc_paystack_admin.init();
});
