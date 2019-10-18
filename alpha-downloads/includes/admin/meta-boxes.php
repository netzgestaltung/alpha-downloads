<?php
/**
 * Alpha Downloads Metaboxes
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Metaboxes
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Meta Boxes
 *
 * @since  1.0
 */
function alpha_register_meta_boxes() {
	add_meta_box( 'alpha_download', __( 'Download', 'alpha-downloads' ), 'alpha_meta_box_download', 'alpha_download', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'alpha_register_meta_boxes' );

/**
 * Add Correct Enc Type
 *
 * @since  1.0
 */
function alpha_form_enctype() {
	if ( get_post_type() == 'alpha_download' ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'alpha_form_enctype' );

/**
 * Add post type custom update messages
 *
 * @since  1.3.8
 */
function alpha_update_messages( $messages ) {
	global $post, $post_ID;

	$messages['alpha_download'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Download updated. Use the %s shortcode to include it in posts or pages.', 'alpha-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		2 => __('Custom field updated.', 'alpha-downloads'),
		3 => __('Custom field deleted.', 'alpha-downloads'),
		4 => sprintf( __('Download updated. Use the %s shortcode to include it in posts or pages.', 'alpha-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		5 => isset($_GET['revision']) ? sprintf( __('Download restored to revision from %s', 'alpha-downloads'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Download published. Use the %s shortcode to include it in posts or pages.', 'alpha-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		7 => __('Download saved.', 'alpha-downloads'),
		8 => __('Download submitted.', 'alpha-downloads'),
		9 => sprintf( __('Download scheduled for: <strong>%1$s</strong>.', 'alpha-downloads'),
		  date_i18n( __( 'M j, Y @ G:i', 'alpha-downloads' ), strtotime( $post->post_date ) ) ),
		10 => __('Download draft updated.', 'alpha-downloads'),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'alpha_update_messages' );

/**
 * Bulk messages
 *
 * @param array $bulk_messages
 * @param array $bulk_counts
 *
 * @return array
 */
function alpha_bulk_messages( $bulk_messages, $bulk_counts ) {
	$screen = get_current_screen();

	if ( 'alpha_download' !== $screen->post_type ) {
		return $bulk_messages;
	}

	$bulk_messages['post'] = array(
		'updated'   => _n( '%s download updated.', '%s downloads updated.', $bulk_counts['updated'], 'alpha-downloads-customizer' ),
		'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 download not updated, somebody is editing it.', 'alpha-downloads-customizer' ) : _n( '%s download not updated, somebody is editing it.', '%s downloads not updated, somebody is editing them.', $bulk_counts['locked'], 'alpha-downloads-customizer' ),
		'deleted'   => _n( '%s download permanently deleted.', '%s downloads permanently deleted.', $bulk_counts['deleted'], 'alpha-downloads-customizer' ),
		'trashed'   => _n( '%s download moved to the Trash.', '%s downloads moved to the Trash.', $bulk_counts['trashed'], 'alpha-downloads-customizer' ),
		'untrashed' => _n( '%s download restored from the Trash.', '%s downloads restored from the Trash.', $bulk_counts['untrashed'], 'alpha-downloads-customizer' ),
	);

	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'alpha_bulk_messages', 10, 2 );

/**
 * Render Download Metabox
 *
 * @since  1.5
 */
function alpha_meta_box_download( $post ) {
	global $post;

	$file_url = get_post_meta( $post->ID, '_alpha_file_url', true );
	$file_url = ( false != $file_url ) ? $file_url : '';
	
	$file_size = get_post_meta( $post->ID, '_alpha_file_size', true );
	$file_size = ( false != $file_size ) ? size_format( $file_size, 1 ) : '';
	
	$file_count = get_post_meta( $post->ID, '_alpha_file_count', true );
	$file_count = ( false != $file_count ) ? $file_count : 0;

	$file_options = get_post_meta( $post->ID, '_alpha_file_options', true );

	// Update status args
	$status_args = array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
		'nonce' 		=> wp_create_nonce( 'alpha_download_update_status' ),
		'action'    	=> 'alpha_download_update_status',
		'default_icon'	=> alpha_get_file_icon( 'default' ),
		'lang_local'	=> __( 'Local File', 'alpha-downloads' ),
		'lang_remote'	=> __( 'Remote File', 'alpha-downloads' ),
		'lang_warning'	=> __( 'Inaccessible File', 'alpha-downloads' )
	);

	// Plupload args
	$plupload_args = array(
		'runtimes'            => 'html5, silverlight, flash, html4',
		'browse_button'       => 'alpha-upload-button',
		'container'           => 'alpha-upload-container',
		'drop_element'		  => 'alpha-drag-drop-area',
		'file_data_name'      => 'async-upload',            
		'multiple_queues'     => false,
		'multi_selection'	  => false,
		'max_file_size'       => wp_max_upload_size() . 'b',
		'url'                 => admin_url( 'admin-ajax.php' ),
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'             => array( array( 'title' => __( 'Allowed Files' ), 'extensions' => '*' ) ),
		'multipart'           => true,
		'urlstream_upload'    => true,

		// additional post data to send to our ajax hook
		'multipart_params'    => array(
			'_ajax_nonce' 		=> wp_create_nonce( 'alpha_download_upload' ),
			'action'      		=> 'alpha_download_upload',
			'post_id'			=> $post->ID
		)
	);

	// File browser args
	$file_browser_args = array(
		'root'			=> alpha_get_upload_dir( 'basedir' ) . '/',
		'url'			=> alpha_get_upload_dir( 'baseurl' ) . '/',
		'script'		=> ALPHA_PLUGIN_URL . 'assets/vendor/jqueryFileTree/connectors/jqueryFileTree.php'
	);

	?>

	<script type="text/javascript">
		var updateStatusArgs = <?php echo json_encode( $status_args ); ?>;
	</script>
	
	<div id="alpha-new-download" style="<?php echo ( !isset( $file_url ) || empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<a href="#alpha-upload-modal" class="button alpha-modal-action"><?php _e( 'Upload File', 'alpha-downloads' ); ?></a>
		<a href="#alpha-select-modal" class="button alpha-modal-action select-existing"><?php _e( 'Existing File', 'alpha-downloads' ); ?></a>
	</div>
	<div id="alpha-existing-download" style="<?php echo ( isset( $file_url ) && !empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<div class="left-panel">
			<div class="file-icon">	
				<img src="<?php echo alpha_get_file_icon( $file_url ); ?>" />
			</div>
			<div class="file-name"><?php echo alpha_get_file_name( $file_url ); ?></div>
			<div class="file-size"><?php echo $file_size; ?></div>
			<div class="file-status">
				<span class="status spinner"></span>
			</div>
		</div>
		<div class="right-panel">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Download Count', 'alpha-downloads' ); ?>
						</th>
						<td>
							<input name="download_count" id="download_count" class="regular-text" type="number" min="0" value="<?php echo $file_count; ?>" />
							<p class="description"><?php _e( 'The number of times this file has been downloaded.' ); ?></p>
						</td>
					</tr>
					<?php $members_only = ( isset( $file_options['members_only'] ) ? $file_options['members_only'] : '' ); ?>
					<?php $members_only_redirect = ( isset( $file_options['members_only_redirect'] ) ? $file_options['members_only_redirect'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Members Only', 'alpha-downloads' ); ?>
						</th>
						<td>
							<label for="members_only_true"><input name="members_only" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
							<label for="members_only_false"><input name="members_only" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
							<label for="members_only_inherit"><input name="members_only" id="members_only_inherit" type="radio" value <?php echo ( '' === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'alpha-downloads' ); ?></label>
							<p class="description"><?php _e( 'Allow only logged in users to download this file.' ); ?></p>
							<div id="members_only_sub" class="alpha-sub-option" style="<?php echo ( 0 === $members_only ) ? 'display: none;' : ''; ?>">
								<?php 

								$args = array(
									'name'						=> 'members_only_redirect',
									'depth'						=> 0,
									'selected'					=> $members_only_redirect,
									'show_option_none'			=> __( 'Inherit', 'alpha-downloads' ),
									'option_none_value'			=> 	'',
									'echo'						=> 0
								);
								
								$list = wp_dropdown_pages( $args );

								// Add option groups
								$list = explode( '<option value="">' . __( 'Inherit', 'alpha-downloads' ) . '</option>', $list );
								$list = implode( '<optgroup label="' . __( 'Global', 'alpha-downloads' ) . '"><option value="">' . __( 'Inherit', 'alpha-downloads' ) . '</option></optgroup><optgroup label="' . __( 'Pages', 'alpha-downloads' ) . '">', $list );
								$list = explode( '</select>', $list );
								$list = implode( '</optgroup></select>', $list );

								echo $list; 
								?>

								<p class="description"><?php _e( 'The page to redirect non-members.' ); ?></p>
							</div>
						</td>
					</tr>
					<?php $open_browser = ( isset( $file_options['open_browser'] ) ? $file_options['open_browser'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Open In Browser', 'alpha-downloads' ); ?>
						</th>
						<td>
							<label for="open_browser_true"><input name="open_browser" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
							<label for="open_browser_false"><input name="open_browser" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
							<label for="open_browser_inherit"><input name="open_browser" id="open_browser_inherit" type="radio" value <?php echo ( '' === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'alpha-downloads' ); ?></label>
							<p class="description"><?php echo sprintf( __( 'This file will attempt to open in the browser window. If the file is located within the Alpha Downloads upload directory, you will need to set the %sfolder protection%s setting to \'No\'.', 'alpha-downloads' ), '<a href="' . admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings&tab=advanced' ) . '" target="_blank">', '</a>' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="footer">
			<?php _e( 'Replace File:', 'alpha-downloads' ); ?>
			<a href="#alpha-upload-modal" class="button alpha-modal-action"><?php _e( 'Upload', 'alpha-downloads' ); ?></a>
			<a href="#alpha-select-modal" class="button alpha-modal-action select-existing"><?php _e( 'Select Existing', 'alpha-downloads' ); ?></a>
			<a href="#alpha-delete-modal" class="delete alpha-delete-file"><?php _e( 'Delete File', 'alpha-downloads' ); ?></a>
		</div>
	</div>

	<script type="text/javascript">
		var pluploadArgs = <?php echo json_encode( $plupload_args ); ?>;
	</script>

	<div id="alpha-upload-modal" class="alpha-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="alpha-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div id="alpha-upload-container" class="alpha-modal-content">
			<h1><?php _e( 'Upload File', 'alpha-downloads' ); ?></h1>
			<div id="alpha-drag-drop-area-container">
				<div id="alpha-drag-drop-area">
					<p class="drag-drop-info"><?php _e( 'Drop file here', 'alpha-downloads' ); ?></p>
					<p><?php _e( 'or', 'alpha-downloads' ); ?></p>
					<p class="drag-drop-button"><input id="alpha-upload-button" type="button" value="<?php _e( 'Select File', 'alpha-downloads' ); ?>" class="button" />
					<div id="alpha-progress-percent" style="width: 0%;"></div>
					<div id="alpha-progress-text">0%</div>
				</div>
			</div>
			<p><?php printf( __( 'Maximum upload file size: %s.', 'alpha-downloads' ), size_format( wp_max_upload_size(), 1 ) ); ?></p>
			<div id="alpha-progress-error" style="display: none"></div>
		</div>
	</div>

	<script type="text/javascript">
		var fileBrowserArgs = <?php echo json_encode( $file_browser_args ); ?>;
	</script>

	<div id="alpha-select-modal" class="alpha-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="alpha-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="alpha-modal-content">
			<h1><?php _e( 'Existing File', 'alpha-downloads' ); ?></h1>
			<p><?php _e( 'Manually enter a file URL, or use the file browser.', 'alpha-downloads' ); ?></p>
			<p>	
				<?php wp_nonce_field( 'ddownload_file_save', 'ddownload_file_save_nonce' ); ?>
				<input name="alpha-file-url" id="alpha-file-url" type="text" class="large-text" value="<?php echo $file_url; ?>" placeholder="<?php _e( 'File URL or path...', 'alpha-downloads' ); ?>" />
			</p>
			<p>
				<div id="alpha-file-browser"><p><?php _e( 'Loading...', 'alpha-downloads' ); ?></p></div>
			</p>
			<p>
				<a href="#" id="alpha-select-done" class="button button-primary"><?php _e( 'Confirm', 'alpha-downloads' ); ?></a>
			</p>
		</div>
	</div>

	<?php
	
}

/**
 * Update Status Ajax
 *
 * @since  1.5
*/
function alpha_download_update_status_ajax() {

	global $alpha_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'alpha_download_update_status', 'nonce', false ) || !current_user_can( apply_filters( 'alpha_cap_add_new', 'edit_pages' ) ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'alpha-downloads' )
		) );

		die();
	}

	$file_url = trim( $_REQUEST['url'] );

	if( $result = alpha_get_file_status( $file_url ) ) {
		// Cache remote file sizes, for 15 mins
		if ( 'remote' === $result['type'] ) {
			$cached_remotes = get_transient( 'alpha_remote_file_sizes' );

			if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
				$cached_remotes[esc_url_raw( $file_url )] = $result['size'];
				set_transient( 'alpha_remote_file_sizes', $cached_remotes, 900 );
			}
		}

		// Add extra data to result
		$result['size'] = size_format( $result['size'], 1 );
		$result['icon']	= alpha_get_file_icon( $file_url );
		$result['filename'] = alpha_get_file_name( $file_url );

		// Exists
		echo json_encode( array (
			'status'	=> 'success',
			'content'	=> $result
		) );
	}
	else {
		$result['filename'] = alpha_get_file_name( $file_url );

		echo json_encode( array (
			'status'	=> 'error',
			'content'	=> $result
		) );
	}

	die();
}
add_action( 'wp_ajax_alpha_download_update_status', 'alpha_download_update_status_ajax' );

/**
 * Save Meta Boxes
 *
 * @since  1.0
 */
function alpha_meta_boxes_save( $post_id ) {
	// Check for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	// Check for other post types
	if ( isset( $post->post_type ) && $post->post_type != 'alpha_download' ) {
		return;
	}

	// Check user has permission
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Check for file nonce
	if ( isset( $_POST['ddownload_file_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_file_save_nonce'], 'ddownload_file_save' ) ) {	

		$file_url = trim( $_POST['alpha-file-url'] );

		/**
		 * Get cached remote file sizes
		 *
		 * Ajax grabs the remote file size on each file update, it makes sense to cache
		 * the value and use it here. Otherwise, the user has to wait for headers to return
		 * when saving a file.
		 */
		$cached_remotes = get_transient( 'alpha_remote_file_sizes' );
		
		// Check for cached remote file size
		if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
			$file = alpha_get_file_status( $file_url );
			$file_size = $file['size'];
		}
		else {
			$file_size = $cached_remotes[esc_url_raw( $file_url )];
		}

		// Save file url and size
		update_post_meta( $post_id, '_alpha_file_url', $file_url );
		update_post_meta( $post_id, '_alpha_file_size', $file_size );

		// Save download count
		if ( isset( $_POST['download_count'] ) && '' !== trim( $_POST['download_count'] ) ) {
			update_post_meta( $post_id, '_alpha_file_count', trim( $_POST['download_count'] ) );
		}

		// Get current file options
		$file_options = get_post_meta( $post_id, '_alpha_file_options', true );
		$file_options = ( false == $file_options ) ? array() : $file_options;

		// Set file options
		$default_options = array(
			'members_only',
			'members_only_redirect',
			'open_browser'
		);

		// Loop through and save to file array
		foreach ( $default_options as $option ) {
			if ( isset( $_POST[$option] ) && '' !== $_POST[$option] ) {
				$file_options[$option] = absint( $_POST[$option] );
			}
			else {
				unset( $file_options[$option] );
			}
		}

		update_post_meta( $post_id, '_alpha_file_options', $file_options );
	}
}
add_action( 'save_post', 'alpha_meta_boxes_save' );
