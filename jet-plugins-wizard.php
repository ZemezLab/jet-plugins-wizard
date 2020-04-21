<?php
/**
 * Plugin Name: Jet Plugins Wizard
 * Plugin URI:  https://zemez.io
 * Description: Plugins installation wizard.
 * Version:     1.2.8
 * Author:      Zemez
 * Author URI:
 * Text Domain: jet-plugins-wizard
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Plugins_Wizard' ) ) {

	/**
	 * Define Jet_Plugins_Wizard class
	 */
	class Jet_Plugins_Wizard {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Plugin base url
		 *
		 * @var string
		 */
		private $url = null;

		/**
		 * Plugin base path
		 *
		 * @var string
		 */
		private $path = null;

		/**
		 * Menu page slug.
		 * @var string
		 */
		private $slug = 'jet-plugins-wizard';

		/**
		 * Dispalying tab data
		 * @var array
		 */
		public $current_tab = array();

		/**
		 * Nonce action name.
		 * @var string
		 */
		public $_nonce = 'jet-plugins-wizard-nonce';

		/**
		 * Notice visibility trigger
		 * @var  null|boolean
		 */
		public $is_notice_visible = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {

			if ( ! is_admin() ) {
				return;
			}

			add_action( 'after_setup_theme',     array( $this, 'preload' ), 0 );
			add_action( 'plugins_loaded',        array( $this, 'lang' ), 2 );
			add_action( 'init',                  array( $this, 'init' ) );
			add_action( 'admin_init',            array( $this, 'dismiss_notice' ) );
			add_action( 'admin_head',            array( $this, 'notice_css' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'register_assets' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

			register_activation_hook( __FILE__,   array( $this, '_activation' ) );
			register_deactivation_hook( __FILE__, array( $this, '_deactivation' ) );

		}

		/**
		 * Preload required files.
		 *
		 * @return void
		 */
		public function preload() {
			require_once $this->path( 'includes/class-jet-plugins-wizard-settings.php' );
			require_once $this->path( 'includes/class-jet-plugins-wizard-license.php' );
			require_once $this->path( 'includes/class-jet-plugins-wizard-data.php' );
			require_once $this->path( 'includes/class-jet-plugins-wizard-extensions.php' );
		}

		/**
		 * Create wizard nonce
		 *
		 * @return void
		 */
		public function nonce() {
			wp_create_nonce( $this->_nonce );
		}

		/**
		 * Verify nonce
		 *
		 * @param  string $nonce Nonce value.
		 * @return bool
		 */
		public function verify_nonce( $nonce ) {
			return wp_verify_nonce( $nonce, $this->_nonce );
		}

		/**
		 * Do stuff on the wiard activation.
		 *
		 * @return void
		 */
		public function _activation() {
			add_option( 'jet_plugins_wizard_show_notice', 1 );
		}

		/**
		 * Do stuff on wizard deactivation
		 *
		 * @return void
		 */
		public function _deactivation() {
			delete_option( 'jet_plugins_wizard_show_notice' );
		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 */
		public function lang() {
			load_plugin_textdomain( 'jet-plugins-wizard', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Init plugin
		 *
		 * @return void
		 */
		public function init() {

			add_action( 'admin_notices', array( $this, 'wizard_notice' ) );

			jet_plugins_wizard_license();

			$this->load();
		}

		/**
		 * Check if wizard notice is visible
		 *
		 * @return boolean
		 */
		public function is_notice_visible() {

			if ( $this->is_wizard() ) {
				return false;
			}

			if ( null === $this->is_notice_visible ) {
				$this->is_notice_visible = get_option( 'jet_plugins_wizard_show_notice' );
			}

			return apply_filters( 'jet-plugins-wizard/notice-visibility', $this->is_notice_visible );
		}

		/**
		 * Show wizard notice.
		 *
		 * @return null
		 */
		public function wizard_notice() {

			if ( ! $this->is_notice_visible() ) {
				return;
			}

			$this->get_template( 'wizard-notice.php' );
		}

		/**
		 * Dismiss wizard notice.
		 *
		 * @return void
		 */
		public function dismiss_notice() {

			if ( ! isset( $_GET['jet_plugins_wizard_dismiss'] ) ) {
				return;
			}

			delete_option( 'jet_plugins_wizard_show_notice' );
		}

		/**
		 * Get plugin page URL.
		 *
		 * @param  array $args Arguments array.
		 * @return string
		 */
		public function get_page_link( $args = array() ) {

			$args['page'] = $this->slug();

			if ( isset( $_GET['advanced-install'] ) ) {
				$args['advanced-install'] = esc_attr( $_GET['advanced-install'] );
			}

			foreach ( array( 'skin', 'type' ) as $key ) {
				if ( isset( $_GET[ $key ] ) && empty( $args[ $key ] ) ) {
					$args[ $key ] = esc_attr( $_GET[ $key ] );
				}
			}

			return add_query_arg( $args, esc_url( admin_url( 'admin.php' ) ) );
		}

		/**
		 * Get plugin template
		 *
		 * @param  string $template Template name.
		 * @param  mixed  $data     Additional data to pass into template
		 * @return void
		 */
		public function get_template( $template, $data = false ) {

			$file = locate_template( 'jet-plugins-wizard/' . $template );

			if ( ! $file ) {
				$file = $this->path( 'templates/' . $template );
			}

			$file = apply_filters( 'jet-plugins-wizard/template-path', $file, $template );

			if ( file_exists( $file ) ) {
				include $file;
			}

		}

		/**
		 * Load globally required files
		 */
		public function load() {
			require_once $this->path( 'includes/class-jet-plugins-wizard-interface.php' );
			require_once $this->path( 'includes/class-jet-plugins-wizard-installer.php' );
		}

		/**
		 * Returns plugin slug
		 *
		 * @return string
		 */
		public function slug() {
			return $this->slug;
		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function path( $path = null ) {

			if ( ! $this->path ) {
				$this->path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->path . $path;

		}

		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function url( $path = null ) {

			if ( ! $this->url ) {
				$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->url . $path;

		}

		/**
		 * Show wizard notice CSS
		 *
		 * @return null
		 */
		public function notice_css() {

			if ( ! $this->is_notice_visible() ) {
				return;
			}

			if ( ! $this->is_wizard() ) {
				$this->print_inline_css( 'notice.css' );
				return;
			}

		}

		/**
		 * Print inline CSS file
		 *
		 * @param  string $file File name.
		 * @return void
		 */
		public function print_inline_css( $file ) {

			$css = $this->path( 'assets/css/' . $file );

			if ( ! file_exists( $css ) ) {
				return;
			}

			ob_start();
			include $css;
			$notice = ob_get_clean();
			printf( '<style type="text/css">%s</style>', $notice );
		}

		/**
		 * Register plugin assets
		 *
		 * @return void
		 */
		public function register_assets() {

			$handle = 'jet-plugins-wizard';

			wp_register_script(
				$handle,
				$this->url( 'assets/js/jet-plugins-wizard.js' ),
				array( 'wp-util' ),
				'1.2.1',
				true
			);

			wp_register_script(
				$handle . '-dashboard',
				$this->url( 'assets/js/jet-plugins-wizard-dashboard.js' ),
				array( 'jquery' ),
				'1.2.1',
				true
			);

			wp_register_style(
				$handle,
				$this->url( 'assets/css/jet-plugins-wizard.css' ),
				false,
				'1.2.1'
			);
		}

		/**
		 * Enqueue required assets
		 *
		 * @return void
		 */
		public function enqueue_assets( $hook ) {

			if ( ! $this->is_wizard() ) {
				return;
			}

			$handle   = 'jet-plugins-wizard';
			$settings = array(
				'redirect' => true,
			);

			if ( $this->is_wizard( 3 ) ) {

				$skin = ! empty( $_GET['skin'] )
					? jet_plugins_wizard_data()->sanitize_skin( $_GET['skin'] )
					: jet_plugins_wizard_data()->default_skin();

				$type = ! empty( $_GET['type'] )
					? jet_plugins_wizard_data()->sanitize_type( $_GET['type'] )
					: jet_plugins_wizard_data()->default_type();

				$settings['firstPlugin']  = jet_plugins_wizard_data()->get_first_plugin_data( $skin, $type );
				$settings['totalPlugins'] = jet_plugins_wizard_data()->get_plugins_count( $skin, $type );
			}

			wp_enqueue_script( $handle );
			wp_enqueue_style( $handle );

			/**
			 * Hook fires on wizard assets enqueue
			 */
			do_action( 'jet-plugins-wizard/enqueue-assets' );

			wp_localize_script(
				$handle,
				'JetWizardSettings',
				apply_filters( 'jet-plugins-wizard/js-settings', $settings )
			);
		}

		/**
		 * Check if is wizard-related page.
		 *
		 * @param  bool|int $step Current step.
		 * @return bool
		 */
		public function is_wizard( $step = false ) {

			if ( ! isset( $_GET['page'] ) || $this->slug() !== $_GET['page'] ) {
				return false;
			}

			if ( false === $step ) {
				return true;
			}

			if ( empty( $_GET['step'] ) ) {
				$current = 1;
			} else {
				$current = intval( $_GET['step'] );
			}

			if ( $step === $current ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of Jet_Plugins_Wizard
 *
 * @return object
 */
function jet_plugins_wizard() {
	return Jet_Plugins_Wizard::get_instance();
}

jet_plugins_wizard();

/**
 * Register new 3rd-party configuration.
 * Should be called on 'init' hook with priority 9 or earlier.
 *
 * @param  array  $config [description]
 * @return [type]         [description]
 */
function jet_plugins_wizard_register_config( $config = array() ) {
	jet_plugins_wizard_settings()->register_external_config( $config );
}
