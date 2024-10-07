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

            /*----------------------------------------------------
            *  Getting all pending schedule events
            *----------------------------------------------------*/
            $pending_events_cache_key = 'wdass_pending_events_cache';
            $pending_events = wp_cache_get( $pending_events_cache_key );

            if ($pending_events === false) {
                global $wpdb;

                $table_events = $wpdb->prefix . 'wdass_events';

                $pending_events = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM %i WHERE schedule_status = %s;",
                    [ $table_events, 'pending' ]
                ));

                wp_cache_set($pending_events_cache_key, $pending_events);
            }

            if ( count($pending_events) ) {
                foreach ( $pending_events as $event ) {
                    $schedule_time_string = $event->schedule_date . ' ' . $event->schedule_time;

                    if ( strtotime(current_time('mysql')) > strtotime($schedule_time_string) ) {
                        $this->execute( $wpdb, $event->object_id, $event->id, 'modified', 'schedule_status', 'completed' );
                    }

                } // Events Table Loop ENDS
            }
        }

        private function execute ( $wpdb, $post_id, $event_id, $data_type, $status_key, $status_value ) {

            $table_events   = $wpdb->prefix . 'wdass_events';

            $all_posts_data = [];
            

            /*----------------------------------------------------
            *  Getting all event metas
            *----------------------------------------------------*/
            $cache_key_events_metas = 'wdass_events_metas_cache';
            $all_event_metas = wp_cache_get( $cache_key_events_metas );

            if ($all_event_metas === false) {
                $table_eventmeta = $wpdb->prefix . 'wdass_eventmeta';

                $all_event_metas = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM %i
                    WHERE `event_id` = %d AND `type` = %s AND NOT `content` = %s;",
                    [ $table_eventmeta, $event_id, $data_type, '404' ]
                ));

                wp_cache_set($cache_key_events_metas, $all_event_metas);
            }
            


            foreach ( $all_event_metas as $meta ) {

                $first_character = substr($meta->meta_key, 0, 1);
                
                if ( $meta->content !== '404' ) {
                    switch ( $first_character ) {
                        case '_':
                            $all_posts_data[ $meta->post_id ][ 'meta_input' ][ $meta->meta_key ] = $meta->content;

                            if ( $meta->meta_key == '_sale_price') {
                                $all_posts_data[ $meta->post_id ][ 'meta_input' ][ '_price' ] = $meta->content;
                            }
                            break;
    
                        case 't':
                            $terms = json_decode( $meta->content, true );
                            
                            wp_set_object_terms( $post_id, $terms[ 'product_cat' ], 'product_cat' );
                            wp_set_object_terms( $post_id, $terms[ 'product_tag' ], 'product_tag' );
                            break;
                        
                        default:
                            $all_posts_data[ $meta->post_id ][ $meta->meta_key ] = $meta->content;
                            break;
                    }
                }
            } // Meta Table Loop ENDS
            


            /*----------------------------------------------------
            *  Update the post with scheduled data
            *----------------------------------------------------*/

            foreach ($all_posts_data as $pid => $post_object) {
                $post_object['ID'] = $pid;
                wp_update_post( $post_object );
            }
            

            update_option( 'wplab_test', json_encode($all_posts_data) );


            /*----------------------------------------------------
            *  Update event status on custom table
            *----------------------------------------------------*/

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
