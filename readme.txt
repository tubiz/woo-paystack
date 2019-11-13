=== Paystack WooCommerce Payment Gateway ===
Contributors: tubiz
Donate link: https://bosun.me/donate
Tags: paystack, woocommerce, payment gateway, tubiz plugins, verve, ghana, nigeria, mastercard, visa
Requires at least: 4.7
Tested up to: 5.3
Stable tag: 5.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Paystack WooCommerce Payment Gateway allows you to accept online payments from local and international customers



== Description ==

This is a Paystack payment gateway for WooCommerce.

Paystack is on a mission to deliver a safe and convenient payment experience for customers and merchants. Paystack provide Nigerian & Ghanaian merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve, Bank Accounts, GTB 737 & Visa QR.

To signup for a Paystack Merchant account visit their website by clicking [here](https://paystack.com)

Paystack WooCommerce Payment Gateway allows you to accept payment on your WooCommerce store using Mastercard, Visa, Verve, bank accounts, GTB 737 & Visa QR.

With this Paystack WooCommerce Payment Gateway plugin, you will be able to accept the following payment methods in your shop:

* __Mastercard__
* __Visa__
* __Verve__
* __Bank Account__
* __GTB 737__
* __Visa QR__

= Note =

This plugin is meant to be used by merchants in Nigeria and Ghana.

= Plugin Features =

*   __Accept payment__ via Mastercard, Visa, Verve, Bank Accounts, GTB 737 & Visa QR
*   __Seamless integration__ into the WooCommerce checkout page. Accept payment directly on your site
*   __Refunds__ from the WooCommerce order details page. Refund an order directly from the order details page
*   __Recurring payment__ using [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) plugin

= WooCommerce Subscriptions Integration =

*	The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) integration only works with __WooCommerce v2.6 and above__ and __WooCommerce Subscriptions v2.0 and above__.

*	No subscription plans is created on Paystack. The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) plugin handles all the subscription functionality.

*	If a customer pays for a subscription using a Mastercard or Visa card, their subscription will renew automatically throughout the duration of the subscription. If an automatic renewal fail their subscription will be put on-hold and they will have to login to their account to renew the subscription.

*	For customers paying with a Verve card, their subscription can't be renewed automatically, once a payment is due their subscription will be on-hold. The customer will have to login to his account to manually renew his subscription.

*	If a subscription has a free trial and no signup-fee, automatic renewal is not possible for the first payment because the initial order total will be 0, after the free trial the subscription will be put on-hold. The customer will have to login to his account to renew his subscription. If a Mastercard or Visa card is used to renew the subscription subsequent renewals will be automatic throughout the duration of the subscription, if a Verve card is used automatic renewal isn't possible.

= Suggestions / Feature Request =

If you have suggestions or a new feature request, feel free to get in touch with me via the contact form on my website [here](http://bosun.me/get-in-touch/)

You can also follow me on Twitter! **[@tubiz](https://twitter.com/tubiz)**


== Installation ==

= Automatic Installation =
* 	Login to your WordPress Admin area
* 	Go to "Plugins > Add New" from the left hand menu
* 	In the search box type __Paystack WooCommerce Payment Gateway__
*	From the search result you will see __Paystack WooCommerce Payment Gateway__ click on __Install Now__ to install the plugin
*	A popup window will ask you to confirm your wish to install the Plugin.
*	After installation, activate the plugin.
* 	Open the settings page for WooCommerce and click the "Checkout" tab.
* 	Click on the __Paystack__ link from the available Checkout Options
*	Configure your __Paystack Payment Gateway__ settings. See below for details.

= Manual Installation =
1. 	Download the plugin zip file
2. 	Login to your WordPress Admin. Click on "Plugins > Add New" from the left hand menu.
3.  Click on the "Upload" option, then click "Choose File" to select the zip file from your computer. Once selected, press "OK" and press the "Install Now" button.
4.  Activate the plugin.
5. 	Open the settings page for WooCommerce and click the "Checkout" tab.
6. 	Click on the __Paystack__ link from the available Checkout Options
7.	Configure your __Paystack Payment Gateway__ settings. See below for details.



= Configure the plugin =
To configure the plugin, go to __WooCommerce > Settings__ from the left hand menu, then click __Checkout__ from the top tab. You will see __Paystack__ as part of the available Checkout Options. Click on it to configure the payment gateway.

* __Enable/Disable__ - check the box to enable Paystack Payment Gateway.
* __Title__ - allows you to determine what your customers will see this payment option as on the checkout page.
* __Description__ - controls the message that appears under the payment fields on the checkout page. Here you can list the types of cards you accept.
* __Test Mode__ - Check to enable test mode. Test mode enables you to test payments before going live. If you ready to start receving real payment on your site, kindly uncheck this.
* __Test Secret Key__ - Enter your Test Secret Key here. Get your API keys from your Paystack account under Settings > Developer/API
* __Test Public Key__ - Enter your Test Public Key here. Get your API keys from your Paystack account under Settings > Developer/API
* __Live Secret Key__ - Enter your Live Secret Key here. Get your API keys from your Paystack account under Settings > Developer/API
* __Live Public Key__ - Enter your Live Public Key here. Get your API keys from your Paystack account under Settings > Developer/API
* Click on __Save Changes__ for the changes you made to be effected.





== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1.	You need to have WooCommerce plugin installed and activated on your WordPress site.
2.	You need to open a Paystack merchant account on [Paystack](https://paystack.com)

= WooCommerce Subscriptions Integration =

*	The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) integration only works with WooCommerce v2.6 and above and WooCommerce Subscriptions v2.0 and above.

*	No subscription plans is created on Paystack. The [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) handles all the subscription functionality.

*	If a customer pays for a subscription using a MasterCard or Visa card, their subscription will renew automatically throughout the duration of the subscription. If an automatic renewal fail their subscription will be put on-hold and they will have to login to their account to renew the subscription.

*	For customers paying with a Verve card, their subscription can't be renewed automatically, once a payment is due their subscription will be on-hold. The customer will have to login to his account to manually renew his subscription.

*	If a subscription has a free trial and no signup-fee, automatic renewal is not possible because the order total will be 0, after the free trial the subscription will be put on-hold. The customer will have to login to his account to renew his subscription. If a MasterCard or Visa card is used to renew subsequent renewals will be automatic throughout the duration of the subscription, if a Verve card is used automatic renewal isn't possible.


== Changelog ==

= 5.6.1 - November 13, 2019 =
*   Update: WooCommerce 3.8 compatibility.

= 5.6.0 - August 7, 2019 =
*   New: Support for refunds via Paystack from the order details screen.
*   New: Log successful transaction to Paystack metrics tracker.
*   New: Add support for sending additional order details to Paystack when making payment using a saved card.
*   New: Add support for sending additional order details to Paystack when a subscription payment is renewed.
*   Update: WC 3.7 compatibility.

= 5.5.0 - May 27, 2019 =
*   Misc: Renamed Diamond Bank to Access Bank (Diamond)
*   Tweak: Significant cleanup of code formatting and adherence of WordPress coding standards
*   New: Support for translation

= 5.4.2 - February 13, 2019 =
*   Misc: Remove Paystack fee and Paystack payout amount on the order details page

= 5.4.1 - February 1, 2019 =
*   Fix: Split payment not working properly when the split payment transaction charge setting field is empty

= 5.4.0 - December 9, 2018 =
*   New: Add support for Paystack split payments
*   New: Display Paystack fee and Paystack payout amount on the order details page
*   Misc: Add support for WooCommerce 3.5
*   Misc: Renamed Skye Bank Plc to Polaris Bank Limited
*   Misc: Add new banks (ALAT by WEMA, ASO Savings and Loans, MainStreet Bank & Ekondo Microfinance Bank) to Allowed Banks Card list
*   Misc: Add new banks logos (ALAT by WEMA, ASO Savings and Loans, MainStreet Bank & Ekondo Microfinance Bank) to Payment Icons list

= 5.3.1 - July 26, 2018 =
*	Fix: The bank payment channel not showing in the default gateway

= 5.3.0 - June 2, 2018 =
*	Fix: Saved cards feature not working in the custom gateways
*	Fix: Custom gateways not processing automatic renewal payments via WooCommerce Subscriptions plugin

= 5.2.1 - June 1, 2018 =
*	Misc: Add support for WooCommerce 3.4

= 5.2.0 - May 18, 2018 =
*	New: Add support for multiple subscriptions purchase using WooCommerce Subscriptions plugin
*	Fix: Deprecated functions in the Tbz_WC_Gateway_Paystack_Subscription class

= 5.1.0 - March 27, 2018 =
*	New: Add support for GHS (Ghanaian cedi) currency
* 	Fix: Deprecated WooCommerce 2.X functions

= 5.0.2 - September 15, 2017 =
*	Fix: Illegal string offset warnings when plugin is newly installed

= 5.0.1 - September 14, 2017 =
*	Fix: Fatal error on the checkout page if WooCommerce 2.6.14 and below is installed

= 5.0.0 - August 29, 2017 =
*	New: Add support for Paystack custom filters
*	New: Create additional Paystack gateways (max of 5) using different custom filters. You can create a gateway that accepts only Verve cards, a gateway that accepts only bank account payments, a gateway that accepts only GTB issued Mastercard.

= 4.1.0 - July 7, 2017 =
*	Fix: Deprecated WooCommerce 2.X functions

= 4.0.1 - April 10, 2017 =
* 	Fix: Fatal error if WooCommerce 2.6.14 and below is installed

= 4.0.0 - April 10, 2017 =
* 	New: Add support for Paystack Inline Embed.
*  	New: Add support for sending additional order details to Paystack

= 3.1.1 - February 13, 2017 =
* 	New: Changed Paystack payment methods icon.

= 3.1.0 - January 10, 2017 =
* 	New: Add support for USD and GBP currency. Note this has to be enabled by Paystack for your account before it can be used on your site.

= 3.0.0 - November 11, 2016 =
* 	New: Add support for recurring payment using [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/) plugin.

= 2.1.0 - October 15, 2016 =
*	New: Add support for confirming payment using the webhook url

= 2.0.1 - July 5, 2016 =
*	Fix: Paystack payment option and settings not available if Paystack WooCommerce Payment Gateway version 2.0.0 is installed and WooCommerce version 2.5.5 and below is installed

= 2.0.0 - June 28, 2016 =
* 	New: Saved cards - allow store customers to save their card details and pay again using the same card. Card details are saved on Paystack servers and not on your store.
*	Fix: Change payment icon

= 1.1.0 - April 22, 2016 =
*   Fix: Fatal error if the WooCommerce plugin is deactivated while the Paystack plugin is active

= 1.0.0 - February 3, 2016 =
*   First release



== Upgrade Notice ==

= 5.6.1
*   WooCommerce 3.8 compatibility.

== Screenshots ==

1. Paystack WooCommerce Payment Gateway Setting Page

2. Paystack WooCommerce Payment Gateway on the checkout page

3. Paystack inline payment page

4. Paystack inline embed payment page

5. Manage saved cards from your account page