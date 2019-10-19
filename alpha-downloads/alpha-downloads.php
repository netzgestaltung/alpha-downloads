<?php
/*
Plugin Name: Alpha Downloads
Plugin URI: https://netzgestaltung.at
Description: A super-awesome downloads manager for WordPress.
Version: 0.6.6
Author: Ashley Rich/Thomas Fellinger
Author URI: http://ashleyrich.com/https://netzgestaltung.at
Text Domain: alpha-downloads
Domain Path: /languages/
License: GPL2

Copyright 2013  Ashley Rich  (email : hello@ashleyrich.com)
Copyright 2019  Thomas Fellinger  (email : office@netzgestaltung.at)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Alpha Downloads
 *
 * @package  Alpha Downloads
 * @since    1.3.2
 */
class Alpha_Downloads {

	/**
	 * Instance of this class.
	 *
	 * @since  1.3.2
	 */
	private static $instance;

	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * As this class is a singleton it should not be clone-able
	 */
	protected function __clone() {
	}

	/**
	 * As this class is a singleton it should not be able to be unserialized
	 */
	protected function __wakeup() {
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since  1.3.2
	 */
	public static function get_instance( $path, $version ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Alpha_Downloads();

			// Initialize the class
			self::$instance->init( $path, $version );
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @param string $path
	 * @param string $version
	 */
	protected function init( $path, $version ) {
		$this->path    = $path;
		$this->version = $version;

		self::$instance->constants();
		self::$instance->textdomain();
		self::$instance->options();
		self::$instance->includes();

		// Register activation/deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Plugin row links
		add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );
	}


	/**
	 * Include all the classes used by the plugin
	 */
	protected function includes() {
		require_once dirname( $this->path ) . '/classes/class-addon.php';
		require_once dirname( $this->path ) . '/includes/class-alpha-cache.php';
		require_once dirname( $this->path ) . '/includes/class-alpha-logging.php';
		require_once dirname( $this->path ) . '/includes/class-alpha-statistics.php';
		require_once dirname( $this->path ) . '/includes/class-alpha-widget-list.php';
		require_once dirname( $this->path ) . '/includes/cron.php';
		require_once dirname( $this->path ) . '/includes/functions.php';
		require_once dirname( $this->path ) . '/includes/mime-types.php';
		require_once dirname( $this->path ) . '/includes/post-types.php';
		require_once dirname( $this->path ) . '/includes/process-download.php';
		require_once dirname( $this->path ) . '/includes/scripts.php';
		require_once dirname( $this->path ) . '/includes/shortcodes.php';
		require_once dirname( $this->path ) . '/includes/widgets.php';
		require_once dirname( $this->path ) . '/includes/taxonomies.php';

		if ( is_admin() ) {
			require_once dirname( $this->path ) . '/includes/admin/ajax.php';
			require_once dirname( $this->path ) . '/includes/admin/class-alpha-list-table.php';
			require_once dirname( $this->path ) . '/includes/admin/class-alpha-notices.php';
			require_once dirname( $this->path ) . '/includes/admin/dashboard.php';
			require_once dirname( $this->path ) . '/includes/admin/media-button.php';
			require_once dirname( $this->path ) . '/includes/admin/meta-boxes.php';
			require_once dirname( $this->path ) . '/includes/admin/page-addons.php';
			require_once dirname( $this->path ) . '/includes/admin/page-settings.php';
			require_once dirname( $this->path ) . '/includes/admin/page-statistics.php';
			require_once dirname( $this->path ) . '/includes/admin/upgrades.php';
		}
	}

	/**
	 * Setup class constants
	 */
	protected function constants() {
		if ( ! defined( 'ALPHA_VERSION' ) ) {
			define( 'ALPHA_VERSION', $this->version );
		}

		if ( ! defined( 'ALPHA_PLUGIN_URL' ) ) {
			define( 'ALPHA_PLUGIN_URL', plugin_dir_url( $this->path ) );
		}

		if ( ! defined( 'ALPHA_PLUGIN_DIR' ) ) {
			define( 'ALPHA_PLUGIN_DIR', plugin_dir_path( $this->path ) );
		}

		if ( ! defined( 'ALPHA_DOWNLOADS_API' ) ) {
			define( 'ALPHA_DOWNLOADS_API', 'https://delightfuldownloads.com' );
		}
	}

	/**
	 * Textdomain
	 */
	protected function textdomain() {
		load_textdomain( 'alpha-downloads', WP_LANG_DIR . '/alpha-downloads/alpha-downloads-' . get_locale() . '.mo' );
		load_plugin_textdomain( 'alpha-downloads', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Options
	 */
	protected function options() {
		global $alpha_options, $alpha_default_options;

		require_once dirname( $this->path ) . '/includes/options.php';

		// Set globals
		$alpha_default_options = alpha_get_default_options();
		$alpha_options         = wp_parse_args( get_option( 'alpha-downloads' ), $alpha_default_options );
	}

	/**
	 * Plugin Links.
	 *
	 * Add links below Alpha Downloads on the plugin screen.
	 *
	 * @param string $links
	 * @param string $file
	 *
	 * @return string
	 */
	public function plugin_links( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$plugin_links[] = '<a href="' . admin_url( 'edit.php?post_type=alpha_download&page=alpha_settings' ) . '">' . __( 'Settings', 'alpha-downloads' ) . '</a>';

			foreach ( $plugin_links as $plugin_link ) {
				array_unshift( $links, $plugin_link );
			}
		}

		return $links;
	}

	/**
	 * Activate plugin
	 */
	public function activate() {
		global $alpha_default_options, $alpha_statistics;

		// Install database table
		$alpha_statistics->setup_table();

		// Add version to database
		update_option( 'alpha-downloads-version', ALPHA_VERSION );

		// Add default options to database
		update_option( 'alpha-downloads', $alpha_default_options );

		// Add option for admin notices
		update_option( 'alpha-downloads-notices', array() );

		// Run folder protection
		alpha_folder_protection();
	}

	/**
	 * Deactivate plugin
	 */
	public function deactivate() {
		// Clear alpha transients
		alpha_delete_all_transients();
	}

	/**
	 * Render view
	 *
	 * @param string $view
	 * @param array  $args
	 */
	public function render_view( $view, $args = array() ) {
		extract( $args );
		include dirname( $this->path ) . '/views/' . $view . '.php';
	}

}

/**
 * Alpha Downloads
 *
 * @return Alpha_Downloads
 */
function Alpha_Downloads() {
	$version = '1.6.6';

	return Alpha_Downloads::get_instance( __FILE__, $version );
}
Alpha_Downloads();
