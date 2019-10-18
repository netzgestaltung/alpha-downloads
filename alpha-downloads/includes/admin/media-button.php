<?php
/**
 * Alpha Downloads Media Button
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Media Button
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Display Media Button
 *
 * @since  1.0
 */
function alpha_media_button( $context ) {
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		$context .= '<a href="#alpha-shortcode-modal" id="alpha-media-button" class="button alpha-modal-action add-download" data-editor="content" title="Add Download"><span class="wp-media-buttons-icon"></span>Add Download</a>';	
	}

	return $context;
}
add_filter( 'media_buttons_context', 'alpha_media_button' );

/**
 * Add Modal Window to Footer
 *
 * @since  1.0
 */
function alpha_media_modal() {
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		
		// Get published downloads
		$downloads = get_posts( array(
			'post_type'		=> 'alpha_download',
			'post_status'	=> 'publish',
			'orderby'		=> 'title',
			'order'			=> 'ASC',
			'posts_per_page'=> -1	
		) );

		// Get registered styles
		$styles = alpha_get_shortcode_styles();

		// Get registered buttons
		$buttons = alpha_get_shortcode_buttons();

		?>
			
			<div id="alpha-shortcode-modal" class="alpha-modal" style="display: none; width: 30%; left: 50%; margin-left: -15%;">
				<a href="#" class="alpha-modal-close" title="<?php _e( 'Close', 'alpha-downloads' ); ?>"><span class="media-modal-icon"></span></a>
				<div class="alpha-modal-content">
					<h1><?php _e( 'Insert Download', 'alpha-downloads' ); ?></h1>
							
					<?php if ( $downloads ) : ?>
						<p>
							<label><span><?php _e( 'Download', 'alpha-downloads' ); ?></span>
								<select id="alpha-select-download-dropdown">
									<?php foreach ( $downloads as $download ) : ?>
										<option value="<?php echo $download->ID; ?>"><?php echo $download->post_title; ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						</p>
						<p class="clear">
							<label id="alpha-style-dropdown-container" class="column-2"><span><?php _e( 'Style', 'alpha-downloads' ); ?></span>
								<select id="alpha-select-style-dropdown">
									<optgroup label="<?php _e( 'Global', 'alpha-downloads' ); ?>">
										<option value=""><?php _e( 'Inherit', 'alpha-downloads' ); ?></option>
									</optgroup>
									<optgroup label="<?php _e( 'Styles', 'alpha-downloads' ); ?>">
										<?php foreach ( $styles as $key => $value ) : ?>
											<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</label>

							<label id="alpha-button-dropdown-container" class="column-2"><span><?php _e( 'Button', 'alpha-downloads' ); ?></span>
								<select id="alpha-select-button-dropdown">
									<optgroup label="<?php _e( 'Global', 'alpha-downloads' ); ?>">
										<option value=""><?php _e( 'Inherit', 'alpha-downloads' ); ?></option>
									</optgroup>
									<optgroup label="<?php _e( 'Buttons', 'alpha-downloads' ); ?>">
										<?php foreach ( $buttons as $key => $value ) : ?>
											<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</label>
						</p>
						<p>
							<label><span><?php _e( 'Text', 'alpha-downloads' ); ?></span>	
								<input id="alpha-custom-text" type="text" placeholder="<?php _e( 'Inherit', 'alpha-downloads' ); ?>" />
							</label>
						</p>
						<p class="buttons clear">
							<a href="#" id="alpha-insert" class="button button-large button-primary"><?php _e( 'Insert', 'alpha-downloads' ); ?></a>
							<a href="#" id="alpha-file-size" class="button button-large right"><?php _e( 'File Size', 'alpha-downloads' ); ?></a>
							<a href="#" id="alpha-download-count" class="button button-large right"><?php _e( 'Download Count', 'alpha-downloads' ); ?></a>
						</p>
					<?php else: ?>
						<p><?php echo sprintf( __( 'Please %sadd%s a new download.', 'alpha-downloads' ), '<a href="' . admin_url( 'post-new.php?post_type=alpha_download' ) . '" target="_blank">', '</a>' ); ?></p>
					<?php endif; ?>

				</div>
			</div>

		<?php 
	}
}
add_action( 'admin_footer', 'alpha_media_modal', 100 );
