<?php

defined( 'ABSPATH' ) || exit;


/*---------------------------------------------------------
*  Displaying all the events under 'Events' submenu
*---------------------------------------------------------*/
function wdass_scheduled_events () {
	
	$all_events = wp_cache_get( 'wdass_events_cache' );

	if ($all_events === false) {
		global $wpdb;
		$table_events = $wpdb->prefix . 'wdass_events';
		
		$all_events = $wpdb->get_results($wpdb->prepare(
			"SELECT * FROM %i;",
			[ $table_events ]
		));

		// Cache the result
		wp_cache_set($cache_key, $all_events);
	}

	// Now you can use $events

	if ( count( $all_events ) ) {
		?>
		<hr class="wdass__spacer">
		<h1>All Events</h1>
		<table class="wdass--event_table" id="events-table">
			<thead>
				<tr>
					<th>Product</th>
					<th>Schedule Status</th>
					<th>Schedule Time</th>
				</tr>
			</thead>
			<tbody>
			<?php

			foreach ( $all_events as $event ) {
				?>
				<tr>
					<td><strong>#<?php echo esc_html( $event->object_id ); ?></strong> <?php echo esc_html( get_the_title( $event->object_id ) ); ?></td>
					<td><?php echo esc_html( ucwords( str_replace('_', ' ', $event->schedule_status) ) ); ?></td>
					<td><?php echo esc_html( $event->schedule_date . ' ' . $event->schedule_time ); ?></td>
				</tr>
				<?php
			}

			?>
			</tbody>
		</table>
		<?php
	} else {
		?><h1>No events found!</h1><?php
	}
}