<?php
/**
 * Alpha Downloads Widgets
 *
 * @package     Alpha Downloads
 * @subpackage  Includes/Widgets
 * @since       1.6
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widgets
 *
 * @since 1.6
 */
function alpha_widgets() {	
	register_widget( 'ALPHA_Widget_List' );
}
add_action( 'widgets_init', 'alpha_widgets', 5 );
