<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Gateway_Paystack extends WC_Payment_Gateway_CC {

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * Should orders be marked as complete after payment?
	 * 
	 * @var bool
	 */
	public $autocomplete_order;

	/**
	 * Paystack payment page type.
	 *
	 * @var string
	 */
	public $payment_page;

	/**
	 * Paystack test public key.
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * Paystack test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * Paystack live public key.
	 *
	 * @var string
	 */
	public $live_public_key;

	/**
	 * Paystack live secret key.
	 *
	 * @var string
	 */
	public $live_secret_key;

	/**
	 * Should we save customer cards?
	 *
	 * @var bool
	 */
	public $saved_cards;

	/**
	 * Should Paystack split payment be enabled.
	 *
	 * @var bool
	 */
	public $split_payment;

	/**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	/**
	 * Paystack sub account code.
	 *
	 * @var string
	 */
	public $subaccount_code;

	/**
	 * Who bears Paystack charges?
	 *
	 * @var string
	 */
	public $charges_account;

	/**
	 * A flat fee to charge the sub account for each transaction.
	 *
	 * @var string
	 */
	public $transaction_charges;

	/**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to Paystack?
	 *
	 * @var bool
	 */
	public $meta_products;

	/**
	 * API public key
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'paystack';
		$this->method_title       = __( 'Paystack', 'woo-paystack' );
		$this->method_description = sprintf( __( 'Paystack provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a Paystack account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-paystack' ), 'https://paystack.com', 'https://dashboard.paystack.com/#/settings/developer' );
		$this->has_fields         = true;

		$this->payment_page = $this->get_option( 'payment_page' );

		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values

		$this->title              = $this->get_option( 'title' );
		$this->description        = $this->get_option( 'description' );
		$this->enabled            = $this->get_option( 'enabled' );
		$this->testmode           = $this->get_option( 'testmode' ) === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option( 'autocomplete_order' ) === 'yes' ? true : false;

		$this->test_public_key = $this->get_option( 'test_public_key' );
		$this->test_secret_key = $this->get_option( 'test_secret_key' );

		$this->live_public_key = $this->get_option( 'live_public_key' );
		$this->live_secret_key = $this->get_option( 'live_secret_key' );

		$this->saved_cards = $this->get_option( 'saved_cards' ) === 'yes' ? true : false;

		$this->split_payment              = $this->get_option( 'split_payment' ) === 'yes' ? true : false;
		$this->remove_cancel_order_button = $this->get_option( 'remove_cancel_order_button' ) === 'yes' ? true : false;
		$this->subaccount_code            = $this->get_option( 'subaccount_code' );
		$this->charges_account            = $this->get_option( 'split_payment_charge_account' );
		$this->transaction_charges        = $this->get_option( 'split_payment_transaction_charge' );

		$this->custom_metadata = $this->get_option( 'custom_metadata' ) === 'yes' ? true : false;

		$this->meta_order_id         = $this->get_option( 'meta_order_id' ) === 'yes' ? true : false;
		$this->meta_name             = $this->get_option( 'meta_name' ) === 'yes' ? true : false;
		$this->meta_email            = $this->get_option( 'meta_email' ) === 'yes' ? true : false;
		$this->meta_phone            = $this->get_option( 'meta_phone' ) === 'yes' ? true : false;
		$this->meta_billing_address  = $this->get_option( 'meta_billing_address' ) === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option( 'meta_shipping_address' ) === 'yes' ? true : false;
		$this->meta_products         = $this->get_option( 'meta_products' ) === 'yes' ? true : false;

		$this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
		$this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;

		// Hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_paystack', array( $this, 'verify_paystack_transaction' ) );

		// Webhook listener/API hook.
		add_action( 'woocommerce_api_tbz_wc_paystack_webhook', array( $this, 'process_webhooks' ) );

		// Check if the gateway can be used.
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}

	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use() {

		if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_paystack_supported_currencies', array( 'NGN', 'USD', 'ZAR', 'GHS', 'KES' ) ) ) ) {

			$this->msg = sprintf( __( 'Paystack does not support your store currency. Kindly set it to either NGN (&#8358), GHS (&#x20b5;), USD (&#36;), KES (KSh) or ZAR (R) <a href="%s">here</a>', 'woo-paystack' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

			return false;

		}

		return true;

	}

	/**
	 * Display paystack payment icon.
	 */
	public function get_icon() {

		$base_location = wc_get_base_location();

		if ( 'GH' === $base_location['country'] ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-gh.png', WC_PAYSTACK_MAIN_FILE ) ) . '" alt="Paystack Payment Options" />';
		} elseif ( 'ZA' === $base_location['country'] ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-za.png', WC_PAYSTACK_MAIN_FILE ) ) . '" alt="Paystack Payment Options" />';
		} elseif ( 'KE' === $base_location['country'] ) {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-ke.png', WC_PAYSTACK_MAIN_FILE ) ) . '" alt="Paystack Payment Options" />';
		} else {
			$icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/paystack-wc.png', WC_PAYSTACK_MAIN_FILE ) ) . '" alt="Paystack Payment Options" />';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

	}

	/**
	 * Check if Paystack merchant details is filled.
	 */
	public function admin_notices() {

		if ( $this->enabled == 'no' ) {
			return;
		}

		// Check required fields.
		if ( ! ( $this->public_key && $this->secret_key ) ) {
			echo '<div class="error"><p>' . sprintf( __( 'Please enter your Paystack merchant details <a href="%s">here</a> to be able to use the Paystack WooCommerce plugin.', 'woo-paystack' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' ) ) . '</p></div>';
			return;
		}

	}

	/**
	 * Check if Paystack gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available() {

		if ( 'yes' == $this->enabled ) {

			if ( ! ( $this->public_key && $this->secret_key ) ) {

				return false;

			}

			return true;

		}

		return false;

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		?>

		<h2><?php _e( 'Paystack', 'woo-paystack' ); ?>
		<?php
		if ( function_exists( 'wc_back_link' ) ) {
			wc_back_link( __( 'Return to payments', 'woo-paystack' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
		}
		?>
		</h2>

		<h4>
			<strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-paystack' ), 'https://dashboard.paystack.co/#/settings/developer', WC()->api_request_url( 'Tbz_WC_Paystack_Webhook' ) ); ?></strong>
		</h4>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {
			?>
			<div class="inline error"><p><strong><?php _e( 'Paystack Payment Gateway Disabled', 'woo-paystack' ); ?></strong>: <?php echo $this->msg; ?></p></div>

			<?php
		}

	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {

		$form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-paystack' ),
				'label'       => __( 'Enable Paystack', 'woo-paystack' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable Paystack as a payment option on the checkout page.', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-paystack' ),
				'default'     => __( 'Debit/Credit Cards', 'woo-paystack' ),
				'desc_tip'    => true,
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-paystack' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-paystack' ),
				'default'     => __( 'Make payment using your debit and credit cards', 'woo-paystack' ),
				'desc_tip'    => true,
			),
			'testmode'                         => array(
				'title'       => __( 'Test mode', 'woo-paystack' ),
				'label'       => __( 'Enable Test Mode', 'woo-paystack' ),
				'type'        => 'checkbox',
				'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Paystack account uncheck this.', 'woo-paystack' ),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Option', 'woo-paystack' ),
				'type'        => 'select',
				'description' => __( 'Popup shows the payment popup on the page while Redirect will redirect the customer to Paystack to make payment.', 'woo-paystack' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''          => __( 'Select One', 'woo-paystack' ),
					'inline'    => __( 'Popup', 'woo-paystack' ),
					'redirect'  => __( 'Redirect', 'woo-paystack' ),
				),
			),
			'test_secret_key'                  => array(
				'title'       => __( 'Test Secret Key', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'Enter your Test Secret Key here', 'woo-paystack' ),
				'default'     => '',
			),
			'test_public_key'                  => array(
				'title'       => __( 'Test Public Key', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'Enter your Test Public Key here.', 'woo-paystack' ),
				'default'     => '',
			),
			'live_secret_key'                  => array(
				'title'       => __( 'Live Secret Key', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'Enter your Live Secret Key here.', 'woo-paystack' ),
				'default'     => '',
			),
			'live_public_key'                  => array(
				'title'       => __( 'Live Public Key', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'Enter your Live Public Key here.', 'woo-paystack' ),
				'default'     => '',
			),
			'autocomplete_order'               => array(
				'title'       => __( 'Autocomplete Order After Payment', 'woo-paystack' ),
				'label'       => __( 'Autocomplete Order', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-autocomplete-order',
				'description' => __( 'If enabled, the order will be marked as complete after successful payment', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'remove_cancel_order_button'       => array(
				'title'       => __( 'Remove Cancel Order & Restore Cart Button', 'woo-paystack' ),
				'label'       => __( 'Remove the cancel order & restore cart button on the pay for order page', 'woo-paystack' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'split_payment'                    => array(
				'title'       => __( 'Split Payment', 'woo-paystack' ),
				'label'       => __( 'Enable Split Payment', 'woo-paystack' ),
				'type'        => 'checkbox',
				'description' => '',
				'class'       => 'woocommerce_paystack_split_payment',
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'subaccount_code'                  => array(
				'title'       => __( 'Subaccount Code', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'Enter the subaccount code here.', 'woo-paystack' ),
				'class'       => 'woocommerce_paystack_subaccount_code',
				'default'     => '',
			),
			'split_payment_transaction_charge' => array(
				'title'             => __( 'Split Payment Transaction Charge', 'woo-paystack' ),
				'type'              => 'number',
				'description'       => __( 'A flat fee to charge the subaccount for this transaction, in Naira (&#8358;). This overrides the split percentage set when the subaccount was created. Ideally, you will need to use this if you are splitting in flat rates (since subaccount creation only allows for percentage split). e.g. 100 for a &#8358;100 flat fee.', 'woo-paystack' ),
				'class'             => __( 'woocommerce_paystack_split_payment_transaction_charge', 'woo-paystack' ),
				'default'           => '',
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 0.1,
				),
				'desc_tip'          => false,
			),
			'split_payment_charge_account'     => array(
				'title'       => __( 'Paystack Charges Bearer', 'woo-paystack' ),
				'type'        => 'select',
				'description' => __( 'Who bears Paystack charges?', 'woo-paystack' ),
				'class'       => 'woocommerce_paystack_split_payment_charge_account',
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''           => __( 'Select One', 'woo-paystack' ),
					'account'    => __( 'Account', 'woo-paystack' ),
					'subaccount' => __( 'Subaccount', 'woo-paystack' ),
				),
			),
			'custom_gateways'                  => array(
				'title'       => __( 'Additional Paystack Gateways', 'woo-paystack' ),
				'type'        => 'select',
				'description' => __( 'Create additional custom Paystack based gateways. This allows you to create additional Paystack gateways using custom filters. You can create a gateway that accepts only verve cards, a gateway that accepts only bank payment, a gateway that accepts a specific bank issued cards.', 'woo-paystack' ),
				'default'     => '',
				'desc_tip'    => true,
				'options'     => array(
					''  => __( 'Select One', 'woo-paystack' ),
					'1' => __( '1 gateway', 'woo-paystack' ),
					'2' => __( '2 gateways', 'woo-paystack' ),
					'3' => __( '3 gateways', 'woo-paystack' ),
					'4' => __( '4 gateways', 'woo-paystack' ),
					'5' => __( '5 gateways', 'woo-paystack' ),
				),
			),
			'saved_cards'                      => array(
				'title'       => __( 'Saved Cards', 'woo-paystack' ),
				'label'       => __( 'Enable Payment via Saved Cards', 'woo-paystack' ),
				'type'        => 'checkbox',
				'description' => __( 'If enabled, users will be able to pay with a saved card during checkout. Card details are saved on Paystack servers, not on your store.<br>Note that you need to have a valid SSL certificate installed.', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'custom_metadata'                  => array(
				'title'       => __( 'Custom Metadata', 'woo-paystack' ),
				'label'       => __( 'Enable Custom Metadata', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-metadata',
				'description' => __( 'If enabled, you will be able to send more information about the order to Paystack.', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_order_id'                    => array(
				'title'       => __( 'Order ID', 'woo-paystack' ),
				'label'       => __( 'Send Order ID', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-order-id',
				'description' => __( 'If checked, the Order ID will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_name'                        => array(
				'title'       => __( 'Customer Name', 'woo-paystack' ),
				'label'       => __( 'Send Customer Name', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-name',
				'description' => __( 'If checked, the customer full name will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_email'                       => array(
				'title'       => __( 'Customer Email', 'woo-paystack' ),
				'label'       => __( 'Send Customer Email', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-email',
				'description' => __( 'If checked, the customer email address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_phone'                       => array(
				'title'       => __( 'Customer Phone', 'woo-paystack' ),
				'label'       => __( 'Send Customer Phone', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-phone',
				'description' => __( 'If checked, the customer phone will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_billing_address'             => array(
				'title'       => __( 'Order Billing Address', 'woo-paystack' ),
				'label'       => __( 'Send Order Billing Address', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-billing-address',
				'description' => __( 'If checked, the order billing address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_shipping_address'            => array(
				'title'       => __( 'Order Shipping Address', 'woo-paystack' ),
				'label'       => __( 'Send Order Shipping Address', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-shipping-address',
				'description' => __( 'If checked, the order shipping address will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_products'                    => array(
				'title'       => __( 'Product(s) Purchased', 'woo-paystack' ),
				'label'       => __( 'Send Product(s) Purchased', 'woo-paystack' ),
				'type'        => 'checkbox',
				'class'       => 'wc-paystack-meta-products',
				'description' => __( 'If checked, the product(s) purchased will be sent to Paystack', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);

		if ( 'NGN' !== get_woocommerce_currency() ) {
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

		if ( ! is_ssl() ) {
			return;
		}

		if ( $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards && is_user_logged_in() ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
			$this->save_payment_method_checkbox();
		}

	}

	/**
	 * Outputs scripts used for paystack payment.
	 */
	public function payment_scripts() {

		if ( ! is_checkout_pay_page() ) {
			return;
		}

		if ( $this->enabled === 'no' ) {
			return;
		}

		$order_key = urldecode( $_GET['key'] );
		$order_id  = absint( get_query_var( 'order-pay' ) );

		$order = wc_get_order( $order_id );

		$payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;

		if ( $this->id !== $payment_method ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery' );

		wp_enqueue_script( 'paystack', 'https://js.paystack.co/v1/inline.js', array( 'jquery' ), WC_PAYSTACK_VERSION, false );

		wp_enqueue_script( 'wc_paystack', plugins_url( 'assets/js/paystack' . $suffix . '.js', WC_PAYSTACK_MAIN_FILE ), array( 'jquery', 'paystack' ), WC_PAYSTACK_VERSION, false );

		$paystack_params = array(
			'key' => $this->public_key,
		);

		if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

			$email         = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
			$amount        = $order->get_total() * 100;
			$txnref        = $order_id . '_' . time();
			$the_order_id  = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$the_order_key = method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;
			$currency      = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$paystack_params['email']    = $email;
				$paystack_params['amount']   = $amount;
				$paystack_params['txnref']   = $txnref;
				$paystack_params['currency'] = $currency;

			}

			if ( $this->split_payment ) {

				$paystack_params['subaccount_code'] = $this->subaccount_code;
				$paystack_params['charges_account'] = $this->charges_account;

				if ( empty( $this->transaction_charges ) ) {
					$paystack_params['transaction_charges'] = '';
				} else {
					$paystack_params['transaction_charges'] = $this->transaction_charges * 100;
				}
			}

			if ( $this->custom_metadata ) {

				if ( $this->meta_order_id ) {

					$paystack_params['meta_order_id'] = $order_id;

				}

				if ( $this->meta_name ) {

					$first_name = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
					$last_name  = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;

					$paystack_params['meta_name'] = $first_name . ' ' . $last_name;

				}

				if ( $this->meta_email ) {

					$paystack_params['meta_email'] = $email;

				}

				if ( $this->meta_phone ) {

					$billing_phone = method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;

					$paystack_params['meta_phone'] = $billing_phone;

				}

				if ( $this->meta_products ) {

					$line_items = $order->get_items();

					$products = '';

					foreach ( $line_items as $item_id => $item ) {
						$name      = $item['name'];
						$quantity  = $item['qty'];
						$products .= $name . ' (Qty: ' . $quantity . ')';
						$products .= ' | ';
					}

					$products = rtrim( $products, ' | ' );

					$paystack_params['meta_products'] = $products;

				}

				if ( $this->meta_billing_address ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$paystack_params['meta_billing_address'] = $billing_address;

				}

				if ( $this->meta_shipping_address ) {

					$shipping_address = $order->get_formatted_shipping_address();
					$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

					if ( empty( $shipping_address ) ) {

						$billing_address = $order->get_formatted_billing_address();
						$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

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
	 * Load admin scripts.
	 */
	public function admin_scripts() {

		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$paystack_admin_params = array(
			'plugin_url' => WC_PAYSTACK_URL,
		);

		wp_enqueue_script( 'wc_paystack_admin', plugins_url( 'assets/js/paystack-admin' . $suffix . '.js', WC_PAYSTACK_MAIN_FILE ), array(), WC_PAYSTACK_VERSION, true );

		wp_localize_script( 'wc_paystack_admin', 'wc_paystack_admin_params', $paystack_admin_params );

	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment( $order_id ) {

		if ( 'redirect' === $this->payment_page ) {

			return $this->process_redirect_payment_option( $order_id );

		} elseif ( isset( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $this->id . '-payment-token' ] ) {

			$token_id = wc_clean( $_POST[ 'wc-' . $this->id . '-payment-token' ] );
			$token    = WC_Payment_Tokens::get( $token_id );

			if ( $token->get_user_id() !== get_current_user_id() ) {

				wc_add_notice( 'Invalid token ID', 'error' );

				return;

			} else {

				$status = $this->process_token_payment( $token->get_token(), $order_id );

				if ( $status ) {

					$order = wc_get_order( $order_id );

					return array(
						'result'   => 'success',
						'redirect' => $this->get_return_url( $order ),
					);

				}
			}
		} else {

			if ( is_user_logged_in() && isset( $_POST[ 'wc-' . $this->id . '-new-payment-method' ] ) && true === (bool) $_POST[ 'wc-' . $this->id . '-new-payment-method' ] && $this->saved_cards ) {

				update_post_meta( $order_id, '_wc_paystack_save_card', true );

			}

			$order = wc_get_order( $order_id );

			return array(
				'result'   => 'success',
				'redirect' => $order->get_checkout_payment_url( true ),
			);

		}

	}

	/**
	 * Process a redirect payment option payment.
	 *
	 * @since 5.7
	 * @param int $order_id
	 * @return array|void
	 */
	public function process_redirect_payment_option( $order_id ) {

		$order        = wc_get_order( $order_id );
		$email        = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
		$amount       = $order->get_total() * 100;
		$txnref       = $order_id . '_' . time();
		$currency     = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;
		$callback_url = WC()->api_request_url( 'WC_Gateway_Paystack' );

		$payment_channels = $this->get_gateway_payment_channels( $order );

		$paystack_params = array(
			'amount'       => $amount,
			'email'        => $email,
			'currency'     => $currency,
			'reference'    => $txnref,
			'callback_url' => $callback_url,
		);

		if ( ! empty( $payment_channels ) ) {
			$paystack_params['channels'] = $payment_channels;
		}

		if ( $this->split_payment ) {

			$paystack_params['subaccount'] = $this->subaccount_code;
			$paystack_params['bearer']     = $this->charges_account;

			if ( empty( $this->transaction_charges ) ) {
				$paystack_params['transaction_charge'] = '';
			} else {
				$paystack_params['transaction_charge'] = $this->transaction_charges * 100;
			}
		}

		$paystack_params['metadata']['custom_fields'] = $this->get_custom_fields( $order_id );
		$paystack_params['metadata']['cancel_action'] = wc_get_cart_url();

		update_post_meta( $order_id, '_paystack_txn_ref', $txnref );

		$paystack_url = 'https://api.paystack.co/transaction/initialize/';

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
			'Content-Type'  => 'application/json',
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
			'body'    => json_encode( $paystack_params ),
		);

		$request = wp_remote_post( $paystack_url, $args );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

			$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

			return array(
				'result'   => 'success',
				'redirect' => $paystack_response->data->authorization_url,
			);

		} else {
			wc_add_notice( __( 'Unable to process payment try again', 'woo-paystack' ), 'error' );

			return;
		}

	}

	/**
	 * Process a token payment.
	 *
	 * @param $token
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function process_token_payment( $token, $order_id ) {

		if ( $token && $order_id ) {

			$order = wc_get_order( $order_id );

			$email        = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
			$order_amount = method_exists( $order, 'get_total' ) ? $order->get_total() : $order->order_total;
			$order_amount = $order_amount * 100;

			$paystack_url = 'https://api.paystack.co/transaction/charge_authorization';

			$headers = array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key,
			);

			$metadata['custom_fields'] = $this->get_custom_fields( $order_id );

			$body = array(
				'email'              => $email,
				'amount'             => $order_amount,
				'metadata'           => $metadata,
				'authorization_code' => $token,
			);

			$args = array(
				'body'    => json_encode( $body ),
				'headers' => $headers,
				'timeout' => 60,
			);

			$request = wp_remote_post( $paystack_url, $args );

			if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

				$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

					$order = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $paystack_response->data->amount / 100;
					$paystack_ref     = $paystack_response->data->reference;
					$payment_currency = $paystack_response->data->currency;
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $paystack_ref );
						$order->add_order_note( $admin_order_note );

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $paystack_ref );

							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $paystack_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $paystack_ref );

							$order->add_order_note( sprintf( 'Payment via Paystack successful (Transaction Reference: %s)', $paystack_ref ) );

						}
					}

					$this->save_subscription_payment_token( $order_id, $paystack_response );

					WC()->cart->empty_cart();

					return true;

				} else {

					$order_notice  = __( 'Payment was declined by Paystack.', 'woo-paystack' );
					$failed_notice = __( 'Payment failed using the saved card. Kindly use another payment option.', 'woo-paystack' );

					if ( isset( $paystack_response->data->gateway_response ) && ! empty( $paystack_response->data->gateway_response ) ) {

						$order_notice  = sprintf( __( 'Payment was declined by Paystack. Reason: %s.', 'woo-paystack' ), $paystack_response->data->gateway_response );
						$failed_notice = sprintf( __( 'Payment failed using the saved card. Reason: %s. Kindly use another payment option.', 'woo-paystack' ), $paystack_response->data->gateway_response );

					}

					$order->update_status( 'failed', $order_notice );

					wc_add_notice( $failed_notice, 'error' );

					return false;

				}
			}
		} else {

			wc_add_notice( __( 'Payment Failed.', 'woo-paystack' ), 'error' );

		}

	}

	/**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method() {

		wc_add_notice( __( 'You can only add a new card when placing an order.', 'woo-paystack' ), 'error' );

		return;

	}

	/**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page( $order_id ) {

		$order = wc_get_order( $order_id );

		echo '<div id="wc-paystack-form">';

		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Paystack.', 'woo-paystack' ) . '</p>';

		echo '<div id="paystack_form"><form id="order_review" method="post" action="' . WC()->api_request_url( 'WC_Gateway_Paystack' ) . '"></form><button class="button" id="paystack-payment-button">' . __( 'Pay Now', 'woo-paystack' ) . '</button>';

		if ( ! $this->remove_cancel_order_button ) {
			echo '  <a class="button cancel" id="paystack-cancel-payment-button" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woo-paystack' ) . '</a></div>';
		}

		echo '</div>';

	}

	/**
	 * Verify Paystack payment.
	 */
	public function verify_paystack_transaction() {

		if ( isset( $_REQUEST['paystack_txnref'] ) ) {
			$paystack_txn_ref = sanitize_text_field( $_REQUEST['paystack_txnref'] );
		} elseif ( isset( $_REQUEST['reference'] ) ) {
			$paystack_txn_ref = sanitize_text_field( $_REQUEST['reference'] );
		} else {
			$paystack_txn_ref = false;
		}

		@ob_clean();

		if ( $paystack_txn_ref ) {

			$paystack_url = 'https://api.paystack.co/transaction/verify/' . $paystack_txn_ref;

			$headers = array(
				'Authorization' => 'Bearer ' . $this->secret_key,
			);

			$args = array(
				'headers' => $headers,
				'timeout' => 60,
			); 

			$request = wp_remote_get( $paystack_url, $args );

			if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

				$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

				if ( 'success' == $paystack_response->data->status ) {

					$order_details = explode( '_', $paystack_response->data->reference );
					$order_id      = (int) $order_details[0];
					$order         = wc_get_order( $order_id );

					if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

						wp_redirect( $this->get_return_url( $order ) );

						exit;

					}

					$order_total      = $order->get_total();
					$order_currency   = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
					$currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
					$amount_paid      = $paystack_response->data->amount / 100;
					$paystack_ref     = $paystack_response->data->reference;
					$payment_currency = strtoupper( $paystack_response->data->currency );
					$gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );

					// check if the amount paid is equal to the order amount.
					if ( $amount_paid < $order_total ) {

						$order->update_status( 'on-hold', '' );

						add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

						$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note( $notice, 1 );

						// Add Admin Order Note
						$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $paystack_ref );
						$order->add_order_note( $admin_order_note );

						function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

						wc_add_notice( $notice, $notice_type );

					} else {

						if ( $payment_currency !== $order_currency ) {

							$order->update_status( 'on-hold', '' );

							update_post_meta( $order_id, '_transaction_id', $paystack_ref );

							$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
							$notice_type = 'notice';

							// Add Customer Order Note
							$order->add_order_note( $notice, 1 );

							// Add Admin Order Note
							$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $paystack_ref );
							$order->add_order_note( $admin_order_note );

							function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

							wc_add_notice( $notice, $notice_type );

						} else {

							$order->payment_complete( $paystack_ref );
							$order->add_order_note( sprintf( __( 'Payment via Paystack successful (Transaction Reference: %s)', 'woo-paystack' ), $paystack_ref ) );

							if ( $this->is_autocomplete_order_enabled( $order ) ) {
								$order->update_status( 'completed' );
							}
						}
					}

					$this->save_card_details( $paystack_response, $order->get_user_id(), $order_id );

					WC()->cart->empty_cart();

				} else {

					$order_details = explode( '_', $_REQUEST['paystack_txnref'] );

					$order_id = (int) $order_details[0];

					$order = wc_get_order( $order_id );

					$order->update_status( 'failed', __( 'Payment was declined by Paystack.', 'woo-paystack' ) );

				}
			}

			wp_redirect( $this->get_return_url( $order ) );

			exit;
		}

		wp_redirect( wc_get_page_permalink( 'cart' ) );

		exit;

	}

	/**
	 * Process Webhook.
	 */
	public function process_webhooks() {

		if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' ) || ! array_key_exists( 'HTTP_X_PAYSTACK_SIGNATURE', $_SERVER ) ) {
			exit;
		}

		$json = file_get_contents( 'php://input' );

		// validate event do all at once to avoid timing attack.
		if ( $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac( 'sha512', $json, $this->secret_key ) ) {
			exit;
		}

		$event = json_decode( $json );

		if ( 'charge.success' == $event->event ) {

			sleep( 10 );

			$order_details = explode( '_', $event->data->reference );

			$order_id = (int) $order_details[0];

			$order = wc_get_order( $order_id );

			$paystack_txn_ref = get_post_meta( $order_id, '_paystack_txn_ref', true );

			if ( $event->data->reference != $paystack_txn_ref ) {
				exit;
			}

			http_response_code( 200 );

			if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
				exit;
			}

			$order_currency = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

			$currency_symbol = get_woocommerce_currency_symbol( $order_currency );

			$order_total = $order->get_total();

			$amount_paid = $event->data->amount / 100;

			$paystack_ref = $event->data->reference;

			$payment_currency = strtoupper( $event->data->currency );

			$gateway_symbol = get_woocommerce_currency_symbol( $payment_currency );

			// check if the amount paid is equal to the order amount.
			if ( $amount_paid < $order_total ) {

				$order->update_status( 'on-hold', '' );

				add_post_meta( $order_id, '_transaction_id', $paystack_ref, true );

				$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
				$notice_type = 'notice';

				// Add Customer Order Note.
				$order->add_order_note( $notice, 1 );

				// Add Admin Order Note.
				$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $paystack_ref );
				$order->add_order_note( $admin_order_note );

				function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

				wc_add_notice( $notice, $notice_type );

				WC()->cart->empty_cart();

			} else {

				if ( $payment_currency !== $order_currency ) {

					$order->update_status( 'on-hold', '' );

					update_post_meta( $order_id, '_transaction_id', $paystack_ref );

					$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-paystack' ), '<br />', '<br />', '<br />' );
					$notice_type = 'notice';

					// Add Customer Order Note.
					$order->add_order_note( $notice, 1 );

					// Add Admin Order Note.
					$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'woo-paystack' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $paystack_ref );
					$order->add_order_note( $admin_order_note );

					function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

					wc_add_notice( $notice, $notice_type );

				} else {

					$order->payment_complete( $paystack_ref );

					$order->add_order_note( sprintf( __( 'Payment via Paystack successful (Transaction Reference: %s)', 'woo-paystack' ), $paystack_ref ) );

					WC()->cart->empty_cart();

					if ( $this->is_autocomplete_order_enabled( $order ) ) {
						$order->update_status( 'completed' );
					}
				}
			}

			$this->save_card_details( $event, $order->get_user_id(), $order_id );

			exit;
		}

		exit;

	}

	/**
	 * Save Customer Card Details.
	 *
	 * @param $paystack_response
	 * @param $user_id
	 * @param $order_id
	 */
	public function save_card_details( $paystack_response, $user_id, $order_id ) {

		$this->save_subscription_payment_token( $order_id, $paystack_response );

		$save_card = get_post_meta( $order_id, '_wc_paystack_save_card', true );

		if ( $user_id && $this->saved_cards && $save_card && $paystack_response->data->authorization->reusable && 'card' == $paystack_response->data->authorization->channel ) {

			$order = wc_get_order( $order_id );

			$gateway_id = $order->get_payment_method();

			$last4     = $paystack_response->data->authorization->last4;
			$exp_year  = $paystack_response->data->authorization->exp_year;
			$brand     = $paystack_response->data->authorization->card_type;
			$exp_month = $paystack_response->data->authorization->exp_month;
			$auth_code = $paystack_response->data->authorization->authorization_code;

			$token = new WC_Payment_Token_CC();
			$token->set_token( $auth_code );
			$token->set_gateway_id( $gateway_id );
			$token->set_card_type( strtolower( $brand ) );
			$token->set_last4( $last4 );
			$token->set_expiry_month( $exp_month );
			$token->set_expiry_year( $exp_year );
			$token->set_user_id( $user_id );
			$token->save();

			delete_post_meta( $order_id, '_wc_paystack_save_card' );

		}

	}

	/**
	 * Save payment token to the order for automatic renewal for further subscription payment.
	 *
	 * @param $order_id
	 * @param $paystack_response
	 */
	public function save_subscription_payment_token( $order_id, $paystack_response ) {

		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return;
		}

		if ( $this->order_contains_subscription( $order_id ) && $paystack_response->data->authorization->reusable && 'card' == $paystack_response->data->authorization->channel ) {

			$auth_code = $paystack_response->data->authorization->authorization_code;

			// Also store it on the subscriptions being purchased or paid for in the order
			if ( function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_order( $order_id );

			} elseif ( function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id ) ) {

				$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );

			} else {

				$subscriptions = array();

			}

			foreach ( $subscriptions as $subscription ) {

				$subscription_id = $subscription->get_id();

				update_post_meta( $subscription_id, '_paystack_token', $auth_code );

			}
		}

	}

	/**
	 * Get custom fields to pass to Paystack.
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields( $order_id ) {

		$order = wc_get_order( $order_id );

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'woo-paystack',
		);
		
		if ( $this->custom_metadata ) {

			if ( $this->meta_order_id ) {

				$custom_fields[] = array(
					'display_name'  => 'Order ID',
					'variable_name' => 'order_id',
					'value'         => $order_id,
				);

			}

			if ( $this->meta_name ) {

				$first_name = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
				$last_name  = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;

				$custom_fields[] = array(
					'display_name'  => 'Customer Name',
					'variable_name' => 'customer_name',
					'value'         => $first_name . ' ' . $last_name,
				);

			}

			if ( $this->meta_email ) {

				$email = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;

				$custom_fields[] = array(
					'display_name'  => 'Customer Email',
					'variable_name' => 'customer_email',
					'value'         => $email,
				);

			}

			if ( $this->meta_phone ) {

				$billing_phone = method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;

				$custom_fields[] = array(
					'display_name'  => 'Customer Phone',
					'variable_name' => 'customer_phone',
					'value'         => $billing_phone,
				);

			}

			if ( $this->meta_products ) {

				$line_items = $order->get_items();

				$products = '';

				foreach ( $line_items as $item_id => $item ) {
					$name     = $item['name'];
					$quantity = $item['qty'];
					$products .= $name . ' (Qty: ' . $quantity . ')';
					$products .= ' | ';
				}

				$products = rtrim( $products, ' | ' );

				$custom_fields[] = array(
					'display_name'  => 'Products',
					'variable_name' => 'products',
					'value'         => $products,
				);

			}

			if ( $this->meta_billing_address ) {

				$billing_address = $order->get_formatted_billing_address();
				$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

				$paystack_params['meta_billing_address'] = $billing_address;

				$custom_fields[] = array(
					'display_name'  => 'Billing Address',
					'variable_name' => 'billing_address',
					'value'         => $billing_address,
				);

			}

			if ( $this->meta_shipping_address ) {

				$shipping_address = $order->get_formatted_shipping_address();
				$shipping_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $shipping_address ) );

				if ( empty( $shipping_address ) ) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html( preg_replace( '#<br\s*/?>#i', ', ', $billing_address ) );

					$shipping_address = $billing_address;

				}
				$custom_fields[] = array(
					'display_name'  => 'Shipping Address',
					'variable_name' => 'shipping_address',
					'value'         => $shipping_address,
				);

			}

		}

		return $custom_fields;
	}

	/**
	 * Process a refund request from the Order details screen.
	 *
	 * @param int    $order_id WC Order ID.
	 * @param null   $amount   WC Order Amount.
	 * @param string $reason   Refund Reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		if ( ! ( $this->public_key && $this->secret_key ) ) {
			return false;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return false;
		}

		if ( $this->is_wc_lt( '3.0' ) ) {
			$order_currency = get_post_meta( $order_id, '_order_currency', true );
			$transaction_id = get_post_meta( $order_id, '_transaction_id', true );
		} else {
			$order_currency = $order->get_currency();
			$transaction_id = $order->get_transaction_id();
		}

		$verify_url = 'https://api.paystack.co/transaction/verify/' . $transaction_id;

		$headers = array(
			'Authorization' => 'Bearer ' . $this->secret_key,
		);

		$args = array(
			'headers' => $headers,
			'timeout' => 60,
		);

		$request = wp_remote_get( $verify_url, $args );

		if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {

			$paystack_response = json_decode( wp_remote_retrieve_body( $request ) );

			if ( 'success' == $paystack_response->data->status ) {

				$merchant_note = sprintf( __( 'Refund for Order ID: #%1$s on %2$s', 'woo-paystack' ), $order_id, get_site_url() );

				$body = array(
					'transaction'   => $transaction_id,
					'amount'        => $amount * 100,
					'currency'      => $order_currency,
					'customer_note' => $reason,
					'merchant_note' => $merchant_note,
				);

				$args['body'] = $body;
				$refund_url   = 'https://api.paystack.co/refund';

				$refund_request = wp_remote_post( $refund_url, $args );

				if ( ! is_wp_error( $refund_request ) && 200 === wp_remote_retrieve_response_code( $refund_request ) ) {

					$refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );

					if ( $refund_response->status ) {
						$amount         = wc_price( $amount, array( 'currency' => $order_currency ) );
						$refund_id      = $refund_response->data->id;
						$refund_message = sprintf( __( 'Refunded %1$s. Refund ID: %2$s. Reason: %3$s', 'woo-paystack' ), $amount, $refund_id, $reason );
						$order->add_order_note( $refund_message );

						return true;
					}

				} else {

					$refund_response = json_decode( wp_remote_retrieve_body( $refund_request ) );

					if ( isset( $refund_response->message ) ) {
						return new WP_Error( 'error', $refund_response->message );
					} else {
						return new WP_Error( 'error', __( 'Can&#39;t process refund at the moment. Try again later.', 'woo-paystack' ) );
					}
				}

			}

		}

	}

	/**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt( $version ) {
		return version_compare( WC_VERSION, $version, '<' );
	}

	/**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled( $order ) {
		$autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$paystack_settings = get_option('woocommerce_' . $payment_method . '_settings');

		if ( isset( $paystack_settings['autocomplete_order'] ) && 'yes' === $paystack_settings['autocomplete_order'] ) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;
	}

	/**
	 * Retrieve the payment channels configured for the gateway
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_gateway_payment_channels( $order ) {

		$payment_method = $order->get_payment_method();

		if ( 'paystack' === $payment_method ) {
			return array();
		}

		$payment_channels = $this->payment_channels;

		if ( empty( $payment_channels ) ) {
			$payment_channels = array( 'card' );
		}

		return $payment_channels;
	}

}
