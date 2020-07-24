<?php
/**
 * Scheduled Events for the plugin.
 *
 * create your function, that runs on cron
 */
function cross_check_orders_for_billing( ){

    // Get the orders pending
    $pending_payments = get_order_pending_payments();

    if( empty( $pending_payments ) || NULL == $pending_payments ) {
        return;
    }

    if( ! empty( $pending_payments ) || NULL != $pending_payments ) {
        //Loop through each order post object
        foreach( $pending_payments as $pending_payment ){
            zengapay_get_invoiced_orders_status( $pending_payment );
        }
    }

}

/**
 * Query for the orders with wc-invoiced status
 * return array $pending_payments
 */
function get_order_pending_payments() {
    global $wpdb;

    $date        = date('now');
    $today       = date( 'Y-m-d', strtotime( $date ) );
    $yesterday   = date('Y-m-d',strtotime("-1 days"));
    $date_string = "BETWEEN '$today' AND '$yesterday'";

    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE 'shop_order' AND post_status = 'wc-invoiced' ORDER BY post_date DESC");

    return $results;
}

/**
 * Loop through each order from 
 * Query for the orders with wc-invoiced status
 * return array $pending_payment
 */
function zengapay_get_invoiced_orders_status( $order_object ){

    $timestamp = get_post_meta( $order_object->ID, 'api_order_id_meta_value', true );
    
    if( $timestamp == 0 || $timestamp == NULL || empty( $timestamp ) ) return;

    $url = 'https://backend.services.zengapay.co/api/v2/mobilemoney/' . $timestamp . '/status';
    
    $arguments = array(
        'method' => 'GET',
        'timeout' => 10
    );

    // It wasn't there, so regenerate the data and save the transient
    $response = wp_remote_get( esc_url_raw( $url ), $arguments );

    $response_body = wp_remote_retrieve_body( $response );
    $status_reponse = json_decode( wp_remote_retrieve_body( $response ) );
    $status_code = wp_remote_retrieve_response_code( $response );

    if ( empty( $status_reponse ) || $status_code !== 200 ) {
        return new \WP_Error(
            'invalid_data',
            'Invalid data returned',
            [
                'code' => $status_code,
                'body' => empty( $response_json ) ? $response_body : $response_json,
            ]
        );
    }

    if ( 200 == $status_code && ! empty ( $status_reponse ) ) {

        foreach( $status_reponse as $status => $keys ) {
  
            if( is_array( $keys ) ) {
                
                foreach( $keys as $key ){

                    // echo '<pre>';
                    // var_dump( $key );
                    // echo '</pre>';
            
                    // Find the order with the same transaction_id then change order status to processing
                    $transaction_id = isset( $key->transaction_id );
                    
                    if ( $timestamp == $transaction_id ) {
                        
                        $order = new WC_Order( $order_object->ID );
    
                        if( 'zengapay_payment' === $order->get_payment_method() && 'Failed' == $key->payment_status || 'failed' == $key->payment_status ) {
                            $order->update_status( 'failed' );
                        }

                        if( 'zengapay_payment' === $order->get_payment_method() && 'Successful' == $key->payment_status || 'successful' == $key->payment_status ) {
                            $order->update_status( 'processing' );
                        }

                        $pay = 'Request: ' . $key->request_status . ', Pay: ' . $key->payment_status;
                        update_post_meta( $order_object->ID, 'api_response_id_meta_value', $pay );

                    }

                }

            }

        }

    }

}