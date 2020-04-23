<?php
/**
 * Alpha Downloads Ajax
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Ajax
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Ajax upload file
 *
 * @since  1.0
 */
function alpha_download_upload_ajax() {

	if ( !check_ajax_referer( 'alpha_download_upload', false, false ) ) {

		// Echo error message
		die( '{ "jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . __( 'Failed security checks!', 'alpha-downloads' ) . '" } }' );
	}

	// Set upload dir
	add_filter( 'upload_dir', 'alpha_set_upload_dir' );

	// Upload the file
	$file = wp_handle_upload( $_FILES['async-upload'], array( 'test_form'=> true, 'action' => 'alpha_download_upload' ) );

	// Check for success
	if ( isset( $file['url'] ) ) {
		// Post ID
		$post_id = $_REQUEST['post_id'];
		$post_thumbnail = false;

		// Add/update post meta
		update_post_meta( $post_id, '_alpha_file_url', $file['url'] );
		update_post_meta( $post_id, '_alpha_file_size', $_FILES['async-upload']['size'] );

	  // Remove upload dir for thumbnails going to the normal upload dir
	  remove_filter('upload_dir', 'alpha_set_upload_dir');
    if ( !has_post_thumbnail($post_id) ) {
	    $pdf_thumbnail = alpha_generate_pdf_thumbnail($file['file']);
	    if ( $pdf_thumbnail !== false ) {
	      $post_thumbnail = alpha_set_post_thumbnail($post_id, $pdf_thumbnail);
      }
    }

		// Echo success response
		// die( '{"jsonrpc" : "2.0", "file" : {"url": "' . $file['url'] . '", "thumbnail":"' . $file_thumbnail .'"}}' );
		$json = array(
		  'jsonrpc' => '2.0',
		  'file' => array(
		    'url' => $file['url'],
		    'thumbnail' => $post_thumbnail,
		    'pdf_thumbnail' => $pdf_thumbnail,
		  ),
		);
		wp_send_json($json);
	}
	else {
		// Echo error message
		die( '{"jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . $file['error'] . '"}, "details" : "' . $file['error'] . '"}' );
	}
}
add_action( 'wp_ajax_alpha_download_upload', 'alpha_download_upload_ajax' );
