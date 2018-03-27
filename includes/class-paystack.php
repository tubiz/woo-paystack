<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tbz_WC_Paystack_Gateway extends WC_Payment_Gateway_CC {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id		   			= 'paystack';
		$this->method_title 	    = 'Paystack';
		$this->has_fields 	    	= true;

		$this->supports           	= array(
			'products',
			'tokenization',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer'
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values
		$this->title 				= $this->get_option( 'title' );
		$this->description 			= $this->get_option( 'description' );
		$this->enabled            	= $this->get_option( 'enabled' );
		$this->testmode             = $this->get_option( 'testmode' ) === 'yes' ? true : false;

		$this->payment_page         = $this->get_option( 'payment_page' );

		$this->test_public_key  	= $this->get_option( 'test_public_key' );
		$this->test_secret_key  	= $this->get_option( 'test_secret_key' );

		$this->live_public_key  	= $this->get_option( 'live_public_key' );
		$this->live_secret_key  	= $this->get_option( 'live_secret_key' );

		$this->saved_cards         	= $this->get_option( 'saved_cards' ) === 'yes' ? true : false;

		$this->custom_metadata      = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id      	= $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name      		= $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email      		= $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone      		= $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address= $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products      	= $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->public_key      		= $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key      		= $this->testmode ? $this->test_secret_key : $this->live_secret_key;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook
		add_action( 'woocommerce_api_tbz_wc_paystack_gateway', array( $this, 'verify_paystack_transaction' ) );

		// Webhook listener/API hook
		add_action( 'woocommerce_api_tbz_wc_paystack_webhook', array( $this, 'process_webhooks' ) );

		// Check if the gateway can be used
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}


	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_paystack_supported_currencies', array( 'NGN', 'USD', 'GBP', 'GHS' ) ) ) ) {

			$this->msg = 'Paystack does not support your store currency. Kindly set it to either NGN (&#8358), GHS (&#x20b5;), USD (&#36;) or GBP (&#163;) <a href="' . admin_url( 'admin.php?page=wc-settings&tab=general' ) . '">here</a>';

			return false;

		}

		return true;

	}


	/**
	 * Display paystack payment icon
	 */
	public function get_icon() {

		$icon  = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-wc.png' , WC_PAYSTACK_MAIN_FILE ) ) . '" alt="cards" />';

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	}


	/**
	 * Check if Paystack merchant details is filled
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( 'Please enter your Paystack merchant details <a href="%s">here</a> to be able to use the Paystack WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' ) ) . '</p></div>';
			return;
		}

	}


	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {

		if ( $this->enabled == "yes" ) {

			if ( ! ( $this->public_key && $this->secret_key ) ) {

				return false;

			}

			return true;

		}

		return false;

	}


    /**
     * Admin Panel Options
    */
    public function admin_options() {

    	?>

    	<h3>Paystack</h3>

        <h4>Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="https://dashboard.paystack.co/#/settings/developer" target="_blank" rel="noopener noreferrer">here</a> to the URL below<strong style="color: red"><pre><code><?php echo WC()->api_request_url( 'Tbz_WC_Paystack_Webhook' ); ?></code></pre></strong></h4>

        <?php

		if ( $this->is_valid_for_use() ){

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        }
		else {	 ?>
			<div class="inline error"><p><strong>Paystack Payment Gateway Disabled</strong>: <?php echo $this->msg ?></p></div>

		<?php }

    }


	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$form_fields = array(
			'enabled' => array(
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Paystack',
				'type'        => 'checkbox',
				'description' => 'Enable Paystack as a payment option on the checkout page.',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'title' => array(
				'title' 		=> 'Title',
				'type' 			=> 'text',
				'description' 	=> 'This controls the payment method title which the user sees during checkout.',
    			'desc_tip'      => true,
				'default' 		=> 'Debit/Credit Cards'
			),
			'description' => array(
				'title' 		=> 'Description',
				'type' 			=> 'textarea',
				'description' 	=> 'This controls the payment method description which the user sees during checkout.',
    			'desc_tip'      => true,
				'default' 		=> 'Make payment using your debit and credit cards'
			),
			'testmode' => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Paystack account uncheck this.',
				'default'     => 'yes',
				'desc_tip'    => true
			),
			'payment_page' => array(
				'title'       => 'Payment Page',
				'type'        => 'select',
				'description' => 'Inline shows the payment popup on the page while Inline Embed shows the payment page directly on the page',
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''   		=> 'Select One',
					'inline'   	=> 'Inline',
					'embed' 	=> 'Inline Embed'
				)
			),
			'test_secret_key' => array(
				'title'       => 'Test Secret Key',
				'type'        => 'text',
				'description' => 'Enter your Test Secret Key here',
				'default'     => ''
			),
			'test_public_key' => array(
				'title'       => 'Test Public Key',
				'type'        => 'text',
				'description' => 'Enter your Test Public Key here.',
				'default'     => ''
			),
			'live_secret_key' => array(
				'title'       => 'Live Secret Key',
				'type'        => 'text',
				'description' => 'Enter your Live Secret Key here.',
				'default'     => ''
			),
			'live_public_key' => array(
				'title'       => 'Live Public Key',
				'type'        => 'text',
				'description' => 'Enter your Live Public Key here.',
				'default'     => ''
			),
			'custom_gateways' => array(
				'title'       => 'Additional Paystack Gateways',
				'type'        => 'select',
				'description' => 'Create additional custom Paystack based gateways. This allows you to create additional Paystack gateways using custom filters. You can create a gateway that accepts only verve cards, a gateway that accepts only bank payment, a gateway that accepts a specific bank issued cards.',
				'default'     => '',
				'desc_tip'    => true,
				'options' => array(
					''		=> 'Select One',
					'1'  	=> '1 gateway',
					'2'		=> '2 gateways',
					'3' 	=> '3 gateways',
					'4' 	=> '4 gateways',
					'5' 	=> '5 gateways',
				),
			),
			'saved_cards' 	  => array(
				'title'       => 'Saved Cards',
				'label'       => 'Enable Payment via Saved Cards',
				'type'        => 'checkbox',
				'description' => 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Paystack servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'custom_metadata' 	  => array(
				'title'       => 'Custom Metadata',
				'label'       => 'Enable Custom Metadata',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-metadata',
				'description' => 'If enabled, you will be able to send more information about the order to Paystack.',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_order_id'  => array(
				'title'       => 'Order ID',
				'label'       => 'Send Order ID',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-order-id',
				'description' => 'If checked, the Order ID will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_name'  => array(
				'title'       => 'Customer Name',
				'label'       => 'Send Customer Name',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-name',
				'description' => 'If checked, the customer full name will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_email'  => array(
				'title'       => 'Customer Email',
				'label'       => 'Send Customer Email',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-email',
				'description' => 'If checked, the customer email address will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_phone'  => array(
				'title'       => 'Customer Phone',
				'label'       => 'Send Customer Phone',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-phone',
				'description' => 'If checked, the customer phone will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_billing_address'  => array(
				'title'       => 'Order Billing Address',
				'label'       => 'Send Order Billing Address',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-billing-address',
				'description' => 'If checked, the order billing address will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_shipping_address'  => array(
				'title'       => 'Order Shipping Address',
				'label'       => 'Send Order Shipping Address',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-shipping-address',
				'description' => 'If checked, the order shipping address will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'meta_products'  => array(
				'title'       => 'Product(s) Purchased',
				'label'       => 'Send Product(s) Purchased',
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-products',
				'description' => 'If checked, the product(s) purchased will be sent to Paystack',
				'default'     => 'no',
				'desc_tip'    => true
			),
		);

		if ( 'GHS' == get_woocommerce_currency() ) {
			unset( $form_fields['custom_gateways'] );
		}

		$this->form_fields = $form_fields;

	}


	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {

		if ( $this->description ) {
			echo wpautop( wptexturize( $this->description ) );
		}

		if ( ! is_ssl() ){
			return;
		}

		if ( $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards && is_user_logged_in() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->save_payment_method_checkbox();
		}

	}


	/**
	 * Outputs scripts used for paystack payment
	 */
	public function payment_scripts() {

		if ( ! is_checkout_pay_page() ) {
			return;
		}

		if ( $this->enabled === 'no' ) {
			return;
		}

		$order_key 		= urldecode( $_GET['key'] );
		$order_id  		= absint( get_query_var( 'order-pay' ) );

		$order  		= wc_get_order( $order_id );

		$payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;

		if( $this->id !== $payment_method ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'paystack', 'https://js.paystack.co/v1/inline.js', array( 'jquery' ), WC_PAYSTACK_VERSION, false );

		wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/paystack'. $suffix . '.js', WC_PAYSTACK_MAIN_FILE ), array( 'jquery', 'paystack' ), WC_PAYSTACK_VERSION, false );

		$paystack_params = array(
			'key'	=> $this->public_key
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email  		= method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;

			$amount 		= $order->get_total() * 100;

			$txnref		 	= $order_id . '_' .time();

			$the_order_id 	= method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
	        $the_order_key 	= method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$paystack_params['email'] 				= $email;
				$paystack_params['amount']  			= $amount;
				$paystack_params['txnref']  			= $txnref;
				$paystack_params['pay_page']  			= $this->payment_page;
				$paystack_params['currency']  			= get_woocommerce_currency();

			}

			if( $this->custom_metadata ) {

				if( $this->meta_order_id ) {

					$paystack_params['meta_order_id'] = $order_id;

				}

				if( $this->meta_name ) {

					$first_name  	= method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
					$last_name  	= method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;

					$paystack_params['meta_name'] = $first_name . ' ' . $last_name;

				}

				if( $this->meta_email ) {

					$paystack_params['meta_email'] = $email;

				}

				if( $this->meta_phone ) {

					$billing_phone  	= method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;

					$paystack_params['meta_phone'] = $billing_phone;

				}

				if( $this->meta_products ) {

					$line_items     = $order->get_items();

					$products 		= '';

					foreach ( $line_items as $item_id => $item ) {
						$name = $item['name'];
						$quantity = $item['qty'];
						$products .= $name .' (Qty: ' . $quantity .')';
						$products .= ' | ';
					}

					$products = rtrim( $products, ' | ' );

					$paystack_params['meta_products'] = $products;

				}

				if( $this->meta_billing_address ) {

					$billing_address 	= $order->get_formatted_billing_address();
					$billing_address 	= esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$paystack_params['meta_billing_address'] = $billing_address;

				}

				if( $this->meta_shipping_address ) {

					$shipping_address 	= $order->get_formatted_shipping_address();
					$shipping_address 	= esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

					if( empty( $shipping_address ) ) {

						$billing_address 	= $order->get_formatted_billing_address();
						$billing_address 	= esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

						$shipping_address = $billing_address;

					}

					$paystack_params['meta_shipping_address'] = $shipping_address;

				}


			}

			update_post_meta( $order_id, '_paystack_txn_ref', $txnref );

		}

		wp_localize_script( 'wc_paystack', 'wc_paystack_params', $paystack_params );

	}

	/**
	 * Load admin scripts
	 */
	public function admin_scripts() {

		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$paystack_admin_params = array(
			'plugin_url'	=> WC_PAYSTACK_URL
		);

		wp_enqueue_script( 'wc_paystack_admin', plugins_url( 'assets/js/paystack-admin' . $suffix . '.js', WC_PAYSTACK_MAIN_FILE ), array(), WC_PAYSTACK_VERSION, true );

		wp_localize_script( 'wc_paystack_admin', 'wc_paystack_admin_params', $paystack_admin_params );

	}


	/**
	 * Process the payment
	 */
	public function process_payment( $order_id ) {

		if ( isset( $_POST['wc-paystack-payment-token'] ) && 'new' !== $_POST['wc-paystack-payment-token'] ) {

			$token_id = wc_clean( $_POST['wc-paystack-payment-token'] );
			$token    = WC_Payment_Tokens::get( $token_id );

			if ( $token->get_user_id() !== get_current_user_id() ) {

				wc_add_notice( 'Invalid token ID', 'error' );

				return;

			} else {

				$this->process_token_payment( $token->get_token(), $order_id );

				$order = wc_get_order( $order_id );

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);

			}
		} else {

			if ( is_user_logged_in() && isset( $_POST['wc-paystack-new-payment-method'] ) && true === (bool) $_POST['wc-paystack-new-payment-method'] && $this->saved_cards ) {

				update_post_meta( $order_id, '_wc_paystack_save_card', true );

			}

			$order = wc_get_order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);

		}

	}


	/**
	 * Process a token payment
	 */
	public function process_token_payment( $token, $order_id ) {

		if ( $token && $order_id ) {

			$order            = wc_get_order( $order_id );

			$email            = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;

			$order_amount     = method_exists( $order, 'get_total' ) ? $order->get_total() : $order->order_total;
			$order_amount     = $order_amount * 100;

			$paystack_url   = 'https://api.paystack.co/transaction/charge_authorization';

			$headers = array(
				'Content-Type'	=> 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key
			);

			$body = array(
				'email'						=> $email,
				'amount'					=> $order_amount,
				'authorization_code'		=> $token
			);

			$args = array(
				'body'		=> json_encode( $body ),
				'headers'	=> $headers,
				'timeout'	=> 60
			);

			$request = wp_remote_post( $paystack_url, $args );

	        if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

            	$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

			        $order              = wc_get_order( $order_id );

			        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

			        	wp_redirect( $this->get_return_url( $order ) );

						exit;

			        }

	        		$order_total        = $order->get_total();

					$order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

					$currency_symbol    = get_woocommerce_currency_symbol( $order_currency );

	        		$amount_paid        = $paystack_response->data->amount / 100;

	        		$paystack_ref       = $paystack_response->data->reference;

					$payment_currency   = $paystack_response->data->currency;

        			$gateway_symbol     = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
						$notice_type = 'notice';

						// Add Customer Order Note
	                    $order->add_order_note( $notice, 1 );

	                    // Add Admin Order Note
	                    $order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>'. $currency_symbol . $amount_paid . '</strong> while the total order amount is <strong>'. $currency_symbol . $order_total . '</strong><br />Paystack Transaction Reference: '.$paystack_ref );

						wc_add_notice( $notice, $notice_type );

					} else {

						if( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $paystack_ref );

							$notice = 'Thank you for shopping with us.<br />Your payment was successful, but the payment currency is different from the order currency.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
							$notice_type = 'notice';

							// Add Customer Order Note
		                    $order->add_order_note( $notice, 1 );

			                // Add Admin Order Note
		                	$order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Order currency is different from the payment currency.<br /> Order Currency is <strong>'. $order_currency . ' ('. $currency_symbol . ')</strong> while the payment currency is <strong>'. $payment_currency . ' ('. $gateway_symbol . ')</strong><br /><strong>Paystack Transaction Reference:</strong> ' . $paystack_ref );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $paystack_ref );

							$order->add_order_note( sprintf( 'Payment via Paystack successful (Transaction Reference: %s)', $paystack_ref ) );

						}

					}

					$this->save_subscription_payment_token( $order_id, $paystack_response );

					wc_empty_cart();

				} else {

			        $order = wc_get_order( $order_id );

					$order->update_status( 'failed', 'Payment was declined by Paystack.' );

					wc_add_notice( 'Payment Failed. Try again.', 'error' );

				}

	        }
		} else {

			wc_add_notice( 'Payment Failed.', 'error' );

		}

	}


	/**
	 * Show new card can only be added when placing an order notice
	 */
	public function add_payment_method() {

		wc_add_notice( 'You can only add a new card when placing an order.', 'error' );

		return;

	}


	/**
	 * Displays the payment page
	 */
	public function receipt_page( $order_id ) {

		$order = wc_get_order( $order_id );

		if( 'embed' == $this->payment_page ) {

			echo '<p style="text-align: center; font-weight: bold;">Thank you for your order, please make payment below using Paystack.</p>';

			echo '<div id="paystackWooCommerceEmbedContainer"></div>';

			echo '<div id="paystack_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paystack_Gateway' ) .'"></form>
				<a href="' . esc_url( $order->get_cancel_order_url() ) . '" style="text-align:center; color: #EF3315; display: block; outline: none;">Cancel order &amp; restore cart</a></div>
				';

		} else {

			echo '<p>Thank you for your order, please click the button below to pay with Paystack.</p>';

			echo '<div id="paystack_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paystack_Gateway' ) .'"></form><button class="button alt" id="paystack-payment-button">Pay Now</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">Cancel order &amp; restore cart</a></div>
				';

		}

	}


	/**
	 * Verify Paystack payment
	 */
	public function verify_paystack_transaction() {

		@ob_clean();

		if ( isset( $_REQUEST['paystack_txnref'] ) ){

			$paystack_url = 'https://api.paystack.co/transaction/verify/' . $_REQUEST['paystack_txnref'];

			$headers = array(
				'Authorization' => 'Bearer ' . $this->secret_key
			);

			$args = array(
				'headers'	=> $headers,
				'timeout'	=> 60
			);

			$request = wp_remote_get( $paystack_url, $args );

	        if ( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

            	$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

					$order_details 	= explode( '_', $paystack_response->data->reference );

					$order_id 		= (int) $order_details[0];

			        $order 			= wc_get_order( $order_id );

			        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

			        	wp_redirect( $this->get_return_url( $order ) );

						exit;

			        }

	        		$order_total        = $order->get_total();

					$order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

					$currency_symbol	= get_woocommerce_currency_symbol( $order_currency );

	        		$amount_paid        = $paystack_response->data->amount / 100;

	        		$paystack_ref       = $paystack_response->data->reference;

					$payment_currency   = $paystack_response->data->currency;

        			$gateway_symbol     = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
						$notice_type = 'notice';

						// Add Customer Order Note
	                    $order->add_order_note( $notice, 1 );

	                    // Add Admin Order Note
	                    $order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>&#8358;'.$amount_paid.'</strong> while the total order amount is <strong>&#8358;'.$order_total.'</strong><br />Paystack Transaction Reference: '.$paystack_ref );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {

						if( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $paystack_ref );

							$notice = 'Thank you for shopping with us.<br />Your payment was successful, but the payment currency is different from the order currency.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
							$notice_type = 'notice';

							// Add Customer Order Note
		                    $order->add_order_note( $notice, 1 );

			                // Add Admin Order Note
		                	$order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Order currency is different from the payment currency.<br /> Order Currency is <strong>'. $order_currency . ' ('. $currency_symbol . ')</strong> while the payment currency is <strong>'. $payment_currency . ' ('. $gateway_symbol . ')</strong><br /><strong>Paystack Transaction Reference:</strong> ' . $paystack_ref );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $paystack_ref );

							$order->add_order_note( sprintf( 'Payment via Paystack successful (Transaction Reference: %s)', $paystack_ref ) );

						}

					}

					$this->save_card_details( $paystack_response, $order->get_user_id(), $order_id );

					wc_empty_cart();

				} else {

					$order_details 	= explode( '_', $_REQUEST['paystack_txnref'] );

					$order_id 		= (int) $order_details[0];

			        $order 			= wc_get_order( $order_id );

					$order->update_status( 'failed', 'Payment was declined by Paystack.' );

				}

	        }

			wp_redirect( $this->get_return_url( $order ) );

			exit;
		}

		wp_redirect( wc_get_page_permalink( 'cart' ) );

		exit;

	}


	/**
	 * Process Webhook
	 */
	public function process_webhooks() {

		if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists('HTTP_X_PAYSTACK_SIGNATURE', $_SERVER) ) {
			exit;
		}

	    $json = file_get_contents( "php://input" );

		// validate event do all at once to avoid timing attack
		if ( $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
			exit;
		}

	    $event = json_decode( $json );

	    if ( 'charge.success' == $event->event ) {

			http_response_code( 200 );

			$order_details 		= explode( '_', $event->data->reference );

			$order_id 			= (int) $order_details[0];

	        $order 				= wc_get_order($order_id);

	        $paystack_txn_ref 	= get_post_meta( $order_id, '_paystack_txn_ref', true );

	        if ( $event->data->reference != $paystack_txn_ref ) {
	        	exit;
	        }

	        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
				exit;
	        }

			$order_currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

			$currency_symbol    = get_woocommerce_currency_symbol( $order_currency );

    		$order_total        = $order->get_total();

    		$amount_paid        = $event->data->amount / 100;

    		$paystack_ref       = $event->data->reference;

			$payment_currency   = $event->data->currency;

        	$gateway_symbol     = get_woocommerce_currency_symbol( $payment_currency );

			// check if the amount paid is equal to the order amount.
			if ( $amount_paid < $order_total ) {

				$order->update_status( 'on-hold', '' );

				add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

				$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
				$notice_type = 'notice';

				// Add Customer Order Note
                $order->add_order_note( $notice, 1 );

                // Add Admin Order Note
                $order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>'. $currency_symbol . $amount_paid . '</strong> while the total order amount is <strong>'. $currency_symbol . $order_total . '</strong><br />Paystack Transaction Reference: '.$paystack_ref );

				function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

				wc_add_notice( $notice, $notice_type );

				wc_empty_cart();

			} else {

				if( $payment_currency !== $order_currency ) {

					$order->update_status( 'on-hold', '' );

					update_post_meta( $order_id, '_transaction_id', $paystack_ref );

					$notice = 'Thank you for shopping with us.<br />Your payment was successful, but the payment currency is different from the order currency.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
					$notice_type = 'notice';

					// Add Customer Order Note
                    $order->add_order_note( $notice, 1 );

	                // Add Admin Order Note
                	$order->add_order_note( '<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Order currency is different from the payment currency.<br /> Order Currency is <strong>'. $order_currency . ' ('. $currency_symbol . ')</strong> while the payment currency is <strong>'. $payment_currency . ' ('. $gateway_symbol . ')</strong><br /><strong>Paystack Transaction Reference:</strong> ' . $paystack_ref );

					function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

					wc_add_notice( $notice, $notice_type );

				} else {

					$order->payment_complete( $paystack_ref );

					$order->add_order_note( sprintf( 'Payment via Paystack successful (Transaction Reference: %s)', $paystack_ref ) );

					wc_empty_cart();

				}

			}

			$this->save_card_details( $event, $order->get_user_id(), $order_id );

			exit;
	    }

	    exit;

	}


	/**
	 * Save Customer Card Details
	 */
	public function save_card_details( $paystack_response, $user_id, $order_id ) {

		$this->save_subscription_payment_token( $order_id, $paystack_response );

		$save_card = get_post_meta( $order_id, '_wc_paystack_save_card', true );

		if ( $user_id && $this->saved_cards && $save_card && $paystack_response->data->authorization->reusable ) {

			$last4 		= $paystack_response->data->authorization->last4;
			$exp_year 	= $paystack_response->data->authorization->exp_year;
			$brand 		= $paystack_response->data->authorization->card_type;
			$exp_month 	= $paystack_response->data->authorization->exp_month;
			$auth_code 	= $paystack_response->data->authorization->authorization_code;

			$token = new WC_Payment_Token_CC();
			$token->set_token( $auth_code );
			$token->set_gateway_id( 'paystack' );
			$token->set_card_type( strtolower( $brand ) );
			$token->set_last4( $last4 );
			$token->set_expiry_month( $exp_month  );
			$token->set_expiry_year( $exp_year );
			$token->set_user_id( $user_id );
			$token->save();

			delete_post_meta( $order_id, '_wc_paystack_save_card' );

		}

	}


	/**
	 * Save payment token to the order for automatic renewal for further subscription payment
	 */
	public function save_subscription_payment_token( $order_id, $paystack_response ) {

		if ( ! function_exists ( 'wcs_order_contains_subscription' ) ) {

			return;

		}

		if ( $this->order_contains_subscription( $order_id ) && $paystack_response->data->authorization->reusable ) {

			$auth_code 	= $paystack_response->data->authorization->authorization_code;

			// Also store it on the subscriptions being purchased or paid for in the order
			if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );

			} else {

				$subscriptions = array();

			}

			foreach ( $subscriptions as $subscription ) {

				update_post_meta( $subscription->id, '_paystack_token', $auth_code );

			}

		}

	}

}