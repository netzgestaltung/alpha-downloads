<?php
/**
 * Alpha Downloads Taxonomies
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Taxonomies
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Download Taxonomies
 *
 * @since  1.3
 */
function alpha_download_taxonomies() {
	global $alpha_options;	

	// Register download category taxonomy
	$labels = array(
		'name'				=> __( 'Kategorien', 'alpha-downloads' ),
		'singular_name'		=> __( 'Kategorie', 'alpha-downloads' ),
		'menu_name'			=> __( 'Categories', 'alpha-downloads' ),
		'all_items'			=> __( 'All Categories', 'alpha-downloads' ),
		'edit_item'			=> __( 'Edit Category', 'alpha-downloads' ),
		'view_item'			=> __( 'View Category', 'alpha-downloads' ),
		'update_item'		=> __( 'Update Category', 'alpha-downloads' ),
		'add_new_item'		=> __( 'Add New Category', 'alpha-downloads' ),
		'new_item_name'		=> __( 'New Category Name', 'alpha-downloads' ),
		'search_items'		=> __( 'Search Categories', 'alpha-downloads' ),
		'popular_items'		=> __( 'Popular Categories', 'alpha-downloads' ) 
	);

	$category_args = array(
		'labels'			=> apply_filters( 'alpha_ddownload_category_labels', $labels ),
		'public'			=> true,
		'show_in_nav_menus'	=> false,
		'show_tag_cloud'	=> false,
		'show_admin_column'	=> true,
		'hierarchical'		=> true
	);

	// Register download tag taxonomy
	$labels = array(
		'name'				=> __( 'Themen', 'alpha-downloads' ),
		'singular_name'		=> __( 'Thema', 'alpha-downloads' ),
		'menu_name'			=> __( 'Tags', 'alpha-downloads' ),
		'all_items'			=> __( 'All Tags', 'alpha-downloads' ),
		'edit_item'			=> __( 'Edit Tag', 'alpha-downloads' ),
		'view_item'			=> __( 'View Tag', 'alpha-downloads' ),
		'update_item'		=> __( 'Update Tag', 'alpha-downloads' ),
		'add_new_item'		=> __( 'Add New Tag', 'alpha-downloads' ),
		'new_item_name'		=> __( 'New Tag Name', 'alpha-downloads' ),
		'search_items'		=> __( 'Search Tags', 'alpha-downloads' ),
		'popular_items'		=> __( 'Popular Tags', 'alpha-downloads' )
	);

	$tag_args = array(
		'labels'			=> apply_filters( 'alpha_ddownload_tag_labels', $labels ),
		'public'			=> true,
		'show_in_nav_menus'	=> true,
		'show_tag_cloud'	=> true,
		'show_admin_column'	=> true,
		'hierarchical'		=> false
	);

	// Only register if enabled in settings
	if ( $alpha_options['enable_taxonomies'] ) {
		register_taxonomy( 'ddownload_category', array( 'alpha_download' ), $category_args );
		register_taxonomy( 'ddownload_tag', array( 'alpha_download' ), $tag_args );
	}
}
add_action( 'init', 'alpha_download_taxonomies', 3 );
