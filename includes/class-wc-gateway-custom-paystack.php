<?php

/**
 * Class Tbz_WC_Paystack_Custom_Gateway.
 */
class WC_Gateway_Custom_Paystack extends WC_Gateway_Paystack_Subscriptions {

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
 
		$this->form_fields = array(
			'enabled'                          => array(
				'title'       => __( 'Enable/Disable', 'woo-paystack' ),
				/* translators: payment method title */
				'label'       => sprintf( __( 'Enable Paystack - %s', 'woo-paystack' ), $this->title ),
				'type'        => 'checkbox',
				'description' => __( 'Enable this gateway as a payment option on the checkout page.', 'woo-paystack' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __( 'Title', 'woo-paystack' ),
				'type'        => 'text',
				'description' => __( 'This controls the payment method title which the user sees during checkout.', 'woo-paystack' ),
				'desc_tip'    => true,
				'default'     => __( 'Paystack', 'woo-paystack' ),
			),
			'description'                      => array(
				'title'       => __( 'Description', 'woo-paystack' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the payment method description which the user sees during checkout.', 'woo-paystack' ),
				'desc_tip'    => true,
				'default'     => '',
			),
			'payment_page'                     => array(
				'title'       => __( 'Payment Page', 'woo-paystack' ),
				'type'        => 'select',
				'description' => __( 'Inline shows the payment popup on the page while Inline Embed shows the payment page directly on the page', 'woo-paystack' ),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''       => __( 'Select One', 'woo-paystack' ),
					'inline' => __( 'Inline', 'woo-paystack' ),
					'embed'  => __( 'Inline Embed', 'woo-paystack' ),
				),
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
				'class'       => __( 'woocommerce_paystack_subaccount_code', 'woo-paystack' ),
				'default'     => '',
			),
			'split_payment_transaction_charge' => array(
				'title'             => __( 'Split Payment Transaction Charge', 'woo-paystack' ),
				'type'              => 'number',
				'description'       => __( 'A flat fee to charge the subaccount for this transaction, in Naira (&#8358;). This overrides the split percentage set when the subaccount was created. Ideally, you will need to use this if you are splitting in flat rates (since subaccount creation only allows for percentage split). e.g. 100 for a &#8358;100 flat fee.', 'woo-paystack' ),
				'class'             => 'woocommerce_paystack_split_payment_transaction_charge',
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
			'payment_channels'                 => array(
				'title'             => __( 'Payment Channels', 'woo-paystack' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-paystack-payment-channels',
				'description'       => __( 'The payment channels enabled for this gateway', 'woo-paystack' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->channels(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment channels', 'woo-paystack' ),
				),
			),
			'cards_allowed'                    => array(
				'title'             => __( 'Allowed Card Brands', 'woo-paystack' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-paystack-cards-allowed',
				'description'       => __( 'The card brands allowed for this gateway. This filter only works with the card payment channel.', 'woo-paystack' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->card_types(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select card brands', 'woo-paystack' ),
				),
			),
			'banks_allowed'                    => array(
				'title'             => __( 'Allowed Banks Card', 'woo-paystack' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-paystack-banks-allowed',
				'description'       => __( 'The banks whose card should be allowed for this gateway. This filter only works with the card payment channel.', 'woo-paystack' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->banks(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select banks', 'woo-paystack' ),
				),
			),
			'payment_icons'                    => array(
				'title'             => __( 'Payment Icons', 'woo-paystack' ),
				'type'              => 'multiselect',
				'class'             => 'wc-enhanced-select wc-paystack-payment-icons',
				'description'       => __( 'The payment icons to be displayed on the checkout page.', 'woo-paystack' ),
				'default'           => '',
				'desc_tip'          => true,
				'select_buttons'    => true,
				'options'           => $this->payment_icons(),
				'custom_attributes' => array(
					'data-placeholder' => __( 'Select payment icons', 'woo-paystack' ),
				),
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

	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options() {

		$paystack_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' );
		$checkout_settings_url = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
		?>

		<h2>
			<?php
			/* translators: payment method title */
			printf( __( 'Paystack - %s', 'woo-paystack' ), esc_attr( $this->title ) );
			?>
			<?php
			if ( function_exists( 'wc_back_link' ) ) {
				wc_back_link( __( 'Return to payments', 'woo-paystack' ), $checkout_settings_url );
			}
			?>
		</h2>

		<h4>
			<?php
			/* translators: link to Paystack developers settings page */
			printf( __( 'Important: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%s" target="_blank" rel="noopener noreferrer">here</a> to the URL below', 'woo-paystack' ), 'https://dashboard.paystack.co/#/settings/developer' );
			?>
		</h4>

		<p style="color: red">
			<code><?php echo esc_url( WC()->api_request_url( 'Tbz_WC_Paystack_Webhook' ) ); ?></code>
		</p>

		<p>
			<?php
			/* translators: link to Paystack general settings page */
			printf( __( 'To configure your Paystack API keys and enable/disable test mode, do that <a href="%s">here</a>', 'woo-paystack' ), esc_url( $paystack_settings_url ) );
			?>
		</p>

		<?php

		if ( $this->is_valid_for_use() ) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';

		} else {

			/* translators: disabled message */
			echo '<div class="inline error"><p><strong>' . sprintf( __( 'Paystack Payment Gateway Disabled: %s', 'woo-paystack' ), esc_attr( $this->msg ) ) . '</strong></p></div>';

		}

	}

	/**
	 * Payment Channels.
	 */
	public function channels() {

		return array(
			'card' => __( 'Cards', 'woo-paystack' ),
			'bank' => __( 'Banks', 'woo-paystack' ),
		);

	}

	/**
	 * Card Types.
	 */
	public function card_types() {

		return array(
			'visa'       => __( 'Visa', 'woo-paystack' ),
			'verve'      => __( 'Verve', 'woo-paystack' ),
			'mastercard' => __( 'Mastercard', 'woo-paystack' ),
		);

	}

	/**
	 * Banks.
	 */
	public function banks() {

		return array(
			'044'  => __( 'Access Bank', 'woo-paystack' ),
			'035A' => __( 'ALAT by WEMA', 'woo-paystack' ),
			'401'  => __( 'ASO Savings and Loans', 'woo-paystack' ),
			'023'  => __( 'Citibank Nigeria', 'woo-paystack' ),
			'063'  => __( 'Access Bank (Diamond)', 'woo-paystack' ),
			'050'  => __( 'Ecobank Nigeria', 'woo-paystack' ),
			'562'  => __( 'Ekondo Microfinance Bank', 'woo-paystack' ),
			'084'  => __( 'Enterprise Bank', 'woo-paystack' ),
			'070'  => __( 'Fidelity Bank', 'woo-paystack' ),
			'011'  => __( 'First Bank of Nigeria', 'woo-paystack' ),
			'214'  => __( 'First City Monument Bank', 'woo-paystack' ),
			'058'  => __( 'Guaranty Trust Bank', 'woo-paystack' ),
			'030'  => __( 'Heritage Bank', 'woo-paystack' ),
			'301'  => __( 'Jaiz Bank', 'woo-paystack' ),
			'082'  => __( 'Keystone Bank', 'woo-paystack' ),
			'014'  => __( 'MainStreet Bank', 'woo-paystack' ),
			'526'  => __( 'Parallex Bank', 'woo-paystack' ),
			'076'  => __( 'Polaris Bank Limited', 'woo-paystack' ),
			'101'  => __( 'Providus Bank', 'woo-paystack' ),
			'221'  => __( 'Stanbic IBTC Bank', 'woo-paystack' ),
			'068'  => __( 'Standard Chartered Bank', 'woo-paystack' ),
			'232'  => __( 'Sterling Bank', 'woo-paystack' ),
			'100'  => __( 'Suntrust Bank', 'woo-paystack' ),
			'032'  => __( 'Union Bank of Nigeria', 'woo-paystack' ),
			'033'  => __( 'United Bank For Africa', 'woo-paystack' ),
			'215'  => __( 'Unity Bank', 'woo-paystack' ),
			'035'  => __( 'Wema Bank', 'woo-paystack' ),
			'057'  => __( 'Zenith Bank', 'woo-paystack' ),
		);

	}

	/**
	 * Payment Icons.
	 */
	public function payment_icons() {

		return array(
			'verve'         => __( 'Verve', 'woo-paystack' ),
			'visa'          => __( 'Visa', 'woo-paystack' ),
			'mastercard'    => __( 'Mastercard', 'woo-paystack' ),
			'paystackwhite' => __( 'Secured by Paystack White', 'woo-paystack' ),
			'paystackblue'  => __( 'Secured by Paystack Blue', 'woo-paystack' ),
			'paystack-wc'   => __( 'Paystack', 'woo-paystack' ),
			'access'        => __( 'Access Bank', 'woo-paystack' ),
			'alat'          => __( 'ALAT by WEMA', 'woo-paystack' ),
			'aso'           => __( 'ASO Savings and Loans', 'woo-paystack' ),
			'citibank'      => __( 'Citibank Nigeria', 'woo-paystack' ),
			'diamond'       => __( 'Access Bank (Diamond)', 'woo-paystack' ),
			'ecobank'       => __( 'Ecobank Nigeria', 'woo-paystack' ),
			'ekondo'        => __( 'Ekondo Microfinance Bank', 'woo-paystack' ),
			'enterprise'    => __( 'Enterprise Bank', 'woo-paystack' ),
			'fidelity'      => __( 'Fidelity Bank', 'woo-paystack' ),
			'firstbank'     => __( 'First Bank of Nigeria', 'woo-paystack' ),
			'fcmb'          => __( 'First City Monument Bank', 'woo-paystack' ),
			'gtbank'        => __( 'Guaranty Trust Bank', 'woo-paystack' ),
			'heritage'      => __( 'Heritage Bank', 'woo-paystack' ),
			'jaiz'          => __( 'Jaiz Bank', 'woo-paystack' ),
			'keystone'      => __( 'Keystone Bank', 'woo-paystack' ),
			'mainstreet'    => __( 'MainStreet Bank', 'woo-paystack' ),
			'parallex'      => __( 'Parallex Bank', 'woo-paystack' ),
			'polaris'       => __( 'Polaris Bank Limited', 'woo-paystack' ),
			'providus'      => __( 'Providus Bank', 'woo-paystack' ),
			'stanbic'       => __( 'Stanbic IBTC Bank', 'woo-paystack' ),
			'standard'      => __( 'Standard Chartered Bank', 'woo-paystack' ),
			'sterling'      => __( 'Sterling Bank', 'woo-paystack' ),
			'suntrust'      => __( 'Suntrust Bank', 'woo-paystack' ),
			'union'         => __( 'Union Bank of Nigeria', 'woo-paystack' ),
			'uba'           => __( 'United Bank For Africa', 'woo-paystack' ),
			'unity'         => __( 'Unity Bank', 'woo-paystack' ),
			'wema'          => __( 'Wema Bank', 'woo-paystack' ),
			'zenith'        => __( 'Zenith Bank', 'woo-paystack' ),
		);

	}

}
