<?php


// add_filter( 'manage_edit-shop_order_columns', 'wdass_order_columns');

// function wdass_order_columns ( $columns ) {
//     $columns['billing_country'] = 'Country Name';

//     return $columns;
// }

// add_action( 'manage_shop_order_posts_custom_column', 'wdass_new_order_column');

// function wdass_new_order_column ( $column ) {
//     global $post;

//     if ( 'billing_country' === $column ) {
//         $order = wc_get_order( $post->ID );
//         echo $order->get_billing_country();
//     }
// }

add_filter( 'manage_edit-shop_order_columns', 'bbloomer_add_new_order_admin_list_column', 100, 1 );
 
function bbloomer_add_new_order_admin_list_column( $columns ) {
    $columns['custom_column'] = 'Custom Column';
    return $columns;
}
 
add_action( 'manage_shop_order_posts_custom_column', 'bbloomer_add_new_order_admin_list_column_content', 100, 1 );
 
function bbloomer_add_new_order_admin_list_column_content( $column ) {
   
    // global $post;
 
    if ( 'custom_column' === $column ) {
 
        // $order = wc_get_order( $post->ID );
        // echo $order->get_billing_country();
		echo 'Custom value';
      
    }
}