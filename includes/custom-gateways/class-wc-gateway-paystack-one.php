<?php

class WC_Gateway_Paystack_One extends WC_Gateway_Custom_Paystack {

	/**
	 * Payment channels.
	 *
	 * @var array
	 */
	public $payment_channels;

	/**
	 * Allowed bank cards.
	 *
	 * @var array
	 */
	public $banks;

	/**
	 * Allowed card brands.
	 *
	 * @var array
	 */
	public $cards;

	/**
	 * Payment icons.
	 *
	 * @var array
	 */
	public $payment_icons;

	/**
	 * Paystack settings.
	 *
	 * @var array
	 */
	public $paystack_settings;

	/**
	 * WC_Gateway_Paystack_One constructor.
	 */
	public function __construct() {

		$this->id = 'paystack-one';

		$gateway_title = $this->get_option( 'title' );

		if ( empty( $gateway_title ) ) {
			$gateway_title = __( 'One', 'woo-paystack' );
		}
		/* translators: %s: gateway title */
		$this->method_title       = sprintf( __( 'Paystack - %s', 'woo-paystack' ), $gateway_title );
		/* translators: %s: gateway description */
		$this->method_description = sprintf( __( 'Paystack provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a Paystack account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'woo-paystack' ), 'https://paystack.com', 'https://dashboard.paystack.com/#/settings/developer' );

		$this->has_fields = true;

		$this->supports = array(
			'products',
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

		$this->paystack_settings = get_option( 'woocommerce_paystack_settings', '' );

		// Get setting values.
		$this->title       = $gateway_title;
		$this->description = $this->get_option( 'description' );
		$this->enabled     = $this->get_option( 'enabled' );


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

			$email = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;

			$amount = $order->get_total() * 100;

			$txnref = $order_id . '_' . time();

			$the_order_id  = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$the_order_key = method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;

			if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

				$paystack_params['email']    = $email;
				$paystack_params['amount']   = $amount;
				$paystack_params['txnref']   = $txnref;
				$paystack_params['pay_page'] = $this->payment_page;
				$paystack_params['currency'] = get_woocommerce_currency();

			}

			if ( $this->split_payment ) {

				$paystack_params['subaccount_code']     = $this->subaccount_code;
				$paystack_params['charges_account']     = $this->charges_account;
				$paystack_params['transaction_charges'] = $this->transaction_charges * 100;

			}

			if ( in_array( 'bank', $this->payment_channels ) ) {
				$paystack_params['bank_channel'] = 'true';
			}

			if ( in_array( 'card', $this->payment_channels ) ) {
				$paystack_params['card_channel'] = 'true';
			}

			if ( $this->banks ) {

				$paystack_params['banks_allowed'] = $this->banks;

			}

			if ( $this->cards ) {

				$paystack_params['cards_allowed'] = $this->cards;

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
	 * Add Gateway to checkout page.
	 *
	 * @param $available_gateways
	 *
	 * @return mixed
	 */
	public function add_gateway_to_checkout( $available_gateways ) {

		if ( 'no' === $this->enabled ) {
			unset( $available_gateways[ $this->id ] );
		}

		return $available_gateways;

	}

}
