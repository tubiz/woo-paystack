<?php
/*
	Plugin Name:	Paystack WooCommerce Payment Gateway
	Plugin URI: 	https://paystack.com
	Description: 	WooCommerce payment gateway for Paystack
	Version: 		4.1.0
	Author: 		Tunbosun Ayinla
	Author URI: 	https://bosun.me
	License:        GPL-2.0+
	License URI:    http://www.gnu.org/licenses/gpl-2.0.txt
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_PAYSTACK_MAIN_FILE', __FILE__ );

define( 'WC_PAYSTACK_VERSION', '4.1.0' );

function tbz_wc_paystack_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {
		require_once dirname( __FILE__ ) . '/includes/class-paystack.php';
	} else{
		require_once dirname( __FILE__ ) . '/includes/class-paystack-deprecated.php';
	}

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {require_once dirname( __FILE__ ) . '/includes/class-wc-subscriptions.php';
	}

	require_once dirname( __FILE__ ) . '/includes/polyfill.php';

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_paystack_gateway' );

}
add_action( 'plugins_loaded', 'tbz_wc_paystack_init', 0 );


/**
* Add Settings link to the plugin entry in the plugins menu
**/
function tbz_woo_paystack_plugin_action_links( $links ) {

    $settings_link = array(
    	'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' ) . '" title="View Paystack WooCommerce Settings">Settings</a>'
    );

    return array_merge( $links, $settings_link );

}
add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_paystack_plugin_action_links' );


/**
* Add Paystack Gateway to WC
**/
function tbz_wc_add_paystack_gateway( $methods ) {

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {
		$methods[] = 'Tbz_WC_Gateway_Paystack_Subscription';
	} else {
		$methods[] = 'Tbz_WC_Paystack_Gateway';
	}

	return $methods;

}


/**
* Display the testmode notice
**/
function tbz_wc_paystack_testmode_notice(){

	$paystack_settings = get_option( 'woocommerce_paystack_settings' );

	$enabled 	= $paystack_settings['testmode'];
	$test_mode 	= $paystack_settings['enabled'];

	if ( ( 'yes' == $test_mode ) && ( 'yes' == $enabled ) ) {
    ?>
	    <div class="update-nag">
	        Paystack testmode is still enabled, Click <a href="<?php echo get_bloginfo('wpurl') ?>/wp-admin/admin.php?page=wc-settings&tab=checkout&section=paystack">here</a> to disable it when you want to start accepting live payment on your site.
	    </div>
    <?php
	}
}
add_action( 'admin_notices', 'tbz_wc_paystack_testmode_notice' );