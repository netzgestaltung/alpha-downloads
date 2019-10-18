<?php
/**
 * Upgrades
 *
 * @package  	Alpha Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check for Upgrades
 *
 * @since  1.4
 */
function alpha_check_upgrades() {
	$version = get_option( 'alpha-downloads-version' );

	if ( version_compare( $version, '1.4', '<' ) ) {
		alpha_upgrade_1_4();
	}

	if ( version_compare( $version, '1.5', '<' ) ) {
		alpha_upgrade_1_5();
	}

	// Update version numbers
	if ( $version !== ALPHA_VERSION ) {
		
		// Previous version installed, save prior version to db
		if ( false !== $version ) {
			update_option( 'alpha-downloads-prior-version', $version );
		}
	
		update_option( 'alpha-downloads-version', ALPHA_VERSION );
	}

}
add_action( 'plugins_loaded', 'alpha_check_upgrades' );

/**
 * Version 1.4
 *
 * Add custom database structure for download statistics and
 * check for legacy logs.
 *
 * @since  1.4
 */
function alpha_upgrade_1_4() {
	global $alpha_statistics, $wpdb, $alpha_notices;

	// Setup new table structure
	$alpha_statistics->setup_table();

	// Check for legacy logs
	$sql = $wpdb->prepare( "
		SELECT COUNT(ID) FROM $wpdb->posts
		WHERE post_type = %s
	",
	'alpha_log' );

	$result = $wpdb->get_var( $sql );

	// Add flag to options table
	if ( $result > 0 ) {
		add_option( 'alpha-downloads-legacy-logs', $result );
	}

	// Add new option for admin notices
	add_option( 'alpha-downloads-notices', array() );

	// Add upgrade notice
	$message = __( 'Alpha Downloads updated to version 1.4.', 'alpha-downloads' );

	if ( get_option( 'alpha-downloads-legacy-logs' ) ) {
		
		$message .=  ' ' . sprintf( __( 'Please visit the %slogs screen%s to migrate your download statistics.', 'alpha-downloads' ), '<a href="' . admin_url( 'edit.php?post_type=alpha_download&page=alpha_statistics' ) . '">', '</a>' );
	}

	$alpha_notices->add( 'updated', $message );
}

/**
 * Version 1.5
 *
 * Update options with new sub-options.
 *
 * @since  1.5
 */
function alpha_upgrade_1_5() {
	global $alpha_options, $alpha_notices;

	// Convert old durations to boolean values
	foreach( array( 'grace_period' ,'auto_delete' ) as $key ) {
		$alpha_options[$key] = ( $alpha_options[$key] > 0 ) ? 1 : 0;
	}

	// Handle cache name change
	$alpha_options['cache'] = ( $alpha_options['cache_duration'] > 0 ) ? 1 : 0;

	// Update options
	$new_options = wp_parse_args( $alpha_options, alpha_get_default_options() );
	update_option( 'alpha-downloads', $new_options );

	// Add upgrade notice
	$alpha_notices->add( 'updated', __( 'Alpha Downloads updated to version 1.5.', 'alpha-downloads' ) );
}

/**
 * 1.4 Admin Notices
 *
 * @since  1.4
 */
function alpha_upgrade_notices_1_4() {

	// Only show on statistics page
	if ( isset( $_GET['page'] ) && 'alpha_statistics' == $_GET['page'] ) {

		// Only show if we have legacy logs		
		if ( !$legacy_logs = get_option( 'alpha-downloads-legacy-logs' ) ) {
			return;
		}

		// Enqueue our migration JS
		wp_enqueue_script( 'alpha-admin-js-legacy-logs' );

		// Output ajax url object
		wp_localize_script( 'alpha-admin-js-legacy-logs', 'alpha_admin_logs_migrate', array(
			'ajaxurl'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
			'action'		=> 'alpha_migrate_logs',
			'nonce'			=> wp_create_nonce( 'alpha_migrate_logs' ),
			'migrate_text'	=> __( 'Migrate', 'alpha-downloads' ),
			'stop_text' 	=> __( 'Stop', 'alpha-downloads' ),
			'error_text' 	=> __( 'The migration could not start due to an error.', 'alpha-downloads' )
		) );

		?>
		<div id="alpha_migrate_message" class="error">
			<p><?php echo sprintf( __( 'You have %s logs from an older version of Alpha Downloads. %sPlease make a backup of your database before migrating!%s', 'alpha-downloads' ), '<strong id="alpha_migrate_count">' .  $legacy_logs . '</strong>', '<p><strong>', '</strong></p>' ); ?></p>
			<p style="overflow: hidden;">
				<input type="button" id="alpha_migrate_button" name="alpha_migrate" value="<?php _e( 'Migrate', 'alpha-downloads' ); ?>" class="button button-primary" style="float: left;" />
				<span class="spinner" style="float: left; margin-left: 10px;"></span>
			</p>
			<noscript>
				<p class="description"><?php _e( 'JavaScript must be enabled to migrate legacy logs.', 'alpha-downloads' ); ?></p>
			</noscript>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'alpha_upgrade_notices_1_4' );

/**
 * 1.4 Migrate Legacy Logs
 *
 * Migrate logs from old postmeta table to 
 * custom statistics table. Cleanup postmeta
 * afterwards.
 *
 * @since  1.4
 */
function alpha_migrate_logs_ajax() {

	global $wpdb;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'alpha_migrate_logs', 'nonce', false ) || !current_user_can( 'manage_options' ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'alpha-downloads' )
		) );

		die();
	}

	// Disable max_execution_time
	set_time_limit( 0 );

	// Get amount of legacy logs
	$sql = $wpdb->prepare( "
		SELECT COUNT(ID) FROM $wpdb->posts
		WHERE post_type = %s
	",
	'alpha_log' );

	$total_logs = $wpdb->get_var( $sql );

	// We have old logs, lets grab them
	if ( $total_logs > 0 ) {

		// Query for the results we need in blocks of 100
		$sql = $wpdb->prepare( "
			SELECT $wpdb->posts.ID AS log_id, 
				   $wpdb->posts.post_date AS date, 
				   $wpdb->posts.post_author AS user,
				   download_id.meta_value AS download_id,
				   user_ip.meta_value AS user_ip,
				   user_agent.meta_value AS user_agent
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta download_id 
				ON $wpdb->posts.ID = download_id.post_id 
				AND download_id.meta_key = %s
			LEFT JOIN $wpdb->postmeta user_ip 
				ON $wpdb->posts.ID = user_ip.post_id 
				AND user_ip.meta_key = %s
			LEFT JOIN $wpdb->postmeta user_agent
				ON $wpdb->posts.ID = user_agent.post_id 
				AND user_agent.meta_key = %s
			WHERE post_type = %s 
			ORDER BY post_date ASC LIMIT %d
		",
		'_alpha_log_download',
		'_alpha_log_ip',
		'_alpha_log_agent',
		'alpha_log',
		mt_rand( 95, 105 ) );

		// Store logs
		$logs = $wpdb->get_results( $sql, ARRAY_A );

		// Loop through, move and delete
		foreach ( $logs as $log ) {

			$sql = $wpdb->prepare( "
				INSERT INTO $wpdb->ddownload_statistics (post_id, date, user_id, user_ip, user_agent)
				VALUES (%d, %s, %d, %s, %s)
			",
			$log['download_id'],
			$log['date'],
			$log['user'],
			inet_pton( $log['user_ip'] ),
			$log['user_agent'] );

			if ( $wpdb->query( $sql ) ) {
				// Remove legacy log
				wp_delete_post( $log['log_id'], true );

				// Reduce counter
				$total_logs--;
			}
		}

		// Update legacy log flag
		if ( $total_logs > 0 ) {
			update_option( 'alpha-downloads-legacy-logs', $total_logs );
		}
		else {
			delete_option( 'alpha-downloads-legacy-logs' );
		}
	}

	// Return success
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $total_logs
	) );

	die();
}
add_action( 'wp_ajax_alpha_migrate_logs', 'alpha_migrate_logs_ajax' );
