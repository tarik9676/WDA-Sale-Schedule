<?php


defined( 'ABSPATH' ) || exit;


/*-------------------------------------------
*  Backend Assets
*-------------------------------------------*/
if ( ! function_exists('wdass_backend_assets') ) {
    add_action( 'admin_enqueue_scripts', 'wdass_backend_assets', 10 );

    function wdass_backend_assets () {
        // Meta Box CSS
        wp_enqueue_style( 'wdass-meta-box', WDASS_ROOT_URL . 'assets/css/meta-box.css', [], '1.0.0' );

        // Admin JS
        wp_enqueue_script( 'wdass-meta-box', WDASS_ROOT_URL . 'assets/js/meta-box.js', ["jquery"], "1.0.0", true );
    }
}