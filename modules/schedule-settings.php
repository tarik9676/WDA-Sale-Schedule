<?php

defined( 'ABSPATH' ) || exit;


function wdass_admin_page () {
	wdass_admin_settings_template (
		get_admin_page_title(),
		'wdass_general_settings',
		'wdass-sale-schedule'
	);
}

function wdass_section_callback_general_settings () {
    echo "";
    // print_r( get_option( 'wdass_general_settings' ) );
}

function wdass_general_settings_field_callback ( $args ) {
	$wdass_fields = new WDASS_Settings_Field( $args );
	echo $wdass_fields->render_form();
}