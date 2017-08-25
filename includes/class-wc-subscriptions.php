<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tbz_WC_Gateway_Paystack_Subscription extends Tbz_WC_Paystack_Gateway {

	/**
	 * Constructor
	*/
	public function __construct() {

		parent::__construct();

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {

			add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 10, 2 );

		}
	}


	/**
	 * Check if an order contains a subscription
	 */
	public function order_contains_subscription( $order_id ) {

		return function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) );

	}


	/**
	 * Process a trial subscription order with 0 total
	 */
	public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Check for trial subscription order with 0 total
		if ( $this->order_contains_subscription( $order ) && $order->get_total() == 0 ) {

			$order->payment_complete();

			$order->add_order_note( 'This subscription has a free trial reason for the 0 amount' );

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order )
			);

		} else {

			return parent::process_payment( $order_id );

		}

	}


	/**
	 * Process a subscription renewal
	 */
	public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {

		$response = $this->process_subscription_payment( $renewal_order, $amount_to_charge );

		if ( is_wp_error( $response ) ) {

			$renewal_order->update_status( 'failed', sprintf( 'Paystack Transaction Failed (%s)', $response->get_error_message() ) );

		}

	}


	/**
	 * Process a subscription renewal payment
	 */
	public function process_subscription_payment( $order = '', $amount = 0 ) {

		$auth_code =  get_post_meta( $order->id, '_paystack_token', true );

		if ( $auth_code ) {

			$email 			= $order->billing_email;
			$order_amount 	= $amount * 100;

			$paystack_url 	= 'https://api.paystack.co/transaction/charge_authorization';

			$headers = array(
				'Content-Type'	=> 'application/json',
				'Authorization' => 'Bearer ' . $this->secret_key
			);

			$body = array(
				'email'						=> $email,
				'amount'					=> $order_amount,
				'authorization_code'		=> $auth_code
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

	        		$paystack_ref 	= $paystack_response->data->reference;

					$order->payment_complete( $paystack_ref );

					$message = sprintf( 'Payment via Paystack successful (Transaction Reference: %s)', $paystack_ref );

					$order->add_order_note( $message );

					return true;

				} else {

					return new WP_Error( 'paystack_error', 'Paystack payment failed.' );

				}

	        }
		}

		return new WP_Error( 'paystack_error', 'This subscription can\'t be renewed automatically. The customer will have to login to his account to renew his subscription' );

	}

}