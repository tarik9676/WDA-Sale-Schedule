<?php


defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WDASS__Run_Events' ) ) {
    class WDASS__Run_Events {

        /*-------------------------------------------
        * Hook into the appropriate actions when
        * the class is constructed.
        *-------------------------------------------*/
        public function __construct() {
            add_action( 'init', array( $this, 'check_n_run_events' ) );
        }
    
    
        /*-------------------------------------------
        *  Find & execute pending events
        *-------------------------------------------*/
        public function check_n_run_events () {
            global $wpdb;
            
            $table_events = $wpdb->prefix . 'wdass_events';

            date_default_timezone_set( get_option( 'wdass_timezone', "GMT+0" ) );


            /*----------------------------------------------------
            *  Getting all pending schedule events from DB
            *----------------------------------------------------*/
            $schedule_events_sql = $wpdb->get_results(
                "SELECT * FROM $table_events
                WHERE schedule_status = 'pending';"
            );

            if ( count($schedule_events_sql) ) {
                foreach ( $schedule_events_sql as $schedule_event ) {
                    $schedule_time_string = $schedule_event->schedule_date . ' ' . $schedule_event->schedule_time;

                    if ( strtotime( current_time( 'mysql' ) ) > strtotime( $schedule_time_string ) ) {
                        $this->execute( $wpdb, $schedule_event->object_id, $schedule_event->id, 'modified', 'schedule_status', 'completed' );
                    }

                } // Events Table Loop ENDS
            }


            // /*----------------------------------------------------
            // *  Getting all pending restore events from DB
            // *----------------------------------------------------*/
            // $restore_events_sql = $wpdb->get_results(
            //     "SELECT * FROM $table_events
            //     WHERE restore_status = 'restore_later' OR restore_status = 'restore_now';"
            // );

            // if ( count($restore_events_sql) ) {
            //     foreach ( $restore_events_sql as $restore_event ) {
            //         $restore_time_string = $restore_event->restore_date . ' ' . $restore_event->restore_time;

            //         if ( $restore_event->restore_status == 'restore_now' ) {
            //             $this->execute( $wpdb, $restore_event->object_id, $restore_event->id, 'original', 'restore_status', 'no_restore' );
            //         } else if (
            //             $restore_event->restore_status == 'restore_later'
            //             && strtotime( current_time( 'mysql' ) ) > strtotime( $restore_time_string )
            //         ) {
            //             $this->execute( $wpdb, $restore_event->object_id, $restore_event->id, 'original', 'restore_status', 'no_restore' );
            //         }
            //     } // Events Table Loop ENDS
            // }
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
    
    $wdass__run_events = new WDASS__Run_Events();
}
