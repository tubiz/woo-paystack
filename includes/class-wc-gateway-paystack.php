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
		$this->api_endpoint	    	= 'https://www.paystack.co/charge';

		// Load the form fields
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Get setting values
		$this->title                 = $this->get_option( 'title' );
		$this->description           = $this->get_option( 'description' );
		$this->enabled               = $this->get_option( 'enabled' );
		$this->testmode              = $this->get_option( 'testmode' ) === 'yes' ? true : false;
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
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters( 'wc_stripe_settings', array(
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
				'title'       => 'Publishable Key',
				'type'        => 'text',
				'description' => '',
				'default'     => ''
			),
		) );
	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		$checked = 1;
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
					echo apply_filters( 'wc_stripe_description', wpautop( wp_kses( $this->description, $allowed ) ) );
				}
			?>

			<fieldset id="paystack-cc-form">
				<p class="form-row form-row-wide">
					<label for="cardnumber">Card Number <span class="required">*</span></label>
					<input id="cardnumber" class="input-text wc-credit-card-form-card-number" type="text" maxlength="20" autocomplete="off" placeholder="•••• •••• •••• ••••" data-numeric="" data-pwc="number">
				</p>
				<p class="form-row form-row-first">
					<label for="exp">Expiry (MM/YY) <span class="required">*</span></label>
					<input id="exp" class="input-text wc-credit-card-form-card-expiry" type="text" autocomplete="off" placeholder="MM / YY" data-pwc="exp">
				</p>
				<p class="form-row form-row-last">
					<label for="cvv">Card Code <span class="required">*</span></label>
					<input id="cvv" class="input-text wc-credit-card-form-card-cvc" type="text" autocomplete="off" placeholder="CVC" data-pwc="cvc">
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

		wp_enqueue_script( 'wc-credit-card-form' );

		//wp_enqueue_script( 'paystack', 'https://www.paystack.co/assets/js/paystack.js', array('jquery-payment'), '1.0', true );
		wp_enqueue_script( 'paystack', plugins_url( 'assets/js/paystack.js', dirname( __FILE__ ) ), array('jquery-payment'), WC_PAYSTACK_VERSION, true );

		wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/pay.js', dirname( __FILE__ ) ), array('paystack'), WC_PAYSTACK_VERSION, true );

		$paystack_params = array(
			'key'	=> $this->publishable_key
		);

		wp_localize_script( 'wc_paystack', 'wc_paystack_params', $paystack_params );
	}

	/**
	 * Process the payment
	 */
	public function process_payment( $order_id, $retry = true ) {

		$order        	= wc_get_order( $order_id );
		$paystack_token = isset( $_POST['paystack_token'] ) ? wc_clean( $_POST['paystack_token'] ) : '';
		$paystack_txn   = isset( $_POST['paystack_txn'] ) ? wc_clean( $_POST['paystack_txn'] ) : '';

		$url = $this->api_endpoint;

		$body = array(
			'trans'	=> $paystack_txn,
			'token'	=> $paystack_token
		);

		$args = array(
			'body'	=> $body
		);

		$response 	= wp_safe_remote_post( $url, $args );
		$body 		= json_decode( wp_remote_retrieve_body( $response ) );

		$notice 	= print_r( $body, true );

		wc_add_notice( $notice, 'success' );

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order )
		);

	}
}