jQuery( function( $ ) {

	var paystack_submit = false;

	if ( 'embed' === wc_paystack_params.pay_page ) {

		wcPayStackEmbedFormHandler();

	} else {

		jQuery( '#paystack-payment-button' ).click( function() {
			return wcPaystackFormHandler();
		} );

		jQuery( '#paystack_form form#order_review' ).submit( function() {
			return wcPaystackFormHandler();
		} );

	}

	function wcPaystackCustomFields() {

		var custom_fields = [
			{
				"display_name": "Plugin",
				"variable_name": "plugin",
				"value": "woo-paystack"
			}
		];

		if ( wc_paystack_params.meta_order_id ) {

			custom_fields.push( {
				display_name: "Order ID",
				variable_name: "order_id",
				value: wc_paystack_params.meta_order_id
			} );

		}

		if ( wc_paystack_params.meta_name ) {

			custom_fields.push( {
				display_name: "Customer Name",
				variable_name: "customer_name",
				value: wc_paystack_params.meta_name
			} );
		}

		if ( wc_paystack_params.meta_email ) {

			custom_fields.push( {
				display_name: "Customer Email",
				variable_name: "customer_email",
				value: wc_paystack_params.meta_email
			} );
		}

		if ( wc_paystack_params.meta_phone ) {

			custom_fields.push( {
				display_name: "Customer Phone",
				variable_name: "customer_phone",
				value: wc_paystack_params.meta_phone
			} );
		}

		if ( wc_paystack_params.meta_billing_address ) {

			custom_fields.push( {
				display_name: "Billing Address",
				variable_name: "billing_address",
				value: wc_paystack_params.meta_billing_address
			} );
		}

		if ( wc_paystack_params.meta_shipping_address ) {

			custom_fields.push( {
				display_name: "Shipping Address",
				variable_name: "shipping_address",
				value: wc_paystack_params.meta_shipping_address
			} );
		}

		if ( wc_paystack_params.meta_products ) {

			custom_fields.push( {
				display_name: "Products",
				variable_name: "products",
				value: wc_paystack_params.meta_products
			} );
		}

		return custom_fields;
	}

	function wcPaystackCustomFilters() {

		var custom_filters = new Object();

		if ( wc_paystack_params.banks_allowed ) {

			custom_filters[ 'banks' ] = wc_paystack_params.banks_allowed;

		}

		if ( wc_paystack_params.cards_allowed ) {

			custom_filters[ 'card_brands' ] = wc_paystack_params.cards_allowed;
		}

		return custom_filters;
	}

	function wcPaystackFormHandler() {

		if ( paystack_submit ) {
			paystack_submit = false;
			return true;
		}

		var $form = $( 'form#payment-form, form#order_review' ),
			paystack_txnref = $form.find( 'input.paystack_txnref' ),
			bank = "false",
			card = "false",
			subaccount_code = '',
			charges_account = '',
			transaction_charges = '';

		paystack_txnref.val( '' );

		if ( wc_paystack_params.bank_channel ) {
			bank = "true";
		}

		if ( wc_paystack_params.card_channel ) {
			card = "true";
		}

		if ( wc_paystack_params.subaccount_code ) {
			subaccount_code = wc_paystack_params.subaccount_code;
		}

		if ( wc_paystack_params.charges_account ) {
			charges_account = wc_paystack_params.charges_account;
		}

		if ( wc_paystack_params.transaction_charges ) {
			transaction_charges = Number( wc_paystack_params.transaction_charges );
		}

		var amount = Number( wc_paystack_params.amount );

		var paystack_callback = function( response ) {
			$form.append( '<input type="hidden" class="paystack_txnref" name="paystack_txnref" value="' + response.trxref + '"/>' );
			paystack_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				},
				css: {
					cursor: "wait"
				}
			} );
		};

		var handler = PaystackPop.setup( {
			key: wc_paystack_params.key,
			email: wc_paystack_params.email,
			amount: amount,
			ref: wc_paystack_params.txnref,
			currency: wc_paystack_params.currency,
			callback: paystack_callback,
			bank: bank,
			card: card,
			subaccount: subaccount_code,
			bearer: charges_account,
			transaction_charge: transaction_charges,
			metadata: {
				custom_fields: wcPaystackCustomFields(),
				custom_filters: wcPaystackCustomFilters()
			},
			onClose: function() {
				$( this.el ).unblock();
			}
		} );

		handler.openIframe();

		return false;

	}

	function wcPayStackEmbedFormHandler() {

		if ( paystack_submit ) {
			paystack_submit = false;
			return true;
		}

		var $form = $( 'form#payment-form, form#order_review' ),
			paystack_txnref = $form.find( 'input.paystack_txnref' ),
			bank = "false",
			card = "false",
			subaccount_code = '',
			charges_account = '',
			transaction_charges = '';

		paystack_txnref.val( '' );

		if ( wc_paystack_params.bank_channel ) {
			bank = "true";
		}

		if ( wc_paystack_params.card_channel ) {
			card = "true";
		}

		if ( wc_paystack_params.subaccount_code ) {
			subaccount_code = wc_paystack_params.subaccount_code;
		}

		if ( wc_paystack_params.charges_account ) {
			charges_account = wc_paystack_params.charges_account;
		}

		if ( wc_paystack_params.transaction_charges ) {
			transaction_charges = Number( wc_paystack_params.transaction_charges );
		}

		var amount = Number( wc_paystack_params.amount );

		var paystack_callback = function( response ) {

			$form.append( '<input type="hidden" class="paystack_txnref" name="paystack_txnref" value="' + response.trxref + '"/>' );

			$( '#paystack_form a' ).hide();

			paystack_submit = true;

			$form.submit();

			$( 'body' ).block( {
				message: null,
				overlayCSS: {
					background: "#fff",
					opacity: 0.8
				},
				css: {
					cursor: "wait"
				}
			} );

		};

		var handler = PaystackPop.setup( {
			key: wc_paystack_params.key,
			email: wc_paystack_params.email,
			amount: amount,
			ref: wc_paystack_params.txnref,
			currency: wc_paystack_params.currency,
			container: "paystackWooCommerceEmbedContainer",
			callback: paystack_callback,
			bank: bank,
			card: card,
			subaccount: subaccount_code,
			bearer: charges_account,
			transaction_charge: transaction_charges,
			metadata: {
				custom_fields: wcPaystackCustomFields(),
				custom_filters: wcPaystackCustomFilters()
			},
			onClose: function() {
				$( this.el ).unblock();
			}
		} );

		handler.openIframe();

		return false;

	}

} );