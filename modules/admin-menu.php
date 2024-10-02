<?php

defined( 'ABSPATH' ) || exit;


/*-------------------------------------------
*  Custom Admin Menu
*-------------------------------------------*/
add_action('admin_menu', 'wdass_admin_menu');

function wdass_admin_menu() {
	// Parent Menu : WDA Sale Schedule
    add_menu_page(
        'Sale Schedule Settings',
        'Sale Schedule',
        'manage_options',
        'wdass-sale-schedule',
        "wdass_scheduled_events",
        WDASS_ROOT_URL . 'assets/images/logo-icon.svg',
        30
    );
}


/*-------------------------------------------
*  Assigning Sections & Fields
*-------------------------------------------*/
add_action( 'admin_init', 'wdass_settings_init' );

function wdass_settings_init() {

	register_setting('wdass_general_settings', 'wdass_schedule_timezone', [
		'type'				=> 'string',
		'sanitize_callback'	=> 'sanitize_text_field',
		'default'			=> NULL
	]);

	add_settings_section(
		'wdass_general_settings',
		__( 'General Settings', WDASS_TEXTDOMAIN ),
        'wdass_section_callback_general_settings',
        'wdass-sale-schedule',
		[
			'before_section' => '',
			'after_section' => '',
			'section_class' => '',
		]
	);

	add_settings_field(
		'wdass_schedule_timezone',
	    __( 'Choose Timezone', WDASS_TEXTDOMAIN ),
		'wdass_general_settings_field_callback',
		'wdass-sale-schedule',
		'wdass_general_settings',
		array(
			'type'			=> 'select',
			'option_group'	=> 'wdass_general_settings',
			'option_name'	=> 'wdass_schedule_timezone',
			'label_for'		=> 'wdass_schedule_timezone',
			'class'			=> 'wdass_schedule_timezone',
			'custom_arg'	=> 'custom_data',
            'description'	=> __( 'Select your timezone.', WDASS_TEXTDOMAIN ),
		)
	);
	
}