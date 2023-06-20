<?php
/**
 * Plugin Name: Paystack WooCommerce Payment Gateway
 * Plugin URI: https://paystack.com
 * Description: WooCommerce payment gateway for Paystack
 * Version: 5.7.6
 * Author: Tunbosun Ayinla
 * Author URI: https://bosun.me
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 7.0
 * WC tested up to: 7.8
 * Text Domain: woo-paystack
 * Domain Path: /languages
 */

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_PAYSTACK_MAIN_FILE', __FILE__ );
define( 'WC_PAYSTACK_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

define( 'WC_PAYSTACK_VERSION', '5.7.6' );

/**
 * Initialize Paystack WooCommerce payment gateway.
 */
function tbz_wc_paystack_init() {

	load_plugin_textdomain( 'woo-paystack', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		add_action( 'admin_notices', 'tbz_wc_paystack_wc_missing_notice' );
		return;
	}

	add_action( 'admin_init', 'tbz_wc_paystack_testmode_notice' );

	require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-paystack.php';

	require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-paystack-subscriptions.php';

	require_once dirname( __FILE__ ) . '/includes/class-wc-gateway-custom-paystack.php';

	require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-wc-gateway-paystack-one.php';
	require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-wc-gateway-paystack-two.php';
	require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-wc-gateway-paystack-three.php';
	require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-wc-gateway-paystack-four.php';
	require_once dirname( __FILE__ ) . '/includes/custom-gateways/class-wc-gateway-paystack-five.php';

	add_filter( 'woocommerce_payment_gateways', 'tbz_wc_add_paystack_gateway', 99 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'tbz_woo_paystack_plugin_action_links' );

}
add_action( 'plugins_loaded', 'tbz_wc_paystack_init', 99 );

/**
 * Add Settings link to the plugin entry in the plugins menu.
 *
 * @param array $links Plugin action links.
 *
 * @return array
 **/
function tbz_woo_paystack_plugin_action_links( $links ) {

	$settings_link = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' ) . '" title="' . __( 'View Paystack WooCommerce Settings', 'woo-paystack' ) . '">' . __( 'Settings', 'woo-paystack' ) . '</a>',
	);

	return array_merge( $settings_link, $links );

}

/**
 * Add Paystack Gateway to WooCommerce.
 *
 * @param array $methods WooCommerce payment gateways methods.
 *
 * @return array
 */
function tbz_wc_add_paystack_gateway( $methods ) {

	if ( class_exists( 'WC_Subscriptions_Order' ) && class_exists( 'WC_Payment_Gateway_CC' ) ) {
		$methods[] = 'WC_Gateway_Paystack_Subscriptions';
	} else {
		$methods[] = 'WC_Gateway_Paystack';
	}

	if ( 'NGN' === get_woocommerce_currency() ) {

		$settings        = get_option( 'woocommerce_paystack_settings', '' );
		$custom_gateways = isset( $settings['custom_gateways'] ) ? $settings['custom_gateways'] : '';

		switch ( $custom_gateways ) {
			case '5':
				$methods[] = 'WC_Gateway_Paystack_One';
				$methods[] = 'WC_Gateway_Paystack_Two';
				$methods[] = 'WC_Gateway_Paystack_Three';
				$methods[] = 'WC_Gateway_Paystack_Four';
				$methods[] = 'WC_Gateway_Paystack_Five';
				break;

			case '4':
				$methods[] = 'WC_Gateway_Paystack_One';
				$methods[] = 'WC_Gateway_Paystack_Two';
				$methods[] = 'WC_Gateway_Paystack_Three';
				$methods[] = 'WC_Gateway_Paystack_Four';
				break;

			case '3':
				$methods[] = 'WC_Gateway_Paystack_One';
				$methods[] = 'WC_Gateway_Paystack_Two';
				$methods[] = 'WC_Gateway_Paystack_Three';
				break;

			case '2':
				$methods[] = 'WC_Gateway_Paystack_One';
				$methods[] = 'WC_Gateway_Paystack_Two';
				break;

			case '1':
				$methods[] = 'WC_Gateway_Paystack_One';
				break;

			default:
				break;
		}
	}

	return $methods;

}

/**
 * Display a notice if WooCommerce is not installed
 */
function tbz_wc_paystack_wc_missing_notice() {
	echo '<div class="error"><p><strong>' . sprintf( __( 'Paystack requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'woo-paystack' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

/**
 * Display the test mode notice.
 **/
function tbz_wc_paystack_testmode_notice() {

	if ( ! class_exists( Notes::class ) ) {
		return;
	}

	if ( ! class_exists( WC_Data_Store::class ) ) {
		return;
	}

	if ( ! method_exists( Notes::class, 'get_note_by_name' ) ) {
		return;
	}

	$test_mode_note = Notes::get_note_by_name( 'paystack-test-mode' );

	if ( false !== $test_mode_note ) {
		return;
	}

	$paystack_settings = get_option( 'woocommerce_paystack_settings' );
	$test_mode         = $paystack_settings['testmode'] ?? '';

	if ( 'yes' !== $test_mode ) {
		Notes::delete_notes_with_name( 'paystack-test-mode' );

		return;
	}

	$note = new Note();
	$note->set_title( __( 'Paystack test mode enabled', 'woo-paystack' ) );
	$note->set_content( __( 'Paystack test mode is currently enabled. Remember to disable it when you want to start accepting live payment on your site.', 'woo-paystack' ) );
	$note->set_type( Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
	$note->set_layout( 'plain' );
	$note->set_is_snoozable( false );
	$note->set_name( 'paystack-test-mode' );
	$note->set_source( 'woo-paystack' );
	$note->add_action( 'disable-paystack-test-mode', __( 'Disable Paystack test mode', 'woo-paystack' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paystack' ) );
	$note->save();
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
