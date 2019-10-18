<?php
/**
 * Alpha Downloads Page Addons
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Page Addons
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Addons Page
 */
function alpha_register_page_addons() {
	add_submenu_page( 'edit.php?post_type=alpha_download', __( 'Download Add-Ons', 'alpha-downloads' ), __( 'Add-Ons', 'alpha-downloads' ), 'manage_options', 'alpha_addons', 'alpha_render_page_addons' );
}
add_action( 'admin_menu', 'alpha_register_page_addons', 40 );

/**
 * Render page addons
 */
function alpha_render_page_addons() {
	if ( false === ( $addons = get_site_transient( 'alpha_addons' ) ) ) {
		$response = wp_remote_get( trailingslashit( DELIGHTFUL_DOWNLOADS_API ) . 'wp-json/add-ons/v1/all/' );
		$response = wp_remote_retrieve_body( $response );
		$addons   = json_decode( $response );

		if ( is_array( $addons ) && isset( $addons[0]->title ) ) {
			set_site_transient( 'alpha_addons', $addons, HOUR_IN_SECONDS );
		}
	}

	Alpha_Downloads()->render_view( 'addons', array( 'addons' => $addons ) );
}
