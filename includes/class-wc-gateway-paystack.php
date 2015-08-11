<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Paystack class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Paystack extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id		   			= 'paystack';
		$this->method_title 	    = 'Paystack';
		$this->method_description   = 'Paystack payment gateway';
		$this->has_fields 	    	= true;
		$this->api_endpoint	    	= 'https://paystack.ng/charge/token';

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
		$icon  = '<img src="' . WC_HTTPS::force_https_url( WC_PAYSTACK_PLUGIN_URL . '/assets/images/cards.png' ) . '" alt="cards" />';

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

		// Show message if enabled and FORCE SSL is disabled and WordpressHTTPS plugin is not detected
		if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && ! class_exists( 'WordPressHTTPS' ) ) {
			echo '<div class="error"><p>' . sprintf( 'Paystack is enabled, but the <a href="%s">force SSL option</a> is disabled; your checkout may not be secure! Please enable SSL and ensure your server has a valid SSL certificate - Paystack will only work in test mode.', admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) . '</p></div>';
		}
	}

	/**
	 * Check if this gateway is enabled
	 */
	public function is_available() {
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
        <p>The URL below should be copied and put in the Callback URL field under the Account section in your Paystack dashboard: <br><strong style="color: red"><?php echo WC()->api_request_url( 'WC_Gateway_Paystack' ) ?></strong></p>
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
			'inline_checkout' => array(
				'title'       => 'Inline Checkout',
				'label'       => 'Enable Inline Checkout',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no'
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
	 * Payment form on checkout page
	 */
	public function payment_fields() {

		if( ! $this->inline_checkout ){

			if ( $description = $this->get_description() ) {
				echo wpautop( wptexturize( $description ) );
			}

			return;
		}
		?>
		<fieldset>
			<?php
				$allowed = array(
				    'a' => array(
				        'href' => array(),
				        'title' => array()
				    ),
				    'br' => array(),
				    'em' => array(),
				    'strong' => array(),
				    'span'	=> array(
				    	'class' => array(),
				    ),
				);
				if ( $this->description ) {
					echo wpautop( wp_kses( $this->description, $allowed ) );
				}
			?>

			<fieldset id="paystack-cc-form">
				<p class="form-row form-row-wide">
					<label for="cardnumber">Card Number <span class="required">*</span></label>
					<input id="cardnumber" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" data-numeric="" data-paystack="number">
				</p>
				<p class="form-row form-row-first">
					<label for="exp">Expiry (MM/YY) <span class="required">*</span></label>
					<input id="exp" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="MM / YY" data-paystack="exp">
				</p>
				<p class="form-row form-row-last">
					<label for="cvv">Card Code <span class="required">*</span></label>
					<input id="cvv" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="CVC" data-paystack="cvc">
				</p>
				<div class="clear"></div>
			</fieldset>

		</fieldset>
		<?php
	}

	/**
	 * payment_scripts function.
	 *
	 * Outputs scripts used for paystack payment
	 *
	 * @access public
	 */
	public function payment_scripts() {

		if ( ! is_checkout() ) {
			return;
		}

		if( $this->inline_checkout ){

			wp_enqueue_script( 'wc-credit-card-form' );

			wp_enqueue_script( 'paystack', 'https://paystack.ng/js/paystack.js', array( 'jquery' ), '1.0.0', true );

			wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/pay.js', dirname( __FILE__ ) ), array('paystack'), '1.0.0', true );

			$paystack_params = array(
				'key'	=> $this->publishable_key
			);

			wp_localize_script( 'wc_paystack', 'wc_paystack_params', $paystack_params );
		}
	}

	/**
	 * Process the payment
	 */
	public function process_payment( $order_id, $retry = true ) {

		$order        	= wc_get_order( $order_id );
		$paystack_token = isset( $_POST['paystack_token'] ) ? wc_clean( $_POST['paystack_token'] ) : '';
		$email 			= $order->billing_email;
		$amount 		= $order->order_total * 100;

		$txnref		 	= $order_id . '_' . time();

		if( $this->inline_checkout ){

			try {

				if ( empty( $paystack_token ) ) {
					$error_msg = 'Please make sure your card details have been entered correctly and that your browser supports JavaScript.';

					if ( $this->testmode ) {
						$error_msg .= ' ' . 'Developers: Please make sure that you are including jQuery and there are no JavaScript errors on the page.';
					}

					throw new Exception( $error_msg );
				}

				$body = array(
					'merchantid' 	=> $this->merchant_id,
					'trxref'		=> $txnref,
					'email'			=> $email,
					'amount'		=> $amount,
					'token'			=> $paystack_token
				);

				$url = $this->api_endpoint;

				$response = $this->paystack_request( $url ,$body );

				if ( is_wp_error( $response ) ) {
					throw new Exception( $response->get_error_message() );
				}

				if ( 'success' == $response->status ) {

					$order->payment_complete( $response->transaction->paystack_reference );

					$order->add_order_note( sprintf( 'PayStack Charge Processed (Charge ID: %s)', $response->transaction->paystack_reference ) );

				} else {

					// Mark as on-hold
					$order->update_status( 'on-hold', sprintf( 'PayStack charge authorized (Charge ID: %s). Process order to take payment, or cancel to remove the pre-authorization.', $response->id ) );

					$order->reduce_order_stock();
				}

				WC()->cart->empty_cart();

				wc_add_notice( $response->message, 'success' );

				return array(
					'result'   => 'success',
					'redirect' => $this->get_return_url( $order )
				);

			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );

				$order_note = $e->getMessage();
				$order->update_status( 'failed', $order_note );

				return;
			}

		}
		else{

			$body = array(
				'merchantid' 	=> $this->merchant_id,
				'trxref'		=> $txnref,
				'email'			=> $email,
				'amount'		=> $amount,
			);

			$paystack_url = 'https://paystack.ng/request_authorization';

			$response = $this->paystack_request( $paystack_url, $body );

			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}

			if ( 'success' == $response->status ) {

				return array(
					'result'   => 'success',
					'redirect' => $response->authorization_url
				);

			} else {

				throw new Exception( $response->message );

			}
		}
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