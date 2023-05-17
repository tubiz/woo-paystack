jQuery( function( $ ) {

	let paystack_submit = false;

	$( '#wc-paystack-form' ).hide();

	wcPaystackFormHandler();

	jQuery( '#paystack-payment-button' ).click( function() {
		return wcPaystackFormHandler();
	} );

	jQuery( '#paystack_form form#order_review' ).submit( function() {
		return wcPaystackFormHandler();
	} );

	function wcPaystackCustomFields() {

		let custom_fields = [
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

		let custom_filters = {};

		if ( wc_paystack_params.card_channel ) {

			if ( wc_paystack_params.banks_allowed ) {

				custom_filters[ 'banks' ] = wc_paystack_params.banks_allowed;

			}

			if ( wc_paystack_params.cards_allowed ) {

				custom_filters[ 'card_brands' ] = wc_paystack_params.cards_allowed;
			}

		}

		return custom_filters;
	}

	function wcPaymentChannels() {

		let payment_channels = [];

		if ( wc_paystack_params.bank_channel ) {
			payment_channels.push( 'bank' );
		}

		if ( wc_paystack_params.card_channel ) {
			payment_channels.push( 'card' );
		}

		if ( wc_paystack_params.ussd_channel ) {
			payment_channels.push( 'ussd' );
		}

		if ( wc_paystack_params.qr_channel ) {
			payment_channels.push( 'qr' );
		}

		if ( wc_paystack_params.bank_transfer_channel ) {
			payment_channels.push( 'bank_transfer' );
		}

		return payment_channels;
	}

	function wcPaystackFormHandler() {

		$( '#wc-paystack-form' ).hide();

		if ( paystack_submit ) {
			paystack_submit = false;
			return true;
		}

		let $form = $( 'form#payment-form, form#order_review' ),
			paystack_txnref = $form.find( 'input.paystack_txnref' ),
			subaccount_code = '',
			charges_account = '',
			transaction_charges = '';

		paystack_txnref.val( '' );

		if ( wc_paystack_params.subaccount_code ) {
			subaccount_code = wc_paystack_params.subaccount_code;
		}

		if ( wc_paystack_params.charges_account ) {
			charges_account = wc_paystack_params.charges_account;
		}

		if ( wc_paystack_params.transaction_charges ) {
			transaction_charges = Number( wc_paystack_params.transaction_charges );
		}

		let amount = Number( wc_paystack_params.amount );

		let paystack_callback = function( transaction ) {
			$form.append( '<input type="hidden" class="paystack_txnref" name="paystack_txnref" value="' + transaction.reference + '"/>' );
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

		let paymentData = {
			key: wc_paystack_params.key,
			email: wc_paystack_params.email,
			amount: amount,
			ref: wc_paystack_params.txnref,
			currency: wc_paystack_params.currency,
			subaccount: subaccount_code,
			bearer: charges_account,
			transaction_charge: transaction_charges,
			metadata: {
				custom_fields: wcPaystackCustomFields(),
			},
			onSuccess: paystack_callback,
			onCancel: () => {
				$( '#wc-paystack-form' ).show();
				$( this.el ).unblock();
			}
		};

		if ( Array.isArray( wcPaymentChannels() ) && wcPaymentChannels().length ) {
			paymentData[ 'channels' ] = wcPaymentChannels();
			if ( !$.isEmptyObject( wcPaystackCustomFilters() ) ) {
				paymentData[ 'metadata' ][ 'custom_filters' ] = wcPaystackCustomFilters();
			}
		}

		const paystack = new PaystackPop();
		paystack.newTransaction( paymentData );
	}

} );