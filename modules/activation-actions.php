<?php

defined( 'ABSPATH' ) || exit;


if ( ! function_exists( 'wdass_activation_actions' ) ) {
    function wdass_activation_actions() {
        global $wpdb;
        $DB_VERSION = '1.0.0';
    
        $table_events     = $wpdb->prefix . 'wdass_events';
        $table_eventsmeta = $wpdb->prefix . 'wdass_eventmeta';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql_events = "CREATE TABLE IF NOT EXISTS $table_events (
            id INT(11) NOT NULL AUTO_INCREMENT,
            object_id INT(11) NOT NULL,
            schedule_status VARCHAR(20) NOT NULL,
            schedule_date VARCHAR(20) NOT NULL,
            schedule_time VARCHAR(20) NOT NULL,
            restore_status VARCHAR(20) NOT NULL,
            restore_date VARCHAR(20) NOT NULL,
            restore_time VARCHAR(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
    
        $sql_eventsmeta = "CREATE TABLE IF NOT EXISTS $table_eventsmeta (
            event_id INT(11) NOT NULL,
            post_id INT(11) NOT NULL,
            type VARCHAR(10) NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            content LONGTEXT
        ) $charset_collate;";
    
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql_events );
        dbDelta( $sql_eventsmeta );
        
        add_option( 'wdass_db_version', $DB_VERSION );
        add_option( 'wdass_plugin_version', 'free' );
        add_option( 'wdass_schedule_timezone', "GMT+0" );
    }
}