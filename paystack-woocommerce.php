<?php
/*
	Plugin Name:	Paystack WooCommerce Payment Gateway
	Plugin URI: 	https://paystack.co
	Description: 	A payment gateway for paystack.co
	Version: 		1.0.0
	Author: 		Tunbosun Ayinla
	Author URI: 	http://bosun.me
	License:        GPL-2.0+
	License URI:    http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action( 'plugins_loaded', 'tbz_wc_paystack_init', 0 );

function tbz_wc_paystack_init() {

	class Tbz_WC_Paystack_Gateway extends WC_Payment_Gateway {

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id		   			= 'paystack';
			$this->method_title 	    = 'Paystack';
			$this->method_description   = 'Paystack payment gateway';
			$this->has_fields 	    	= true;

			// Load the form fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();

			// Get setting values
			$this->title                 = $this->get_option( 'title' );
			$this->description           = $this->get_option( 'description' );
			$this->enabled               = $this->get_option( 'enabled' );
			$this->testmode              = $this->get_option( 'testmode' ) === 'yes' ? true : false;
			$this->merchant_id			 = $this->get_option( 'merchant_id' );
			$this->inline_checkout		 = $this->get_option( 'inline_checkout' ) === 'yes' ? true : false;
			$this->test_publishable_key  = $this->get_option( 'test_publishable_key' );
			$this->live_publishable_key  = $this->get_option( 'live_publishable_key' );

			$this->publishable_key       = $this->testmode ? $this->test_publishable_key : $this->live_publishable_key;

			if ( $this->testmode ) {
				$this->description .= '<br/>TEST MODE ENABLED. In test mode, you can use the card number 4123450131001381 with any CVC and a valid expiration date';
				$this->description  = trim( $this->description );
			}

			// Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );


			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

			// Payment listener/API hook
			add_action( 'woocommerce_api_wc_gateway_paystack', array( $this, 'verify_paystack_response' ) );
		}


		/**
		 * get_icon function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_icon() {
			$icon  = '<img src="' . plugins_url( 'assets/images/cards.png' , __FILE__ ) . '" alt="cards" />';

			return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
		}


		/**
		 * Check if SSL is enabled and notify the user
		 */
		public function admin_notices() {
			if ( $this->enabled == 'no' ) {
				return;
			}

			// Check required fields
			if ( ! $this->publishable_key ) {
				echo '<div class="error"><p>' . sprintf( 'Paystack error: Please enter your publishable key <a href="%s">here</a>', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paystack' ) ) . '</p></div>';
				return;
			}
		}

		/**
		 * Check if this gateway is enabled
		 */
		public function is_available() {

			return true;

			if ( $this->enabled == "yes" ) {

				if ( ! is_ssl() && ! $this->testmode ) {
					return false;
				}

				// Required fields check
				if ( ! $this->publishable_key ) {
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
	        <table class="form-table">
	        	<?php $this->generate_settings_html(); ?>
	        </table>
	    <?php
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
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => ''
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => ''
				),
				'testmode' => array(
					'title'       => 'Test mode',
					'label'       => 'Enable Test Mode',
					'type'        => 'checkbox',
					'description' => 'Test Mode Option',
					'default'     => 'yes'
				),
				'test_publishable_key' => array(
					'title'       => 'Test Publishable Key',
					'type'        => 'text',
					'description' => '',
					'default'     => ''
				),
				'live_publishable_key' => array(
					'title'       => 'Live Publishable Key',
					'type'        => 'text',
					'description' => '',
					'default'     => ''
				),
			);
		}

		/**
		 * payment_scripts function.
		 *
		 * Outputs scripts used for paystack payment
		 *
		 * @access public
		 */
		public function payment_scripts() {

			if ( ! is_checkout_pay_page() ) {
				return;
			}

			wp_enqueue_script( 'paystack', 'https://paystack.ng/js/inline.js', array( 'jquery' ), '1.0.0', true );

			wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/paystack.js', __FILE__ ), array('paystack'), '1.0.0', true );

			$paystack_params = array(
				'key'	=> $this->publishable_key
			);

			if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

				$order_key = urldecode( $_GET['key'] );
				$order_id  = absint( get_query_var( 'order-pay' ) );

				$order        	= wc_get_order( $order_id );
				$paystack_token = isset( $_POST['paystack_token'] ) ? wc_clean( $_POST['paystack_token'] ) : '';
				$email 			= $order->billing_email;
				$amount 		= $order->order_total * 100;

				$txnref		 	= $order_id . '_' . time();

				if ( $order->id == $order_id && $order->order_key == $order_key ) {
					$paystack_params['email'] 	= $email;
					$paystack_params['amount']  = $amount;
					$paystack_params['txnref']  = $txnref;
				}
			}

			wp_localize_script( 'wc_paystack', 'wc_paystack_params', $paystack_params );

		}

		/**
		 * Process the payment
		 */
		public function process_payment( $order_id, $retry = true ) {

			$order      = wc_get_order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);

		}

		public function receipt_page( $order_id ) {
			$order = wc_get_order( $order_id );

			echo '<p>' . __( 'Thank you for your order, please click the button below to pay with credit card using Paystack.', 'woocommerce' ) . '</p>';

			$args = array();
			$button_args = array();
			foreach ( $args as $key => $value ) {
				$button_args[] = 'data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}

			echo '<button class="button alt" id="paystack-payment-button">' . __( 'Pay Now', 'woocommerce' ) . '</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce' ) . '</a>
				';
		}

		public function verify_paystack_response(){

			if( isset( $_REQUEST['trxref'] ) ){

				$paystack_url = 'https://paystack.co/charge/verification';

				$body = array(
					'merchantid' 	=> $this->merchant_id,
					'trxref'		=> $_REQUEST['trxref']
				);

				$args = array(
					'body'	=> $body
				);

				$response = wp_remote_post( $paystack_url, $args );

				if ( is_wp_error( $response ) ) {
					return new WP_Error( 'paystack_error', 'There was a problem verifying the transaction.' );
				}

				if ( empty( $response['body'] ) ) {
					return new WP_Error( 'paystack_error', 'Empty response.' );
				}

				$paystack_response = json_decode( $response['body'] );

				dd( $paystack_response );

				if ( 'success' == $paystack_response->status ) {

					$order_details 	= explode('_', $_REQUEST['trxref'] );
					$order_id 		= $order_details[0];

					$order_id 		= (int) $order_id;

			        $order 			= wc_get_order($order_id);

					$order->payment_complete( $paystack_response->transaction->paystack_reference );

					$order->add_order_note( sprintf( 'PayStack Transaction Ref: %s', $paystack_response->transaction->paystack_reference ) );

	                die( 'Paystack IPN Processed. Payment Successful' );

				} else {

					$order_details 	= explode('_', $_REQUEST['trxref'] );
					$order_id 		= $order_details[0];

					$order_id 		= (int) $order_id;

			        $order 			= wc_get_order($order_id);

					$order->add_order_note( sprintf( 'PayStack Transaction Ref: %s', $_REQUEST['trxref'] ) );

	                die( 'Paystack IPN Processed. Payment Failed' );
				}
			}
		}

		public function paystack_request( $url, $body ){

			$args = array(
				'body'		=> $body,
				'timeout'	=> 30
			);

			$response 	= wp_safe_remote_post( $url, $args );

			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'paystack_error', 'There was a problem connecting to the payment gateway.' );
			}

			if ( empty( $response['body'] ) ) {
				return new WP_Error( 'paystack_error', 'Empty response.' );
			}

			$paystack_response = json_decode( $response['body'] );

			if ( ! empty( $paystack_response->error ) ) {
				return new WP_Error( isset( $paystack_response->status ) ? $paystack_response->status : 'paystack_error', $paystack_response->message );
			} else {
				return $paystack_response;
			}
		}
	}

	/**
 	* Add Paystack Gateway to WC
 	**/
	function tbz_wc_add_paystack_gateway( $methods ) {
		$methods[] = 'Tbz_WC_Paystack_Gateway';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_paystack_gateway' );

}