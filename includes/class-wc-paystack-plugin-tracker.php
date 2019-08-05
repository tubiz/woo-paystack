<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Paystack_Plugin_Tracker {

    /**
     * Plugin slug
     *
     * @var string
     */
    public $plugin_slug;

    /**
     * Paystack Public Key
     *
     * @var string
     */
    public $public_key;

    /**
     * WC_Paystack_Plugin_Tracker constructor.
     *
     * @param string $plugin_slug Plugin slug.
     * @param string $public_key  Paystack Public Key
     */
    public function __construct( $plugin_slug, $public_key ) {
        $this->plugin_slug = $plugin_slug;
        $this->public_key  = $public_key;
    }

    /**
     * Send Paystack transaction reference to Paystack logger along with plugin slug and public key.
     *
     * @param $trx_ref string Paystack transaction reference.
     */
    public function log_transaction( $trx_ref ) {

        $url = "https://plugin-tracker.paystackintegrations.com/log/charge_success";

	    $body = array(
		    'public_key'            => $this->public_key,
		    'plugin_name'           => $this->plugin_slug,
		    'transaction_reference' => $trx_ref,
	    );

	    $args = array(
		    'body' => $body,
	    );

        wp_remote_post( $url, $args );

    }

}