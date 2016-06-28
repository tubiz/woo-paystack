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
		$this->method_description   = 'Make payment using your debit and credit cards';
		$this->has_fields 	    	= true;

		$this->supports           	= array(
			'products',
			'tokenization'
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values
		$this->title                = 'Debit/Credit Cards';
		$this->enabled            	= $this->get_option( 'enabled' );
		$this->testmode             = $this->get_option( 'testmode' ) === 'yes' ? true : false;

		$this->test_public_key  	= $this->get_option( 'test_public_key' );
		$this->test_secret_key  	= $this->get_option( 'test_secret_key' );

		$this->live_public_key  	= $this->get_option( 'live_public_key' );
		$this->live_secret_key  	= $this->get_option( 'live_secret_key' );

		$this->saved_cards         	= $this->get_option( 'saved_cards' ) === 'yes' ? true : false;;

		$this->public_key      		= $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key      		= $this->testmode ? $this->test_secret_key : $this->live_secret_key;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook
		add_action( 'woocommerce_api_tbz_wc_paystack_gateway', array( $this, 'verify_paystack_transaction' ) );

		// Check if the gateway can be used
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}


	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_paystack_supported_currencies', array( 'NGN' ) ) ) ) {

			$this->msg = 'Paystack does not support your store currency. Kindly set it to Nigerian Naira &#8358; <a href="' . admin_url( 'admin.php?page=wc-settings&tab=general' ) . '">here</a>';

			return false;

		}

		return true;

	}

	/**
	 * Display paystack payment icon
	 */
	public function get_icon() {

		$icon  = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-woocommerce.png' , WC_PAYSTACK_MAIN_FILE ) ) . '" alt="cards" />';

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	}


	/**
	 * Check if paystack merchant details is filled
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( 'Please enter your Paystack merchant details <a href="%s">here</a> to be able to use the Paystack WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paystack' ) ) . '</p></div>';
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

        echo '<h3>Paystack</h3>';

		if ( $this->is_valid_for_use() ){

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        }
		else{	 ?>
		<div class="inline error"><p><strong>Paystack Payment Gateway Disabled</strong>: <?php echo $this->msg ?></p></div>

		<?php }

    }


	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Paystack',
				'type'        => 'checkbox',
				'description' => 'Enable Paystack as a payment option on the checkout page',
				'default'     => 'no',
				'desc_tip'    => true
			),
			'testmode' => array(
				'title'       => 'Test mode',
				'label'       => 'Enable Test Mode',
				'type'        => 'checkbox',
				'description' => 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Paystack account uncheck this.',
				'default'     => 'yes',
				'desc_tip'    => true
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
			'saved_cards' 	  => array(
				'title'       => 'Saved Cards',
				'label'       => 'Enable Payment via Saved Cards',
				'type'        => 'checkbox',
				'description' => 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Paystack servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.',
				'default'     => 'no',
				'desc_tip'    => true
			),
		);

	}


	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {

		if( ! is_ssl() ){
			wp_enqueue_style( 'paystack', plugins_url( 'assets/css/paystack.css',  WC_PAYSTACK_MAIN_FILE ) );
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

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'paystack', 'https://js.paystack.co/v1/inline.js', array( 'jquery' ), '1.0.0', true );

		wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/paystack'. $suffix . '.js', WC_PAYSTACK_MAIN_FILE ), array('paystack'), '1.0.0', true );

		$paystack_params = array(
			'key'	=> $this->public_key
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$order_key = urldecode( $_GET['key'] );
			$order_id  = absint( get_query_var( 'order-pay' ) );

			$order        	= wc_get_order( $order_id );
			$email 			= $order->billing_email;
			$amount 		= $order->order_total * 100;

			$txnref		 	= $order_id . '_' .time();

			if ( $order->id == $order_id && $order->order_key == $order_key ) {
				$paystack_params['email'] 	= $email;
				$paystack_params['amount']  = $amount;
				$paystack_params['txnref']  = $txnref;
			}

			update_post_meta( $order_id, '_paystack_txn_ref', $txnref );

		}

		wp_localize_script( 'wc_paystack', 'wc_paystack_params', $paystack_params );

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
			}
			else{
				$this->process_token_payment( $token->get_token(), $order_id );

				$order = wc_get_order( $order_id );

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);
			}
		}
		else {

			$maybe_saved_card = ! isset( $_POST['wc-paystack-new-payment-method'] ) || ! empty( $_POST['	wc-paystack-new-payment-method'] );

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

		if( $token && $order_id ) {

			$order        	= wc_get_order( $order_id );
			$email 			= $order->billing_email;
			$order_amount 	= $order->order_total * 100;

			$paystack_url = 'https://api.paystack.co/transaction/charge_authorization';

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

	        if( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

            	$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

			        $order 			= wc_get_order( $order_id );

	        		$order_total	= $order->get_total();

	        		$amount_paid	= $paystack_response->data->amount / 100;

	        		$paystack_ref 	= $paystack_response->data->reference;

					// check if the amount paid is equal to the order amount.
					if( $order_total !=  $amount_paid ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
						$notice_type = 'notice';

						// Add Customer Order Note
	                    $order->add_order_note( $notice, 1 );

	                    // Add Admin Order Note
	                    $order->add_order_note('<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>&#8358;'.$amount_paid.'</strong> while the total order amount is <strong>&#8358;'.$order_total.'</strong><br />Paystack Transaction Reference: '.$paystack_ref );

						wc_add_notice( $notice, $notice_type );

					}
					else{

						$order->payment_complete( $paystack_ref );

						$order->add_order_note( sprintf( 'PayStack Transaction Ref: %s', $paystack_ref ) );

					}

					WC()->cart->empty_cart();
				}
				else {

			        $order = wc_get_order( $order_id );

					$order->update_status( 'failed', 'Payment was declined by Paystack.' );

					wc_add_notice( 'Payment Failed. Try again.', 'error' );

				}

	        }
		}
		else {
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

		echo '<p>Thank you for your order, please click the button below to pay with debit/credit card using Paystack.</p>';

		echo '<div id="paystack_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paystack_Gateway' ) .'"></form><button class="button alt" id="paystack-payment-button">Pay Now</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">Cancel order &amp; restore cart</a></div>
			';

	}


	/**
	 * Verify Paystack payment
	 */
	public function verify_paystack_transaction() {

		@ob_clean();

		header( 'HTTP/1.1 200 OK' );

		if( isset( $_REQUEST['paystack_txnref'] ) ){

			$paystack_url = 'https://api.paystack.co/transaction/verify/' . $_REQUEST['paystack_txnref'];

			$headers = array(
				'Authorization' => 'Bearer ' . $this->secret_key
			);

			$args = array(
				'headers'	=> $headers,
				'timeout'	=> 60
			);

			$request = wp_remote_get( $paystack_url, $args );

	        if( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

            	$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

					$order_details 	= explode( '_', $paystack_response->data->reference );

					$order_id 		= (int) $order_details[0];

			        $order 			= wc_get_order($order_id);

	        		$order_total	= $order->get_total();

	        		$amount_paid	= $paystack_response->data->amount / 100;

	        		$paystack_ref 	= $paystack_response->data->reference;

					// check if the amount paid is equal to the order amount.
					if( $order_total !=  $amount_paid ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice = 'Thank you for shopping with us.<br />Your payment transaction was successful, but the amount paid is not the same as the total order amount.<br />Your order is currently on-hold.<br />Kindly contact us for more information regarding your order and payment status.';
						$notice_type = 'notice';

						// Add Customer Order Note
	                    $order->add_order_note( $notice, 1 );

	                    // Add Admin Order Note
	                    $order->add_order_note('<strong>Look into this order</strong><br />This order is currently on hold.<br />Reason: Amount paid is less than the total order amount.<br />Amount Paid was <strong>&#8358;'.$amount_paid.'</strong> while the total order amount is <strong>&#8358;'.$order_total.'</strong><br />Paystack Transaction Reference: '.$paystack_ref );

						$order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

						wc_empty_cart();
					}
					else{

						$order->payment_complete( $paystack_ref );

						$order->add_order_note( sprintf( 'PayStack Transaction Ref: %s', $paystack_ref ) );

						wc_empty_cart();
					}

					$this->save_card_details( $paystack_response, $order->get_user_id(), $order_id );

				}
				else {

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
	 * Save Customer Card Details
	 */
	public function save_card_details( $paystack_response, $user_id, $order_id ) {

		$save_card = get_post_meta( $order_id, '_wc_paystack_save_card', true );

		// Add token to WooCommerce
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

}