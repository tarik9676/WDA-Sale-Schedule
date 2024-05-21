<?php

/*
* Plugin Name: WDA Sale Schedule
* Description: Make your sale dynamic by WDA Sale Schedule. Setup scheduled tasks like sale price, title, descriptions, category/tag changing etc. at a certiain date & time or for a specific time range.
* Version: 1.0.0
* Requires at least: 5.8
* Requires PHP: 5.6.20
* Author: Web Dev Advisor
* Author URI: https://webdevadvisor.com/
* Text Domain: wdass
*/


/*-------------------------------------------
*  Exit if accessed directly
*-------------------------------------------*/
defined( 'ABSPATH' ) || exit;


/*-------------------------------------------
*  Plugin Version
*-------------------------------------------*/
define( 'WDASS_VERSION', '1.0.0' );


/*-------------------------------------------
*  Minimum Requred Wordpress version
*-------------------------------------------*/
define( 'WDASS_MINIMUM_WP_VERSION', '5.8' );


/*-------------------------------------------
*  Plugin Root Path
*-------------------------------------------*/
define( 'WDASS_ROOT_DIR', plugin_dir_path( __FILE__ ) );


/*-------------------------------------------
*  Plugin Root URL
*-------------------------------------------*/
define( 'WDASS_ROOT_URL', plugin_dir_url( __FILE__ )) ;


/*-------------------------------------------
*  Funtions
*-------------------------------------------*/
require_once( WDASS_ROOT_DIR . 'functions/wdass-functions.php' );
require_once( WDASS_ROOT_DIR . 'functions/admin-settings-template.php' );
require_once( WDASS_ROOT_DIR . 'functions/script-enqueue.php' );


/*-------------------------------------------
*  Classes
*-------------------------------------------*/
require_once( WDASS_ROOT_DIR . 'classes/class-html-generator.php' );
require_once( WDASS_ROOT_DIR . 'classes/class-settings-fields.php' );


/*-------------------------------------------
*  Modules
*-------------------------------------------*/
require_once( WDASS_ROOT_DIR . 'modules/admin-menu.php' );
require_once( WDASS_ROOT_DIR . 'modules/activation-actions.php' );
require_once( WDASS_ROOT_DIR . 'modules/schedule-settings.php' );
require_once( WDASS_ROOT_DIR . 'modules/action-schedules.php' );

// if ( wdass_is_woocommerce_activated() ) {
// 	require_once( WDASS_ROOT_DIR . 'modules/meta-boxes.php' );
// 	require_once( WDASS_ROOT_DIR . 'modules/events-execution.php' );
// }

require_once( WDASS_ROOT_DIR . 'modules/meta-boxes.php' );
require_once( WDASS_ROOT_DIR . 'modules/events-execution.php' );


/*-------------------------------------------
*  Plugin Activation Actions
*-------------------------------------------*/
register_activation_hook( WDASS_ROOT_DIR . 'wda-sale-schedule.php', 'wdass_activation_actions' );