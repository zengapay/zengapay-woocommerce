<?php
/**
 * Defaults for the Zengapay checkout page.
 */

add_filter( 'woocommerce_gateway_description', 'zengapay_billing_phone_fields', 20, 2 );
add_action( 'woocommerce_checkout_process', 'zengapay_billing_phone_fields_validation', 20, 1 );
add_action( 'woocommerce_checkout_update_order_meta', 'zengapay_custom_checkout_field_update_order_meta' );
add_action( 'woocommerce_admin_order_data_after_billing_address','zengapay_custom_checkout_field_display_admin_order_meta', 10, 1 );

/**
 * Check if the phone number for billing is filled.
 *
 * @param object $order Order Object.
 * @return void
 */
function zengapay_billing_phone_fields_validation( $order ) {

    $zengapay_payment_phone_number = $_POST['zengapay_payment_phone_number'];

    // Error the Phone number
    if( 'zengapay_payment' === $_POST['payment_method'] && ! isset( $zengapay_payment_phone_number ) || empty( $zengapay_payment_phone_number ) ) {
        wc_add_notice( 'Please enter the Phone Number for Billing (Format: 256772123456 )', 'error' );
        return;
    }

    if( '256' !== substr( $zengapay_payment_phone_number, 3 ) && 12 !== strlen( $zengapay_payment_phone_number ) && ! is_numeric( $zengapay_payment_phone_number ) ) {
        wc_add_notice( 'Please enter the Phone Number with correct format e.g 256772123456 )', 'error' );
    }

}

/**
 * Set up billing number for the payment gateway.
 *
 * @param array $description Fields added in the gateway platform.
 * @param int $payment_id    Order Payment ID.
 * @return void
 */
function zengapay_billing_phone_fields( $description, $payment_id ) {

    if ( 'zengapay_payment' !== $payment_id ) {
        return $description;
    }

    ob_start();
    
    // Billing number Field.
    woocommerce_form_field(
        'zengapay_payment_phone_number',
        array(
            'type' => 'number',
            'label' =>__( 'Enter Phone Number for Billing (Format: 256772123456 )', 'zengapay-pay-woo' ),
            'class' => array( 'form-row', 'form-row-wide', 'card-number' ),
            'required' => true,
        )
    );

    $description .= ob_get_clean();
    
    return $description;
}
				
add_action( 'woocommerce_checkout_update_order_meta', 'bbloomer_save_new_checkout_field' );
add_action( 'woocommerce_admin_order_data_after_billing_address', 'bbloomer_show_new_checkout_field_order', 10, 1 );
  
function bbloomer_save_new_checkout_field( $order_id ) {
    
    $order       = new WC_Order( $order_id );
    $order_total = intval( $order->get_total() );
    
    if ( $_POST['zengapay_payment_phone_number'] ) {
        update_post_meta( $order_id, 'zengapay_payment_phone_number', esc_attr( $_POST['zengapay_payment_phone_number'] ) );
    }
    if ( $_POST['zengapay_payment_phone_number'] ) {
        update_post_meta( $order_id, 'zengapay_external_reference', esc_attr( $_POST['zengapay_payment_phone_number'] . $order_id . $order_total ) );
    }
}
  
   
function bbloomer_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, 'zengapay_payment_phone_number', true ) ) {
       echo '<p><strong>Zengapay payment number:</strong> ' . get_post_meta( $order_id, 'zengapay_payment_phone_number', true ) . '</p>';
       echo '<p><strong>Zengapay External Reference:</strong> ' . get_post_meta( $order_id, 'zengapay_external_reference', true ) . '</p>';
   }
}