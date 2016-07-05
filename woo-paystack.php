<?php
/*
	Plugin Name:	Paystack WooCommerce Payment Gateway
	Plugin URI: 	https://paystack.com
	Description: 	WooCommerce payment gateway for Paystack
	Version: 		2.0.1
	Author: 		Tunbosun Ayinla
	Author URI: 	http://bosun.me
	License:        GPL-2.0+
	License URI:    http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


define( 'WC_PAYSTACK_MAIN_FILE', __FILE__ );

add_action( 'plugins_loaded', 'tbz_wc_paystack_init', 0 );

function tbz_wc_paystack_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-paystack.php';
	}
	else{
		require_once dirname( __FILE__ ) . '/includes/class-paystack-deprecated.php';
	}

	/**
	* Add Settings link to the plugin entry in the plugins menu
	**/
	function tbz_woo_paystack_plugin_action_links( $links ) {
	    $settings_link = array(
	    	'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=tbz_wc_paystack_gateway' ) . '" title="View Paystack WooCommerce Settings">Settings</a>'
	    );
	    return array_merge( $links, $settings_link );
	}
	add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_paystack_plugin_action_links' );


	/**
 	* Add Paystack Gateway to WC
 	**/
	function tbz_wc_add_paystack_gateway( $methods ) {
		$methods[] = 'Tbz_WC_Paystack_Gateway';
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_paystack_gateway' );

}