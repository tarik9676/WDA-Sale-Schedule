<?php
function wdass_plugin_license () {
	wdass_admin_settings_template(
		get_admin_page_title(),
		'wdass_plugin_license',
		'wdass-plugin-license',
		[
			'label' => 'Save Settings',
			'id'	=>'wdass-plugin-license'
		]
	);
}

function wdass_plugin_license_section_callback () {}


function wdass_plugin_license_field_callback ( $args ) {
	$wdass_fields = new WDASS_Settings_Field( $args );
	echo $wdass_fields->render_form();
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" && isset( $_POST['wdass-plugin-license'] ) ) {
	$wdass_license_key = sanitize_text_field( $_POST['wdass_license_key'] );

	if ( !empty( $wdass_license_key ) ) {
		$wdass_endpoint_url = add_query_arg (
			[
				'request' => 'activation',
				'license_key' => $wdass_license_key,
				'website' => site_url(),
				'email' => get_bloginfo( 'admin_email' ),
			],
			'https://webdevadvisor.com/wp-json/wdasecurity/v1/license'
		);

		$response = wdass_api_call( $wdass_endpoint_url );

		if ( strlen( $response[0] ) == 10 ) {
			update_option( 'wdass_license_status', $response[0] );
		} else {
			exit;
		}
	}
}