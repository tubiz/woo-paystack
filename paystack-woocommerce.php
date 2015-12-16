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
			$this->test_publishable_key  = $this->get_option( 'test_publishable_key' );
			$this->live_publishable_key  = $this->get_option( 'live_publishable_key' );

			$this->publishable_key       = $this->testmode ? $this->test_publishable_key : $this->live_publishable_key;

			// Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

			// Payment listener/API hook
			add_action( 'woocommerce_api_tbz_wc_paystack_gateway', array( $this, 'verify_paystack_transaction' ) );
		}


		/**
		 * display paystack payment icon
		 */
		public function get_icon() {
			$icon  = '<img src="' . plugins_url( 'assets/images/cards.png' , __FILE__ ) . '" alt="cards" />';

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
			if ( ! ( $this->merchant_id && $this->publishable_key ) ) {
				echo '<div class="error"><p>' . sprintf( 'Please enter your Paystack merchant details <a href="%s">here</a> to be able to use the Paystack WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_paystack' ) ) . '</p></div>';
				return;
			}

		}


		/**
		 * Check if this gateway is enabled
		 */
		public function is_available() {

			if ( $this->enabled == "yes" ) {

				if ( ! ( $this->merchant_id && $this->publishable_key ) ) {
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
				'merchant_id' => array(
					'title'       => 'Merchant ID',
					'type'        => 'text',
					'description' => 'Enter your PayStack Merchant ID here',
					'default'     => ''
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
		 * Outputs scripts used for paystack payment
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
				$email 			= $order->billing_email;
				$amount 		= $order->order_total * 100;

				$txnref		 	= $order_id . '_' . time();

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

			$order = wc_get_order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true )
			);

		}


		/**
		 * Displays the payment page
		 */
		public function receipt_page( $order_id ) {

			$order = wc_get_order( $order_id );

			echo '<p>Thank you for your order, please click the button below to pay with credit card using Paystack.</p>';

			echo '<div id="paystack_form"><form id="order_review" method="post" action="'. WC()->api_request_url( 'Tbz_WC_Paystack_Gateway' ) .'"></form><button class="button alt" id="paystack-payment-button">Pay Now</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">Cancel order &amp; restore cart</a></div>
				';

		}


		/**
		 * Verify paystack payment
		 */
		public function verify_paystack_transaction() {

			@ob_clean();

			header( 'HTTP/1.1 200 OK' );

			if( isset( $_REQUEST['paystack_txnref'] ) ){

				$paystack_url = 'https://paystack.ng/charge/verification';

				$body = array(
					'merchantid' 	=> $this->merchant_id,
					'trxref'		=> $_REQUEST['paystack_txnref']
				);

				$args = array(
					'body'	=> $body
				);

				$request = wp_remote_post( $paystack_url, $args );

		        if( ! is_wp_error( $request ) && 200 == wp_remote_retrieve_response_code( $request ) ) {

	            	$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

					if ( 'success' == $paystack_response->status ) {

						$order_details 	= explode('_', $_REQUEST['paystack_txnref'] );
						$order_id 		= $order_details[0];

						$order_id 		= (int) $order_id;

				        $order 			= wc_get_order($order_id);

						$order->payment_complete( $paystack_response->transaction->paystack_reference );

						$order->add_order_note( sprintf( 'PayStack Transaction Ref: %s', $paystack_response->transaction->paystack_reference ) );

						WC()->cart->empty_cart();

					}
					else {

						$order_details 	= explode('_', $_REQUEST['paystack_txnref'] );
						$order_id 		= $order_details[0];

						$order_id 		= (int) $order_id;

				        $order 			= wc_get_order($order_id);

						$order->update_status( 'failed', 'Payment was declined by Paystack.' );
					}

		        }

				wp_redirect( $this->get_return_url( $order ) );

				exit;
			}

			wp_redirect( wc_get_page_permalink( 'cart' ) );

			exit;

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