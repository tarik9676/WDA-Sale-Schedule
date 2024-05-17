<?php

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

	// Schedule Settings
    // add_submenu_page(
    //     "wdass-sale-schedule",      // Parent Slug
    //     "Schedule Settings",        // Page Title
    //     "Settings",					// Menu Title
    //     "manage_options",           // Capability
    //     "wdass-sale-schedule",  // Menu Slug
    //     "wdass_schedule_settings",	// Callback Function
    // );

	/*----- Schedule Events -----*/
    add_submenu_page(
        "wdass-sale-schedule",
        "Schedule Events",
        "Events",
        "manage_options",
        "action-schedules",
        "wdass_scheduled_events",
    );

	/*----- Plugin License -----*/
    add_submenu_page(
        "wdass-sale-schedule",
        "Plugin License",
        "License",
        "manage_options",
        "wdass-plugin-license",
        "wdass_plugin_license",
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



	/*----- PAGE : License -----*/
	
	/* Section :  */
	add_settings_section(
		'wdass_plugin_license',
		__( 'License Activation', 'wdass' ),
        'wdass_plugin_license_section_callback',
        'wdass-plugin-license',
		[
			'before_section' => '',
			'after_section' => '',
			'section_class' => '',
		]
	);
	
	/* Registering License Settings */
	register_setting( 'wdass_plugin_license', 'wdass_license_key', [
		'type'				=> 'string',
		'sanitize_callback'	=> 'sanitize_text_field',
		'default'			=> NULL
	]);
	
	/* Field : License Key */

	$wdass_license_label = 'License Key';
	$wdass_custom_args = 'enabled';
	$wdass_custom_description = 'Enter your premium license key here.';

	$wdass_license_status = get_option( 'wdass_license_status' );
	if ( wdass_execute_key( $wdass_license_status ) ) {
		$wdass_license_label = 'License Activated <span class="dashicons dashicons-yes-alt"></span>';
		$wdass_custom_args = 'disabled';
		$wdass_custom_description = '<a id="wdass__revoke-license-key" href="javascript:void(0)">Change</a> your license key.';
	}

	add_settings_field(
		'wdass_license_key',
	    __( $wdass_license_label, 'wdass' ),
		'wdass_plugin_license_field_callback',
		'wdass-plugin-license',
		'wdass_plugin_license',
		array(
			'type'			=> 'text',
			'option_group'	=> 'wdass_plugin_license',
			'option_name'	=> 'wdass_license_key',
			'label_for'		=> 'wdass_license_key',
			'class'			=> 'wdass_license_key',
			'custom_arg'	=> $wdass_custom_args,
            'description'	=> __( $wdass_custom_description, 'wdass' ),
		)
	);

	/*----- ENDS PAGE : License -----*/
}