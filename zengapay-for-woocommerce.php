<?php
/**
 * 
 * Plugin Name: ZENGAPAY for Woocommerce
 * Plugin URI: https://zengapay.com
 * Author: zengapay
 * Author URI: https://zengapay.com
 * Description: ZENGAPAY for Woocommerce facilitates payment collection on your Woocommerce store via Mobile Money (Airtel, MTN) with your ZENGAPAY account.
 * Version: 0.1.0
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text-Domain: zengapay-pay-woo
 * 
 * WC requires at least: 3.0
 * WC tested up to: 4.3.0
 */ 

// Basic Security to avoid brute access to file.
defined( 'ABSPATH' ) or exit;

// Check if WooCommerce is installed.
// if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

// Define constants to be used.
if( ! defined( 'BASENAME' ) ) {
	define( 'BASENAME', plugin_basename( __FILE__ ) );
}

if( ! defined( 'DIR_PATH' ) ) {
	define( 'DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// When plugin is loaded. Call init functions.
add_action( 'plugins_loaded', 'zengapay_payment_init' );
add_filter( 'woocommerce_payment_gateways', 'zengapay_payment_gateway_add_to_woo');

/**
 * Add the gateway class.
 * Add function helpers.
 * 
 * @return void
 */
function zengapay_payment_init() {
	require_once DIR_PATH . 'includes/zengapay-initial-setup.php';
	require_once DIR_PATH . 'includes/class-zengapay-gateway.php';
	require_once DIR_PATH . 'includes/zengapay-order-statuses.php';
	require_once DIR_PATH . 'includes/zengapay-checkout-page.php';
	require_once DIR_PATH . 'includes/zengapay-payments-cpt.php';
	require_once DIR_PATH . 'includes/zengapay-rest-api-endpoint.php';
}

/**
 * Add Payment gateway to Woocommerce.
 *
 * @param array $gateways Existing Gateways in WC.
 * @return array $gateways Existing Gateways in WC + Zengapay.
 */
function zengapay_payment_gateway_add_to_woo( $gateways ) {
    $gateways[] = 'WC_Zengapay_Gateway';
    return $gateways;
}
