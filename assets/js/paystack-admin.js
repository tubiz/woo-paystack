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
			$( '#woocommerce_paystack_custom_metadata' ).change( function() {
				if ( $( this ).is( ':checked' ) ) {
					$( '#woocommerce_paystack_meta_order_id, #woocommerce_paystack_meta_name, #woocommerce_paystack_meta_email, #woocommerce_paystack_meta_phone, #woocommerce_paystack_meta_billing_address, #woocommerce_paystack_meta_shipping_address, #woocommerce_paystack_meta_products' ).closest( 'tr' ).show();
				} else {
					$( '#woocommerce_paystack_meta_order_id, #woocommerce_paystack_meta_name, #woocommerce_paystack_meta_email, #woocommerce_paystack_meta_phone, #woocommerce_paystack_meta_billing_address, #woocommerce_paystack_meta_shipping_address, #woocommerce_paystack_meta_products' ).closest( 'tr' ).hide();
				}
			}).change();

		}
	};

	wc_paystack_admin.init();
});
