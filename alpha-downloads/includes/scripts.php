<?php
/**
 * Alpha Downloads Scripts
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Scripts
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Frontend Scripts & Styles
 *
 * @since  1.0
 */
function alpha_enqueue_scripts( $page ) {
	global $alpha_options;

	// Enqueue frontend CSS if option is enabled
	if ( ! $alpha_options['enable_css'] ) {
		return;
	}

	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : ALPHA_VERSION;
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Register frontend CSS
	$src = ALPHA_PLUGIN_URL . 'assets/css/alpha-downloads' . $suffix . '.css';
	wp_enqueue_style( 'alpha-css', $src, array(), $version, 'all' );
}
add_action( 'wp_enqueue_scripts', 'alpha_enqueue_scripts' );

/**
 * Register Admin Scripts & Styles
 *
 * @since  1.0
 */
function alpha_admin_enqueue_scripts( $page ) {
	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : ALPHA_VERSION;
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Register scripts
	$src = ALPHA_PLUGIN_URL . 'assets/js/alpha-admin-global' . $suffix . '.js';
	wp_register_script( 'alpha-admin-js-global', $src, array( 'jquery' ), $version, true );

	$src = ALPHA_PLUGIN_URL . 'assets/js/alpha-admin-legacy-logs' . $suffix . '.js';
	wp_register_script( 'alpha-admin-js-legacy-logs', $src, array( 'jquery' ), $version, true ); // 1.4 upgrade

	$src = ALPHA_PLUGIN_URL . 'assets/js/alpha-admin-media-button' . $suffix . '.js';
	wp_register_script( 'alpha-admin-js-media-button', $src, array( 'jquery', 'alpha-jqueryChosen' ), $version, true );

	$src = ALPHA_PLUGIN_URL . 'assets/js/alpha-admin-post-download' . $suffix . '.js';
	wp_register_script( 'alpha-admin-js-post-download', $src, array(
		'jquery',
		'plupload-all',
		'jqueryFileTree'
	), $version, true );

	$src = ALPHA_PLUGIN_URL . 'assets/vendor/jqueryFileTree/jqueryFileTree.js';
	wp_register_script( 'jqueryFileTree', $src, array( 'jquery' ), $version, true );

	$src = ALPHA_PLUGIN_URL . 'assets/vendor/jqueryChosen/chosen.jquery' . $suffix . '.js';
	wp_register_script( 'alpha-jqueryChosen', $src, array( 'jquery' ), $version, true );

	$src = ALPHA_PLUGIN_URL . 'assets/vendor/Vue/vue' . $suffix . '.js';
	wp_register_script( 'alpha-vue', $src, array(), '1.0.10', true );

	// Register styles
	$src = ALPHA_PLUGIN_URL . 'assets/css/alpha-downloads-admin' . $suffix . '.css';
	wp_register_style( 'alpha-css-admin', $src, array(), $version, 'all' );
	$src = ALPHA_PLUGIN_URL . 'assets/vendor/jqueryFileTree/jqueryFileTree.css';
	wp_register_style( 'jqueryFileTree-css', $src, array(), $version, 'all' );
	$src = ALPHA_PLUGIN_URL . 'assets/vendor/jqueryChosen/chosen' . $suffix . '.css';
	wp_register_style( 'alpha-jqueryChosen-css', $src, array(), $version, 'all' );

	// Enqueue on all admin pages
	wp_enqueue_style( 'alpha-css-admin' );
	wp_enqueue_script( 'alpha-admin-js-global' );

	// JS copy to clipboard
	$src = ALPHA_PLUGIN_URL . 'assets/js/copy-to-clipboard' . $suffix . '.js';
	wp_enqueue_script( 'alpha-copy-to-clipboard', $src, array(
		'jquery',
	), $version, true );

	// Enqueue on alpha_download post add/edit screen
	if ( in_array( $page, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php'
		) ) && get_post_type() == 'alpha_download'
	) {
		wp_enqueue_script( 'alpha-admin-js-post-download' );
		wp_enqueue_style( 'jqueryFileTree-css' );
	}

	// Enqueue on all post/edit screen
	if ( in_array( $page, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
		wp_enqueue_script( 'alpha-admin-js-media-button' );
		wp_enqueue_style( 'alpha-jqueryChosen-css' );
	}
}

add_action( 'admin_enqueue_scripts', 'alpha_admin_enqueue_scripts' );
