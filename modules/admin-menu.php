<?php

defined( 'ABSPATH' ) || exit;


/*-------------------------------------------
*  Creating Custom WordPress Admin Menu
*-------------------------------------------*/
add_action('admin_menu', 'wdass_admin_menu');

function wdass_admin_menu() {
	// Parent Menu : WDA Sale Schedule
    add_menu_page(
        'Sale Schedule Settings',	// Page title
        'Sale Schedule',			// Menu Title
        'manage_options',           // Capability
        'wdass-sale-schedule',      // Menu Slug
        'wdass_admin_page',			// Callback function to render the page
        'dashicons-image-filter',	// Dashicon
        30                          // Position
    );

	/*----- Schedule Events -----*/
    add_submenu_page(
        "wdass-sale-schedule",
        "Schedule Events",
        "Events",
        "manage_options",
        "action-schedules",
        "wdass_scheduled_events",
    );
}


/*-------------------------------------------
*  Assigning Sections & Fields
*-------------------------------------------*/
add_action( 'admin_init', 'wdass_settings_init' );

function wdass_settings_init() {

	/*----- PAGE : Schedule Settings -----*/
	
	/* Registering Settings
	*
	* Registers the settings for add_setting_field()
	* 'wdass_general_settings' setting group name same as defined in add_setting_field() and setting_field()
	* 'wdass_schedule_global_status' & 'wdass_schedule_timestamp' are the id attributes of input field of table with name 'wdass_schedule_global_status' & 'wdass_schedule_timestamp'
	*/
	// register_setting('wdass_general_settings', 'wdass_schedule_global_status', [
	// 	'type'				=> 'boolean',
	// 	'sanitize_callback'	=> 'sanitize_text_field',
	// 	'default'			=> NULL
	// ]);
	register_setting('wdass_general_settings', 'wdass_schedule_timezone', [
		'type'				=> 'string',
		'sanitize_callback'	=> 'sanitize_text_field',
		'default'			=> NULL
	]);
	
	/* Section : General Settings
	*
	* Adds a new input section to the setting page.
	* 'wdass_general_settings' is the value of the hidden input field with name option_page
	* 'General Settings' is heading that will be displayed on setting page.
	* 'wdass_section_callback_general_settings' is a callback function that prints the description of setting page.
	* 'wdass-sale-schedule' is the slug name of the page whose settings sections you want to output.
	* 
	* Array Parameter------
	* 'before_section' : HTML content to prepend to the section’s HTML output. (Receives the section’s class name as %s.)
	* 'after_section' : HTML content to append to the section’s HTML output.
	* 'section_class' : The class name to use for the section.
	*/
	add_settings_section(
		'wdass_general_settings',
		__( 'General Settings', 'wdass' ),
        'wdass_section_callback_general_settings',
        'wdass-sale-schedule',
		[
			'before_section' => '',
			'after_section' => '',
			'section_class' => '',
		]
	);
	
	
	/* Field : Schedule Status
	*
	* Adds a settings field to a settings page and section by creating a table with heading ( h2 )
	* 'wdass_schedule_status' is the id attribute of the input field of table with name 'wdass_schedule_status'
	* 'Schedule Status' is the title of the input field of table with name 'wdass_schedule_status'
	* 'wdass_field_callback' is a callback function that prints the input field with name 'wdass_schedule_status'
	* 'wdass-sale-schedule' is the slug name of the page whose settings sections you want to output.
	* 'wdass_general_settings' is the settings group name, which should match the group name used in settings_field()
	* Array Parameter : All these arguments are totally custom data + optional.
	*/
	// add_settings_field(
	// 	'wdass_schedule_global_status',
	//     __( 'Activate Schedule', 'wdass' ),
	// 	'wdass_general_settings_field_callback',
	// 	'wdass-sale-schedule',
	// 	'wdass_general_settings',
	// 	array(
	// 		'type'			=> 'checkbox',
	// 		'option_group'	=> 'wdass_general_settings',
	// 		'option_name'	=> 'wdass_schedule_global_status',
	// 		'label_for'		=> 'wdass_schedule_global_status',
	// 		'class'			=> 'wdass_schedule_global_status',
	// 		'custom_arg'	=> 'custom_data',
    //         'description'	=> __( 'Switch schedule status.', 'wdass' ),
	// 	)
	// );
	
	add_settings_field(
		'wdass_schedule_timezone',
	    __( 'Choose Timezone', 'wdass' ),
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
            'description'	=> __( 'Select your timezone.', 'wdass' ),
		)
	);
	/*----- ENDS PAGE : Schedule Settings -----*/
}