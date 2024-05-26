<?php

defined( 'ABSPATH' ) || exit;


/*---------------------------------------------------------
*  Displaying all the events under 'Events' submenu
*---------------------------------------------------------*/
function wdass_scheduled_events () {
	global $wpdb;
	
	$table_events	= $wpdb->prefix . 'wdass_events';

	$events_sql = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i;", [ $table_events ]));

	if ( count( $events_sql ) ) {
		?>
		<h1>All Events</h1>
		<table class="wp-list-table" id="events-table">
			<thead>
				<tr>
					<th>Product</th>
					<th>Schedule Status</th>
					<th>Schedule Time</th>
				</tr>
			</thead>
			<tbody>
			<?php

			foreach ( $events_sql as $event ) {
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