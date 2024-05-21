<?php


defined( 'ABSPATH' ) || exit;


/*-------------------------------------------
*  Frontend Assets
*-------------------------------------------*/
if ( ! function_exists('wdass_frontend_assets') ) {
    add_action( 'wp_enqueue_scripts', 'wdass_frontend_assets', 10 );

    function wdass_frontend_assets () {
        // // Fontawesome Files
        // wp_enqueue_style( 'wdass_fontawesome_icon', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css' );

        // Bootstrap Files
        // wp_enqueue_style( 'wdass_bootstrap_style', WDASS_ROOT_URL . 'assets/libs/bootstrap.min.css' );
        // wp_enqueue_script( 'wdass_bootstrap_script', WDASS_ROOT_URL . 'assets/libs/bootstrap.bundle.min.js', null, "1.0.0", false );

        // // Custom StyleSheet
        // wp_enqueue_style( 'wdass_style', WDASS_ROOT_URL . 'assets/css/style.css' );

        // // Custom JS SCript
        // wp_enqueue_script( 'wdass_script', WDASS_ROOT_URL . 'assets/js/script.js', ["jquery"], "1.0.0", true );

        // // DISPATCHER LOCALIZE SCRIPT
        // wp_localize_script('wdass_script', 'wdassData', array(
        //     "ajax_url" => admin_url( 'admin-ajax.php' ),
        //     "nonce" => wp_create_nonce( 'wdass_ajax_nonce' ),
        // ));

        // // HEARTBEAT JS
        // wp_enqueue_script('heartbeat');
    }
}


/*-------------------------------------------
*  Backend Assets
*-------------------------------------------*/
if ( ! function_exists('wdass_backend_assets') ) {
    add_action( 'admin_enqueue_scripts', 'wdass_backend_assets', 10 );

    function wdass_backend_assets () {
        // Meta Box CSS
        wp_enqueue_style( 'wdass-meta-box', WDASS_ROOT_URL . 'assets/css/meta-box.css' );

        // Admin JS
        wp_enqueue_script( 'wdass-meta-box', WDASS_ROOT_URL . 'assets/js/meta-box.js', ["jquery"], "1.0.0", true );
    }
}