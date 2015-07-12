<?php
/*
Plugin Name: WooCommerce Paystack Gateway
Plugin URI: http://paystack.co
Description: A payment gateway for paystack.co
Version: 1.0.0
Author: Tunbosun Ayinla
Author URI: http://bosun.me
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paystack main class
 */
class WC_Paystack {

	/**
	 * Constructor
	 */
	public function __construct() {
		define( 'WC_PAYSTACK_VERSION', '1.0.0' );
		define( 'WC_PAYSTACK_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'WC_PAYSTACK_MAIN_FILE', __FILE__ );

		// Actions
		add_action( 'plugins_loaded', array( $this, 'init' ), 0 );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
	}


	/**
	 * Init files
	 */
	public function init() {

		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}
		// Includes
		include_once( 'includes/class-wc-gateway-paystack.php' );

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			//include_once( 'includes/class-wc-gateway-paystack-subscription.php' );
		}
	}

	/**
	 * Register the gateway for use
	 */
	public function register_gateway( $methods ) {

		$methods[] = 'WC_Gateway_Paystack';

		if ( class_exists( 'WC_Subscriptions_Order' ) ) {
			//$methods[] = 'WC_Gateway_Paystack_Subscription';
		} else {
			//$methods[] = 'WC_Gateway_Paystack';
		}

		return $methods;
	}
}

new WC_Paystack();
