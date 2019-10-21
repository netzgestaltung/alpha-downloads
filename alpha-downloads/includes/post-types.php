<?php
/**
 * Alpha Downloads Post Types
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Post Types
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Download Post Type
 *
 * @since  1.0
 */
function alpha_download_post_type() {
	$labels = array(
		'name'               => __( 'Downloads', 'alpha-downloads' ),
		'singular_name'      => __( 'Download', 'alpha-downloads' ),
		'add_new'            => __( 'Add New', 'alpha-downloads' ),
		'add_new_item'       => __( 'Add New Download', 'alpha-downloads' ),
		'edit_item'          => __( 'Edit Download', 'alpha-downloads' ),
		'new_item'           => __( 'New Download', 'alpha-downloads' ),
		'all_items'          => __( 'All Downloads', 'alpha-downloads' ),
		'view_item'          => __( 'View Download', 'alpha-downloads' ),
		'search_items'       => __( 'Search Downloads', 'alpha-downloads' ),
		'not_found'          => __( 'No downloads found', 'alpha-downloads' ),
		'not_found_in_trash' => __( 'No downloads found in Trash', 'alpha-downloads' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Downloads', 'alpha-downloads' ),
	);

	$args = array(
		'labels'          => apply_filters( 'alpha_ddownload_labels', $labels ),
		'public'          => true,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-download',
		'capability_type' => apply_filters( 'alpha_ddownload_cap', 'post' ),
		'supports'        => apply_filters( 'alpha_ddownload_supports', array( 'title' ) ),
	);
	register_post_type( 'alpha_download', apply_filters( 'alpha_ddownload_args', $args ) );
}
add_action( 'init', 'alpha_download_post_type' );

/**
 * Register single template for Download Post Type
 * @see
 * - https://wordpress.stackexchange.com/questions/17385/custom-post-type-templates-from-plugin-folder/350859#350859
 * - https://wpshout.com/hacking-the-wordpress-template-hierarchy/
 * @since  alpha 0.6.7
 */
function alpha_download_template($single_template) {
  $alpha_download_template = ALPHA_PLUGIN_DIR . 'templates/single-alpha_download.php';
  return get_post_type() === 'alpha_download' && file_exists($alpha_download_template) ? $alpha_download_template: $single_template;
}
add_filter('single_template', 'alpha_download_template');


/**
 * Redirect single download post request when not allowed
 * @see
 * - https://wpshout.com/hacking-the-wordpress-template-hierarchy/
 * - permission handling from /includes/process-download.php method alpha_download_process
 * @since  alpha 0.6.7
 */
function alpha_download_template_redirect() {
	$file_options = get_post_meta($post->ID, '_alpha_file_options', true);

	// Check only single post request for alpha_download post type
	if ( is_single() && get_post_type() === 'alpha_download' ) {
	 	// Check for members only
	  if ( !alpha_download_permission($file_options) ) {
	    global $alpha_options;	  
      $post = get_post();
      
		  do_action('ddownload_download_permission', $post->ID);
		  
	    // Get redirect location
	    $redirect_ID = isset($file_options['members_only_redirect']) ? $file_options['members_only_redirect'] : $alpha_options['members_only_redirect'];

		  // Try to redirect
		  if ( $redirect_location = get_permalink($redirect_ID) ) {
			  wp_safe_redirect($redirect_location);
			  exit();
		  } else {
			  // Invalid page provided, show error message
			  wp_die(__('Please login to download this file!', 'alpha-downloads'));
		  }
    }
  }
}
add_action('template_redirect', 'alpha_download_template_redirect');

/**
 * Download Post Type Column Headings
 *
 * @since  1.0
 */
function alpha_download_column_headings( $columns ) {
	global $alpha_options;

	$columns = array(
		'cb'           => '<input type="checkbox" />',
		'title'        => __( 'Title', 'alpha-downloads' ),
		'file'         => __( 'File', 'alpha-downloads' ),
		'filesize'     => __( 'File Size', 'alpha-downloads' ),
		'shortcode'    => __( 'Shortcode', 'alpha-downloads' ),
		'downloads'    => __( 'Downloads', 'alpha-downloads' ),
		'members_only' => '<span class="icon" title="' . __( 'Members Only', 'alpha-downloads' ) . '">' . __( 'Members Only', 'alpha-downloads' ) . '</span>',
		'open_browser' => '<span class="icon" title="' . __( 'Open in Browser', 'alpha-downloads' ) . '">' . __( 'Open in Browser', 'alpha-downloads' ) . '</span>',
		'date'         => __( 'Date', 'alpha-downloads' ),
	);

	// If taxonomies is enabled add to columns array
	if ( $alpha_options['enable_taxonomies'] ) {
		$columns_taxonomies = array(
			'taxonomy-ddownload_category' => __( 'Categories', 'alpha-downloads' ),
			'taxonomy-ddownload_tag'      => __( 'Tags', 'alpha-downloads' ),
		);

		// Splice and insert after shortcode column
		$spliced = array_splice( $columns, 4 );
		$columns = array_merge( $columns, $columns_taxonomies, $spliced );
	}

	return $columns;
}
add_filter( 'manage_alpha_download_posts_columns', 'alpha_download_column_headings' );

/**
 * Download Post Type Column Contents
 *
 * @since  1.0
 */
function alpha_download_column_contents( $column_name, $post_id ) {
	// File column
	if ( $column_name == 'file' ) {
		$file_url = get_post_meta( $post_id, '_alpha_file_url', true );
		$file_url = alpha_get_file_name( $file_url );
		echo ( ! $file_url ) ? '<span class="blank">--</span>' : esc_attr( $file_url );
	}

	// Filesize column
	if ( $column_name == 'filesize' ) {
		$file_size = get_post_meta( $post_id, '_alpha_file_size', true );
		$file_size = ( ! $file_size ) ? 0 : size_format( $file_size, 1 );
		echo ( ! $file_size ) ? '<span class="blank">--</span>' : esc_attr( $file_size );
	}

	// Shortcode column
	if ( $column_name == 'shortcode' ) {
		echo '<input type="text" class="copy-to-clipboard" value="[ddownload id=&quot;' . esc_attr( $post_id ) . '&quot;]" readonly>';
		echo '<p class="description" style="display: none;">' . __( 'Shortcode copied to clipboard.', 'alpha-downloads' ) . '</p>';
	}

	// Count column
	if ( $column_name == 'downloads' ) {
		$count = get_post_meta( $post_id, '_alpha_file_count', true );
		$count = ( ! $count ) ? 0 : number_format_i18n( $count );
		echo esc_attr( $count );
	}

	// Members only column
	if ( 'members_only' == $column_name ) {
		$file = get_post_meta( $post_id, '_alpha_file_options', true );

		if ( isset( $file['members_only'] ) ) {
			echo ( 1 == $file['members_only'] ) ? '<span class="true" title="' . __( 'Yes', 'alpha-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'alpha-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'alpha-downloads' ) . '">--</span>';
		}
	}

	// Open browser column
	if ( 'open_browser' == $column_name ) {
		$file = get_post_meta( $post_id, '_alpha_file_options', true );

		if ( isset( $file['open_browser'] ) ) {
			echo ( 1 == $file['open_browser'] ) ? '<span class="true" title="' . __( 'Yes', 'alpha-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'alpha-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'alpha-downloads' ) . '">--</span>';
		}
	}
}
add_action( 'manage_alpha_download_posts_custom_column', 'alpha_download_column_contents', 10, 2 );

/**
 * Download Post Type Sortable Filter
 *
 * @since  1.0
 */
function alpha_download_column_sortable( $columns ) {
	$columns['filesize']  = 'filesize';
	$columns['downloads'] = 'downloads';

	return $columns;
}
add_filter( 'manage_edit-alpha_download_sortable_columns', 'alpha_download_column_sortable' );

/**
 * Download Post Type Sortable Action
 *
 * @since  1.0
 */
function alpha_download_column_orderby( $query ) {
	$orderby = $query->get( 'orderby' );

	if ( $orderby == 'filesize' ) {
		$query->set( 'meta_key', '_alpha_file_size' );
		$query->set( 'orderby', 'meta_value_num' );
	}

	if ( $orderby == 'downloads' ) {
		$query->set( 'meta_key', '_alpha_file_count' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'alpha_download_column_orderby' );
