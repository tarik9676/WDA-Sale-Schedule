<?php


defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WDASS__Run_Events' ) ) {
    class WDASS__Run_Events {

        public function __construct() {
            add_action( 'init', array( $this, 'check_run_events' ) );
        }
    
    
        /*-------------------------------------------
        *  Find & execute pending events
        *-------------------------------------------*/
        public function check_run_events () {
            global $wpdb;
            
            $table_events = $wpdb->prefix . 'wdass_events';

            date_default_timezone_set( get_option( 'wdass_timezone', "GMT+0" ) );


            /*----------------------------------------------------
            *  Getting all pending schedule events from DB
            *----------------------------------------------------*/
            $pending_events_sql = $wpdb->get_results( "SELECT * FROM $table_events WHERE schedule_status = 'pending';" );

            if ( count( $pending_events_sql ) ) {
                foreach ( $pending_events_sql as $event ) {
                    $schedule_time_string = $event->schedule_date . ' ' . $event->schedule_time;

                    if ( strtotime( current_time( 'mysql' ) ) > strtotime( $schedule_time_string ) ) {
                        $this->execute( $wpdb, $event->object_id, $event->id, 'modified', 'schedule_status', 'completed' );
                    }

                } // Events Table Loop ENDS
            }
        }

        private function execute ( $wpdb, $post_id, $event_id, $data_type, $status_key, $status_value ) {

            $table_events   = $wpdb->prefix . 'wdass_events';
            $table_eventmeta= $wpdb->prefix . 'wdass_eventmeta';
            $table_posts    = $wpdb->prefix . 'posts';
            $table_postmeta = $wpdb->prefix . 'postmeta';

            $event_meta_sql = $wpdb->get_results(
                "SELECT * FROM $table_eventmeta
                WHERE event_id = '$event_id' AND type = '$data_type' AND NOT content = '404';"
            );

            foreach ( $event_meta_sql as $meta ) {
                $first_character = substr($meta->meta_key, 0, 1);
                
                if ( $meta->content !== '404' ) {
                    switch ( $first_character ) {
                        case '_':
                            $wpdb->update(
                                $table_postmeta,
                                [ 'meta_value' => $meta->content ],
                                [
                                    'post_id'   => $meta->post_id,
                                    'meta_key'  => $meta->meta_key
                                ],
                                [ '%s' ],
                                [ '%d', '%s' ]
                            );
                            break;
    
                        case 't':
                            $terms = json_decode( $meta->content, true );
                            
                            wp_set_object_terms( $meta->post_id, $terms[ 'product_cat' ], 'product_cat' );
                            wp_set_object_terms( $meta->post_id, $terms[ 'product_tag' ], 'product_tag' );
                            break;
                        
                        default:
                            $wpdb->update(
                                $table_posts,
                                [ $meta->meta_key => $meta->content ],
                                [ 'ID'  => $meta->post_id ],
                                [ '%s' ],
                                [ '%d' ]
                            );
                            break;
                    }
                }
            } // Meta Table Loop ENDS

            $wpdb->update(
                $table_events,
                [ $status_key => $status_value ],
                [ 'id'  => $event_id ],
                [ '%s' ],
                [ '%d' ]
            );
        }
    }
    
    new WDASS__Run_Events();
}
