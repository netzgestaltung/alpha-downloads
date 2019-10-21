<?php
/**
 * Alpha Downloads Page Settings
 *
 * @package     Alpha Downloads
 * @subpackage  Admin/Page Settings
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Settings Page
 *
 * @since  1.3
 */
function alpha_register_page_settings() {
	add_submenu_page( 'edit.php?post_type=alpha_download', __( 'Download Settings', 'alpha-downloads' ), __( 'Settings', 'alpha-downloads' ), 'manage_options', 'alpha_settings', 'alpha_render_page_settings' );
}
add_action( 'admin_menu', 'alpha_register_page_settings', 30 );

/**
 * Register Settings Sections and Fields
 *
 * @since  1.3
 */
function alpha_register_settings() {
	
	// Get registered tabs and settings
	$registered_tabs = alpha_get_tabs();
	$registered_settings = alpha_get_options();

	// Register whitelist
	register_setting( 'alpha_settings', 'alpha-downloads', 'alpha_validate_settings' ); 

	// Register form sections
	foreach ( $registered_tabs as $key => $value ) {
		
		add_settings_section(
			'alpha_settings_' . $key,
			'',
			function_exists( 'alpha_settings_' . $key . '_section' ) ? 'alpha_settings_' . $key . '_section' : 'alpha_settings_section',
			'alpha_settings_' . $key
		);

	}
	
	// Register form fields
	foreach ( $registered_settings as $key => $value ) {
		$callback = 'alpha_settings_' . $key . '_field';

		if ( ! empty( $value['callback'] ) ) {
			$callback = $value['callback'];
		}

		add_settings_field(
			$key,
			$value['name'],
			$callback,
			'alpha_settings_' . $value['tab'],
			'alpha_settings_' . $value['tab']
		);

	}
} 
add_action( 'admin_init', 'alpha_register_settings' );

/**
 * Render Settings Page
 *
 * @since  1.3
 */
function alpha_render_page_settings() {
	
	// Get registered tabs
	$registered_tabs = alpha_get_tabs();

	// Get current tab
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; 
	?>

	<div class="wrap">
		
		<h1><?php _e( 'Download Settings', 'alpha-downloads' ); ?>
			<a href="#alpha-settings-import" class="add-new-h2 alpha-modal-action"><?php _e( 'Import', 'alpha-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings&action=export' ), 'alpha_export_settings', 'alpha_export_settings_nonce' ) ?>" class="add-new-h2"><?php _e( 'Export', 'alpha-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings&action=reset_defaults' ), 'alpha_reset_settings', 'alpha_reset_settings_nonce' ) ?>" class="add-new-h2 alpha_confirm_action" data-confirm="<?php _e( 'You are about to reset the download settings.', 'alpha-downloads' ); ?>"><?php _e( 'Reset Defaults', 'alpha-downloads' ); ?></a>
		</h1>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice updated is-dismissible"><p>' . __( 'Settings updated successfully.', 'alpha-downloads' ) . '</p></div>';
		} ?>

		<h2 id="alpha-settings-tabs" class="nav-tab-wrapper">
			
			<?php // Generate tabs
			
			foreach ( $registered_tabs as $key => $value ) {
				
				echo '<a href="#alpha-settings-tab-' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} ?>

		</h2>

		<div id="alpha-settings-main" <?php echo ( !apply_filters( 'alpha_admin_sidebar', true ) ) ? 'style="float: none; width: 100%; padding-right: 0;"' : ''; ?>>	

			<form action="options.php" method="post">	
				<?php // Setup fields
				settings_fields( 'alpha_settings' );

				// Display correct fields
				foreach ( $registered_tabs as $key => $value ) {
					$active_class = ( $key === $active_tab ) ? 'active' : '';
					?>

					<section id="alpha-settings-tab-<?php echo $key; ?>" class="alpha-settings-tab <?php echo $active_class; ?>" style="<?php echo ( '' === $active_class ) ? 'display: none;' : ''; ?>">
						<?php 

						if ( 'support' === $key ) {
							alpha_render_part_support();
						}
						else {
							do_settings_sections( 'alpha_settings_' . $key );
						}

						?>
					</section>

					<?php
				}
				
				// Submit button
				submit_button(); ?>
			</form>
	
		</div>

	</div>
	
	<?php
}

/**
 * Render Support Section
 *
 * @since  1.5
 */
function alpha_render_part_support() {

	global $wpdb, $alpha_options;

	// Get current theme data
	$theme = wp_get_theme();

	// Get active plugins
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	// Prior version
	$prior_version = get_option( 'alpha-downloads-prior-version' );
	?>

	<textarea id="alpha_support" readonly>

## Server Information ##

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
PHP Version: <?php echo PHP_VERSION . "\n"; ?>
MySQL Version: <?php echo $wpdb->db_version() . "\n"; ?>

PHP Safe Mode: <?php echo ini_get( 'safe_mode' ) ? "Yes\n" : "No\n"; ?>
PHP Memory Limit: <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Time Limit: <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Post Size: <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Max Upload Size: <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>


## WordPress Information ##

WordPress Version: <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Multisite: <?php echo ( is_multisite() ) ? 'Yes' . "\n" : 'No' . "\n" ?>
Max Upload Size: <?php echo size_format( wp_max_upload_size(), 1 ) . "\n"; ?>

Site Address: <?php echo home_url() . "\n"; ?>
WordPress Address: <?php echo site_url() . "\n"; ?>
Download Address: <?php echo alpha_download_link( 1 ) . "\n"; ?>

<?php echo ( defined('UPLOADS') ? 'Upload Directory: ' . UPLOADS . "\n" : '' ); ?>
Directory (wp-content): <?php echo ( defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . "\n" : '' ); ?>
URL (wp-content): <?php echo ( defined('WP_CONTENT_URL') ? WP_CONTENT_URL . "\n" : '' ); ?>

## Active Theme ## 

<?php echo $theme->Name . " " . $theme->Version . "\n"; ?>


## Active Plugins ##			

<?php 
foreach ( $plugins as $key => $value ) {
	
	if ( in_array( $key, $active_plugins ) ) {
		echo $value['Name'] . ' ' . $value['Version'] . "\n";
	}
	
}
?>


## Alpha Downloads Information ##

Version: <?php echo ALPHA_VERSION . "\n"; ?>
Prior Version: <?php echo $prior_version . "\n"; ?>

<?php

foreach ( $alpha_options as $key => $value ) {
	echo $key . ": " . $value . "\n";
}

?>
	</textarea>

	<?php

}


/**
 * Render Settings Sections
 *
 * @since  1.3
 */
function alpha_settings_section() { return; }

/**
 * Render enable taxonomies field
 *
 * @since  1.3
 */
function alpha_settings_enable_taxonomies_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['enable_taxonomies'] ); 
	?>
	
	<label for="enable_taxonomies_true"><input name="alpha-downloads[enable_taxonomies]" id="enable_taxonomies_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="enable_taxonomies_false"><input name="alpha-downloads[enable_taxonomies]" id="enable_taxonomies_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow downloads to be tagged or categorised.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render members only field
 *
 * @since  1.3
 */
function alpha_settings_members_only_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['members_only'] );
	?>

	<label for="members_only_true"><input name="alpha-downloads[members_only]" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="members_only_false"><input name="alpha-downloads[members_only]" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow only logged in users to download files. This can be overridden on a per-download basis.', 'alpha-downloads' ); ?></p>
	<?php
	// Default selected item
	$selected = $alpha_options['members_only_redirect'];
	
	// Output select input
	$args = array(
		'name'						=> 'alpha-downloads[members_only_redirect]',
		'selected'					=> $selected,
		'show_option_none'			=> __( 'No Page (Generic Error)', 'alpha-downloads' ),
		'option_none_value'			=> 0
	); ?>
	
	<div class="alpha-sub-option">
		<?php wp_dropdown_pages( $args ); ?>
		<p class="description"><?php _e( 'The page to redirect non-members. If no page is selected, a generic error message will be displayed. This can be overridden on a per-download basis.', 'alpha-downloads' ); ?></p>

	</div>
	<?php
}

/**
 * Render open in browser field
 *
 * @since  1.5
 */
function alpha_settings_open_browser_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['open_browser'] );
	?>

	<label for="open_browser_true"><input name="alpha-downloads[open_browser]" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="open_browser_false"><input name="alpha-downloads[open_browser]" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Attempt to open files in the browser window. This can be overridden on a per-download basis. For files located within the Alpha Downloads upload directory, set folder protection to \'No\', which can be found under the advanced tab.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render use template field
 *
 * @since  alpha 0.6.7
 */
function alpha_settings_use_template_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['use_template'] );
	?>

	<label for="use_template_true"><input name="alpha-downloads[use_template]" id="use_template_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="use_template_false"><input name="alpha-downloads[use_template]" id="use_template_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'To use your own template copy /templates/single-alpha_downloads.php to your themes or child themes folder. Start editing.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render block user agents field
 *
 * @since  1.3
 */
function alpha_settings_block_agents_field() {
	global $alpha_options;

	$agents = $alpha_options['block_agents'];

	echo '<textarea name="alpha-downloads[block_agents]" class="alpha-settings-textarea">' . esc_attr( $agents ) . '</textarea>';
	echo '<p class="description">' . __( 'User agents to block from downloading files. One per line.', 'alpha-downloads' ) . '</p>';
}

/**
 * Render default text field
 *
 * @since  1.3
 */
function alpha_settings_default_text_field() {
	global $alpha_options;

	$text = $alpha_options['default_text'];

	echo '<input type="text" name="alpha-downloads[default_text]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . sprintf( __( 'The default text to display, when using the %s shortcode. This can be overridden on a per-download basis.', 'alpha-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default style field
 *
 * @since  1.3
 */
function alpha_settings_default_style_field() {
	global $alpha_options;

	$styles        = alpha_get_shortcode_styles();
	$default_style = $alpha_options['default_style'];
	$disabled      = empty( $styles ) ? 'disabled' : '';

	echo '<select name="alpha-downloads[default_style]" ' . $disabled . '>';

	if ( ! empty( $styles ) ) {
		foreach ( $styles as $key => $value ) {
			$selected = ( $default_style == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No styles registered', 'alpha-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-download basis.', 'alpha-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default button field
 *
 * @since  1.3
 */
function alpha_settings_default_button_field() {
	global $alpha_options;

	$colors        = alpha_get_shortcode_buttons();
	$default_color = $alpha_options['default_button'];
	$disabled      = empty( $colors ) ? 'disabled' : '';

	echo '<select name="alpha-downloads[default_button]" ' . $disabled . '>';

	if ( ! empty( $colors ) ) {
		foreach ( $colors as $key => $value ) {
			$selected = ( $default_color == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No button styles registered', 'alpha-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default button style, when using the %s shortcode. This can be overridden on a per-download basis.', 'alpha-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default list field
 *
 * @since  1.3
 */
function alpha_settings_default_list_field() {
	global $alpha_options;

	$lists        = alpha_get_shortcode_lists();
	$default_list = $alpha_options['default_list'];
	$disabled     = empty( $lists ) ? 'disabled' : '';

	echo '<select name="alpha-downloads[default_list]" ' . $disabled . '>';

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $key => $value ) {
			$selected = ( $default_list == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No list styles registered', 'alpha-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-list basis.', 'alpha-downloads' ), '<code>[ddownload_list]</code>' );
}

/**
 * Render log admin downloads field
 *
 * @since  1.3
 */
function alpha_settings_log_admin_downloads_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['log_admin_downloads'] );
	?>
	
	<label for="log_admin_downloads_true"><input name="alpha-downloads[log_admin_downloads]" id="log_admin_downloads_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="log_admin_downloads_false"><input name="alpha-downloads[log_admin_downloads]" id="log_admin_downloads_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Log events triggered by admin users.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render grace period field
 *
 * @since  1.4
 */
function alpha_settings_grace_period_field() {
	global $alpha_options;
	$grace_period = $alpha_options['grace_period'];
	$duration = $alpha_options['grace_period_duration'];
	?>
	
	<label for="grace_period_toggle_true"><input name="alpha-downloads[grace_period]" id="grace_period_toggle_true" type="radio" value="1" <?php echo ( 1 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="grace_period_toggle_false"><input name="alpha-downloads[grace_period]" id="grace_period_toggle_false" type="radio" value="0" <?php echo ( 0 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop multiple logs of the same type from being saved, in quick succession.', 'alpha-downloads' ); ?></p>
	<div id="grace_period_sub" class="alpha-sub-option" style="<?php echo ( $grace_period == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="alpha-downloads[grace_period_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes before creating a new log.', 'alpha-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render auto delete field
 *
 * @since  1.4
 */
function alpha_settings_auto_delete_field() {
	global $alpha_options;
	$auto_delete = $alpha_options['auto_delete'];
	$duration = $alpha_options['auto_delete_duration'];
	?>
	
	<label for="auto_delete_toggle_true"><input name="alpha-downloads[auto_delete]" id="auto_delete_toggle_true" type="radio" value="1" <?php echo ( 1 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="auto_delete_toggle_false"><input name="alpha-downloads[auto_delete]" id="auto_delete_toggle_false" type="radio" value="0" <?php echo ( 0 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Automatically delete old logs.', 'alpha-downloads' ); ?></p>
	<div id="auto_delete_sub" class="alpha-sub-option" style="<?php echo ( $auto_delete == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="alpha-downloads[auto_delete_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in days to keep logs.', 'alpha-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render enable css field
 *
 * @since  1.3
 */
function alpha_settings_enable_css_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['enable_css'] );
	?>

	<label for="enable_css_true"><input name="alpha-downloads[enable_css]" id="enable_css_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="enable_css_false"><input name="alpha-downloads[enable_css]" id="enable_css_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Output the Alpha Downloads stylesheet on the front-end.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render cache duration field
 *
 * @since  1.3
 */
function alpha_settings_cache_field() {
	global $alpha_options;
	$cache = $alpha_options['cache'];
	$duration = $alpha_options['cache_duration'];
	?>

	<label for="cache_toggle_true"><input name="alpha-downloads[cache]" id="cache_toggle_true" type="radio" value="1" <?php echo ( 1 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="cache_toggle_false"><input name="alpha-downloads[cache]" id="cache_toggle_false" type="radio" value="0" <?php echo ( 0 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Cache database queries that are expensive to generate.', 'alpha-downloads' ); ?></p>
	<div id="cache_sub" class="alpha-sub-option" style="<?php echo ( $cache == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="alpha-downloads[cache_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes to cache queries.', 'alpha-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render Download Address field
 *
 * @since  1.3
 */
function alpha_settings_download_url_field() {
	global $alpha_options;

	$text = $alpha_options['download_url'];

	echo '<input type="text" name="alpha-downloads[download_url]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'The URL for download links.', 'alpha-downloads' ) . ' <code>' . alpha_download_link( 123 ) . '</code></p>';
}

/**
 * Render Upload Directory field
 */
function alpha_settings_upload_directory_field() {
	global $alpha_options;

	$text = $alpha_options['upload_directory'];

	echo '<input type="text" name="alpha-downloads[upload_directory]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'The directory to upload files.', 'alpha-downloads' ) . ' <code>' . trailingslashit( alpha_get_upload_dir( 'alpha_baseurl' ) ) . '</code></p>';
}

/**
 * Render Folder Protection field
 *
 * @since  1.5
 */
function alpha_settings_folder_protection_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['folder_protection'] );
	?>

	<label for="folder_protection_true"><input name="alpha-downloads[folder_protection]" id="folder_protection_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="folder_protection_false"><input name="alpha-downloads[folder_protection]" id="folder_protection_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop direct access to uploaded files, within the Alpha Downloads upload directory.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Render Uninstall field
 *
 * @since  1.3.6
 */
function alpha_settings_uninstall_field() {
	global $alpha_options;
	$checked = absint( $alpha_options['uninstall'] );
	?>

	<label for="uninstall_true"><input name="alpha-downloads[uninstall]" id="uninstall_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'alpha-downloads' ); ?></label>
	<label for="uninstall_false"><input name="alpha-downloads[uninstall]" id="uninstall_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'alpha-downloads' ); ?></label>
	<p class="description"><?php _e( 'Completely remove all data associated with Alpha Downloads, when uninstalling the plugin. All downloads, categories, tags and statistics will be removed.', 'alpha-downloads' ); ?></p>
	<?php
}

/**
 * Validate settings callback
 *
 * @since  1.3
 */
function alpha_validate_settings( $input ) {
	global $alpha_options;

	// Registered options
	$options              = alpha_get_options();
	$alpha_default_options = alpha_get_default_options();

	// Ensure text fields are not blank
	foreach( $options as $key => $value ) {
		if ( 'text' !== $options[ $key ]['type'] ) {
			continue;
		}

		// None empty text fields
		if ( 'licenses' !== $options[ $key ]['tab'] && '' === trim( $input[ $key ] ) ) {
			$input[ $key ] = $alpha_default_options[ $key ];
		}
	}
	 
	// Ensure download URL does not contain illegal characters
	$input['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['download_url'] ) );

	// Ensure upload directory does not contain illegal characters
	$input['upload_directory'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['upload_directory'] ) );

	// Run folder protection if option changed
	if ( $input['folder_protection'] != $alpha_options['folder_protection'] ) {
		alpha_folder_protection( $input['folder_protection'] );
	}
	
	// Clear transients
	alpha_delete_all_transients();

	return apply_filters( 'alpha_validate_settings', $input );
}

/**
 * Render Import Modal
 *
 * @since  1.5
 */
function alpha_render_part_import() {

	// Ensure only added on settings screen	
	$screen = get_current_screen();

	if ( 'alpha_download_page_alpha_settings' !== $screen->id ) {

		return;
	}

	?>

	<div id="alpha-settings-import" class="alpha-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="alpha-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Import Settings', 'alpha-downloads' ); ?></h1>
			<p><?php _e( 'Select a Alpha Downloads settings file to import:', 'alpha-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings&action=import' ); ?>">
				<p><input type="file" name="json_file"/></p>
				<p>
					<?php wp_nonce_field( 'alpha_import_settings','alpha_import_settings_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Import', 'alpha-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'alpha_render_part_import' );

/**
 * Settings Page Actions
 *
 * @since  1.4
 */
function alpha_settings_actions() {

	//Only perform on settings page, when form not submitted
	if ( isset( $_GET['page'] ) && 'alpha_settings' == $_GET['page'] ) {

		// Import
		if( isset( $_GET['action'] ) && 'import' == $_GET['action'] ) {

			alpha_settings_actions_import();
		}
		// Export
		else if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {

			alpha_settings_actions_export();
		}
		// Reset default settings
		else if( isset( $_GET['action'] ) && 'reset_defaults' == $_GET['action'] ) {
			
			alpha_settings_actions_reset();
		}

	}
}
add_action( 'init', 'alpha_settings_actions', 0 );

/**
 * Settings Page Actions Import
 *
 * @since  1.5
 */
function alpha_settings_actions_import() {

	global $alpha_notices;

	// Verfiy nonce
	check_admin_referer( 'alpha_import_settings', 'alpha_import_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	// Check file is uploaded
	if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['size'] > 0 ) {

		// Check file extension
		if ( 'json' !== alpha_get_file_ext( $_FILES['json_file']['name'] ) ) {

			$alpha_notices->add( 'error', __( 'Invalid settings file.', 'alpha-downloads' ) );

			return;
		}

		// Import and display success
		$import = json_decode( file_get_contents( $_FILES['json_file']['tmp_name'] ), true );

		update_option( 'alpha-downloads', $import );

		$alpha_notices->add( 'updated', __( 'Settings have been successfully imported.', 'alpha-downloads' ) );

		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings' ) );
		exit();	
	}
	else {

		$alpha_notices->add( 'error', __( 'No file uploaded.', 'alpha-downloads' ) );

		return;
	}
}

/**
 * Settings Page Actions Export
 *
 * @since  1.5
 */
function alpha_settings_actions_export() {

	global $alpha_options;

	// Verfiy nonce
	check_admin_referer( 'alpha_export_settings', 'alpha_export_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	// Set filename
	$filename = 'alpha-downloads-' . date( 'Ymd' ) . '.json';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	echo json_encode( $alpha_options );	

	die();
}

/**
 * Settings Page Actions Reset
 *
 * @since  1.5
 */
function alpha_settings_actions_reset() {

	global $alpha_default_options, $alpha_notices;

	// Verfiy nonce
	check_admin_referer( 'alpha_reset_settings', 'alpha_reset_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	delete_option( 'alpha-downloads' );
	add_option( 'alpha-downloads', $alpha_default_options );

	// Add success notice
	$alpha_notices->add( 'updated', __( 'Default settings reset successfully.', 'alpha-downloads' ) );

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings' ) );
	exit();	
}
