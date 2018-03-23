<?php
/*
	Plugin Name:            Paystack WooCommerce Payment Gateway
	Plugin URI:             https://paystack.com
	Description:            WooCommerce payment gateway for Paystack
	Version:                5.1.0
	Author:                 Tunbosun Ayinla
	Author URI:             https://bosun.me
	License:                GPL-2.0+
	License URI:            http://www.gnu.org/licenses/gpl-2.0.txt
	WC requires at least:   3.0.0
	WC tested up to:        3.3.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_PAYSTACK_MAIN_FILE', __FILE__ );
define( 'WC_PAYSTACK_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_PAYSTACK_VERSION', '5.1.0' );

function tbz_wc_paystack_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {

		require_once dirname( __FILE__ ) . '/includes/class-paystack.php';

		require_once dirname( __FILE__ ) . '/includes/class-paystack-custom-gateway.php';

		require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-gateway-one.php';
		require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-gateway-two.php';
		require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-gateway-three.php';
		require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-gateway-four.php';
		require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-gateway-five.php';

	} else{

		require_once dirname( __FILE__ ) . '/includes/class-paystack-deprecated.php';

	}

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {

		require_once dirname( __FILE__ ) . '/includes/class-wc-subscriptions.php';

	}

	require_once dirname( __FILE__ ) . '/includes/polyfill.php';

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_paystack_gateway', 99 );

}
add_action( 'plugins_loaded', 'tbz_wc_paystack_init', 99 );


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

	if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {

		if ( 'GHS' != get_woocommerce_currency() ) {

			$settings 		 = get_option( 'woocommerce_paystack_settings', '' );
			$custom_gateways = isset( $settings['custom_gateways'] ) ? $settings['custom_gateways'] : '';

			switch ( $custom_gateways ) {
				case '5':
					$methods[] = 'Tbz_WC_Paystack_Gateway_One';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Two';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Three';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Four';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Five';
				break;
					case '4':
					$methods[] = 'Tbz_WC_Paystack_Gateway_One';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Two';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Three';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Four';
				break;
					case '3':
					$methods[] = 'Tbz_WC_Paystack_Gateway_One';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Two';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Three';
				break;
					case '2':
					$methods[] = 'Tbz_WC_Paystack_Gateway_One';
					$methods[] = 'Tbz_WC_Paystack_Gateway_Two';
					break;
				case '1':
					$methods[] = 'Tbz_WC_Paystack_Gateway_One';
					break;

				default:
					break;
			}

		}

	}

	return $methods;

}


/**
* Display the test mode notice
**/
function tbz_wc_paystack_testmode_notice(){

	$paystack_settings = get_option( 'woocommerce_paystack_settings' );

	$test_mode 	= isset( $paystack_settings['testmode'] ) ? $paystack_settings['testmode'] : '';

	if ( 'yes' == $test_mode ) {
    ?>
	    <div class="update-nag">
	        Paystack testmode is still enabled, Click <a href="<?php echo get_bloginfo('wpurl') ?>/wp-admin/admin.php?page=wc-settings&tab=checkout&section=paystack">here</a> to disable it when you want to start accepting live payment on your site.
	    </div>
    <?php
	}
}
add_action( 'admin_notices', 'tbz_wc_paystack_testmode_notice' );