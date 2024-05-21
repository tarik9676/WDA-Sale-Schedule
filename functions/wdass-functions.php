<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wdass_is_woocommerce_activated' ) ) {
	function wdass_is_woocommerce_activated() {
        return class_exists( 'WooCommerce' ) ? true : false;
	}
}