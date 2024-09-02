<?php

/*
* Plugin Name:          WDA Sale Schedule
* Description:          Make your sale dynamic by WDA Sale Schedule. Setup scheduled tasks like sale price, title, descriptions, category/tag changing etc. at a certiain date & time or for a specific time range.
* Plugin URI:           https://webdevadvisor.com/product/wda-sale-schedule/
* Version:              1.2.0
* Requires at least:    6.2
* Requires PHP:         5.6.20
* Author:               Web Dev Advisor
* Author URI:           https://webdevadvisor.com/
* Text Domain:          wda-sale-schedule
* Requires Plugins:     woocommerce
*/


/*-------------------------------------------
*  Exit if accessed directly
*-------------------------------------------*/
defined( 'ABSPATH' ) || exit;


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
require_once( WDASS_ROOT_DIR . 'modules/meta-boxes.php' );
require_once( WDASS_ROOT_DIR . 'modules/events-execution.php' );


/*-------------------------------------------
*  Plugin Activation Actions
*-------------------------------------------*/
register_activation_hook( WDASS_ROOT_DIR . 'wda-sale-schedule.php', 'wdass_activation_actions' );