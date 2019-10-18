<?php
/**
 * Alpha Downloads Cron
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Cron
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Cron Events
 *
 * @since  1.3
 */
function alpha_cron_register() {
	
	// Daily
	if ( !wp_next_scheduled( 'alpha_cron_daily' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'alpha_cron_daily' );
	}	

	// Weekly
	if ( !wp_next_scheduled( 'alpha_cron_weekly' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'alpha_cron_weekly' );
	}	
}
add_action( 'admin_init', 'alpha_cron_register' );

/**
 * Daily Events
 *
 * @since  1.4
 */
function alpha_cron_daily() {

	global $alpha_options, $alpha_statistics;

	// Delete old logs
	if ( $alpha_options['auto_delete'] == 1 ) {

		$date = $alpha_statistics->convert_days_date( $alpha_options['auto_delete_duration'] );
		$limit = apply_filters( 'alpha_cron_delete_limit', 1000 );

		$alpha_statistics->delete_logs( array( 'end_date' => $date, 'limit' => $limit ) );
	}

}
add_action( 'alpha_cron_daily', 'alpha_cron_daily' );

/**
 * Weekly Events
 *
 * @since  1.3
 */
function alpha_cron_weekly() {
	// Run folder protection
	alpha_folder_protection();
}
add_action( 'alpha_cron_weekly', 'alpha_cron_weekly' );

/**
 * Add Cron Schedules
 *
 * @since  1.3
 */
function alpha_cron_schedules( $schedules ) {
	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once Weekly' )
 	);

 	return $schedules;
}
add_filter( 'cron_schedules', 'alpha_cron_schedules' );
