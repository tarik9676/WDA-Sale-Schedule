<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wdass_admin_page' ) ) {
	function wdass_admin_page () {
		wdass_admin_settings_template (
			get_admin_page_title(),
			'wdass_general_settings',
			'wdass-sale-schedule'
		);
	}
}


if ( ! function_exists( 'wdass_section_callback_general_settings' ) ) {
	function wdass_section_callback_general_settings () {}
}


if ( ! function_exists( 'wdass_general_settings_field_callback' ) ) {
	function wdass_general_settings_field_callback ( $args ) {
		$wdass_fields = new WDASS_Settings_Field( $args );
		$wdass_fields->render_form();
	}
}