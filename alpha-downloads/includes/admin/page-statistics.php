<?php
/**
 * Alpha Downloads Page Statistics
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Page Statistics
 * @since       1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Statistics Page
 *
 * @since  1.4
 */
function alpha_register_page_statistics() {
	
	global $alpha_statistics_page;

	$alpha_statistics_page = add_submenu_page( 'edit.php?post_type=alpha_download', __( 'Download Logs', 'alpha-downloads' ), __( 'Logs', 'alpha-downloads' ), 'manage_options', 'alpha_statistics', 'alpha_render_page_statistics' );

	// Hook for screen options dropdown
	add_action( "load-$alpha_statistics_page", 'alpha_statistics_screen_options' );
}
add_action( 'admin_menu', 'alpha_register_page_statistics', 20 );

/**
 * Render Statistics Page
 *
 * @since  1.4
 */
function alpha_render_page_statistics() {
	
	?>
	<div class="wrap">
		<h1><?php _e( 'Download Logs', 'alpha-downloads' ); ?>
			<a href="#alpha-stats-export" class="add-new-h2 alpha-modal-action"><?php _e( 'Export', 'alpha-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=alpha_download&page=alpha_statistics&action=empty_logs' ), 'alpha_empty_logs', 'alpha_empty_logs_nonce' ); ?>" class="add-new-h2 alpha_confirm_action" data-confirm="<?php _e( 'You are about to permanently delete the download logs.', 'alpha-downloads' ); ?>"><?php _e( 'Delete', 'alpha-downloads' ); ?></a>
		</h1>

		<div id="alpha-settings-main">	
			<?php do_action( 'ddownload_statistics_header' ); ?>
			
			<?php $table = new ALPHA_List_table(); ?>
			<?php $table->display(); ?>

			<?php do_action( 'ddownload_statistics_footer' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Export Logs Modal
 *
 * @since  1.5
 */
function alpha_render_export_modal() {
	// Ensure only added on statistics screen	
	$screen = get_current_screen();

	if ( 'alpha_download_page_alpha_statistics' !== $screen->id ) {
		return;
	}

	?>

	<div id="alpha-stats-export" class="alpha-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="alpha-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Export Logs', 'alpha-downloads' ); ?></h1>
			<p><?php _e( 'Export log entries to a CSV file. Please select a date range, or leave blank to export all:', 'alpha-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=alpha_download&page=alpha_statistics&action=export' ); ?>">
				<p class="left">
					<label for="alpha_start_date"><?php _e( 'Start Date', 'alpha-downloads' ); ?></label>
					<input name="alpha_start_date" id="alpha_start_date" type="date" ?>
				</p>
				<p class="right">
					<label for="alpha_end_date"><?php _e( 'End Date', 'alpha-downloads' ); ?></label>
					<input name="alpha_end_date" id="alpha_end_date" type="date" ?>
				</p>
				<p>
					<?php wp_nonce_field( 'alpha_export_stats','alpha_export_stats_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Export', 'alpha-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'alpha_render_export_modal' );

/**
 * Statistics Page Actions
 *
 * @since  1.4
 */
function alpha_statistics_actions() {

	//Only perform on statistics page
	if ( isset( $_GET['page'] ) && 'alpha_statistics' == $_GET['page'] ) {

		// Export statistics
		if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {
			alpha_statistics_actions_export();	
		}

		// Empty statistics
		if( isset( $_GET['action'] ) && 'empty_logs' == $_GET['action'] ) {
			alpha_statistics_actions_empty();
		}
	}
}
add_action( 'init', 'alpha_statistics_actions', 0 );

/**
 * Statistics Page Action Export
 *
 * @since  1.5
 */
function alpha_statistics_actions_export() {
	global $alpha_statistics, $alpha_notices;

	// Disable max_execution_time
	set_time_limit( 0 );

	// Verfiy nonce
	check_admin_referer( 'alpha_export_stats', 'alpha_export_stats_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	// Add args to query
	$args = array();

	if ( isset( $_POST['alpha_start_date'] ) && !empty( $_POST['alpha_start_date'] ) ) {
		$args['start'] = $_POST['alpha_start_date'] . ' 00:00:00';
	}

	if ( isset( $_POST['alpha_end_date'] )  && !empty( $_POST['alpha_end_date'] ) ) {
		$args['end'] = $_POST['alpha_end_date'] . ' 23:59:59';
	}

	// Get logs
	$logs = $alpha_statistics->get_logs( $args );

	// Check we have logs before creating file
	if ( NULL == $logs ) {
		$alpha_notices->add( 'error', __( 'You do not have any logs to export in that date range.', 'alpha-downloads' ) );
		
		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=alpha_download&page=alpha_statistics' ) );
		exit();	
	}

	// Get download titles
	$downloads = get_posts( array( 'post_type' => 'alpha_download', 'posts_per_page'   => -1, ) );

	foreach( $downloads as $download ) {
		$download_title[$download->ID] =  $download->post_title;
	}

	// Get user names
	$users = get_users();

	foreach( $users as $user ) {
		$user_name[$user->ID] = $user->user_email;
	}

	// Set filename
	$filename = 'download-logs-' . date( 'Ymd' ) . '.csv';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	$output = fopen( 'php://output', 'w' );

	// Column headings
	fputcsv( $output, array( __( 'ID', 'alpha-downloads' ), __( 'Status', 'alpha-downloads' ), __( 'Date', 'alpha-downloads' ), __( 'Download', 'alpha-downloads' ), __( 'User', 'alpha-downloads' ), __( 'IP Address', 'alpha-downloads' ), __( 'User Agent', 'alpha-downloads' ) ) );

	// Add data
	foreach( $logs as $log ) {
		// Convert download ID to title
		$log['post_id'] = ( isset( $download_title[$log['post_id']] ) ) ? $download_title[$log['post_id']] : __( 'Unknown', 'alpha-downloads' );

		// Convert user ID to email
		$log['user_id'] = ( isset( $user_name[$log['user_id']] ) ) ? $user_name[$log['user_id']] : __( 'Non-member', 'alpha-downloads' );

		// Convert ip to human readable
        if ( ! empty( $log['user_ip'] ) ) {
            $log['user_ip'] = inet_ntop( $log['user_ip'] );
        }
		
		fputcsv( $output, $log );
	}	

	die();
}

/**
 * Statistics Page Action Empty
 *
 * @since  1.5
 */
function alpha_statistics_actions_empty() {
	global $alpha_statistics, $alpha_notices;

	// Verfiy nonce
	check_admin_referer( 'alpha_empty_logs', 'alpha_empty_logs_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = $alpha_statistics->empty_table();

	if ( false === $result ) {
		// Error
		$alpha_notices->add( 'error', __( 'Logs could not be deleted.', 'alpha-downloads' ) );
	}
	else {
		// Success
		$alpha_notices->add( 'updated', __( 'Logs deleted successfully.', 'alpha-downloads' ) );
	}

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=alpha_download&page=alpha_statistics' ) );
	exit();
}

/**
 * Statistics Sreen Options
 *
 * @since  1.4
 */
function alpha_statistics_screen_options() {
 
	global $alpha_statistics_page;

	$screen = get_current_screen();

	if ( !is_object( $screen ) || $screen->id != $alpha_statistics_page ) {
		return;
	}

	// Per page option
	$args = array(
	    'label' => __( 'Download Logs', 'alpha-downloads' ),
	    'default' => 20,
	    'option' => 'alpha_logs_per_page'
	);
	 
	add_screen_option( 'per_page', $args );
 
}

/**
 * Statistics Save Sreen Options
 *
 * @since  1.4
 */
function alpha_statistics_save_screen_options( $status, $option, $value ) {
	
	if ( 'alpha_logs_per_page' == $option ) {
		
		return $value;
	}
}
add_filter( 'set-screen-option' , 'alpha_statistics_save_screen_options', 10, 3 );
