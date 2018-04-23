<?php
/**
 * Plugins installation manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Plugins_Wizard_Interface' ) ) {

	/**
	 * Define Jet_Plugins_Wizard_Interface class
	 */
	class Jet_Plugins_Wizard_Interface {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Holder for skins list.
		 *
		 * @var array
		 */
		private $skins = null;

		/**
		 * Holder for current skin data.
		 *
		 * @var array
		 */
		private $skin = null;

		/**
		 * Constructor for the class
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'admin_footer', array( $this, 'item_template' ) );
			add_filter( 'jet-data-importer/tabs-menu-visibility', array( $this, 'import_tabs_visibility' ) );
		}

		/**
		 * Disable tabs on import page if we came from wizard.
		 *
		 * @param  bool $is_visible Default visibility.
		 * @return bool
		 */
		public function import_tabs_visibility( $is_visible = true ) {

			if ( ! empty( $_GET['referrer'] ) && 'jet-plugins-wizard' === $_GET['referrer'] ) {
				return false;
			}

			return $is_visible;
		}

		/**
		 * Register wizard page
		 *
		 * @return void
		 */
		public function menu_page() {

			add_menu_page(
				esc_html__( 'Plugins Installation Wizard', 'jet-plugins-wizard' ),
				esc_html__( 'Plugins Wizard', 'jet-plugins-wizard' ),
				'manage_options',
				jet_plugins_wizard()->slug(),
				array( $this, 'render_plugin_page' ),
				'dashicons-flag',
				75
			);

		}

		/**
		 * Render plugin page
		 *
		 * @return void
		 */
		public function render_plugin_page() {

			jet_plugins_wizard()->get_template( 'page-header.php' );
			$this->dispatch();
			jet_plugins_wizard()->get_template( 'page-footer.php' );
		}

		/**
		 * Print JS item template
		 *
		 * @return void
		 */
		public function item_template() {

			if ( empty( $_GET['page'] ) || jet_plugins_wizard()->slug() !== $_GET['page'] ) {
				return;
			}

			printf(
				'<script type="text/html" id="tmpl-wizard-item">%1$s</script>',
				$this->get_item( '{{{data.slug}}}', '{{{data.name}}}' )
			);

		}

		/**
		 * Get plugin installation notice
		 *
		 * @param  string $slug Plugin slug.
		 * @param  string $name Plugin name.
		 * @return string
		 */
		public function get_item( $slug, $name ) {

			ob_start();
			$wizard_item = jet_plugins_wizard()->get_template( 'plugin-item.php' );
			$item = ob_get_clean();

			return sprintf( $item, $slug, $name, $this->get_loader() );

		}

		/**
		 * Get loader HTML
		 *
		 * @return string
		 */
		public function get_loader() {
			ob_start();
			jet_plugins_wizard()->get_template( 'loader.php' );
			return ob_get_clean();
		}

		/**
		 * Process wizard steps
		 *
		 * @return void
		 */
		public function dispatch() {

			$step = ! empty( $_GET['step'] ) ? $_GET['step'] : 0;

			$dispatch = apply_filters( 'jet-plugins-wizard/steps', array(
				'configure-plugins' => 'step-configure-plugins.php',
				'0'                 => 'step-service-notice.php',
				'1'                 => 'step-before-install.php',
				'2'                 => 'step-select-type.php',
				'3'                 => 'step-install.php',
				'4'                 => 'step-after-install.php',
			), $step );

			do_action( 'jet-plugins-wizards/page-before' );

			if ( isset( $dispatch[ $step ] ) ) {
				jet_plugins_wizard()->get_template( $dispatch[ $step ] );
			}

			do_action( 'jet-plugins-wizards/page-after' );

		}

		/**
		 * Show before import page title
		 *
		 * @return void
		 */
		public function before_import_title() {

			$skins = $this->get_skins();

			if ( empty( $skins ) ) {
				esc_html_e( 'No data found for installation', 'jet-plugins-wizard' );
			} elseif ( 1 === count( $skins ) ) {
				esc_html_e( 'Start install', 'jet-plugins-wizard' );
			} else {
				esc_html_e( 'Select skin and start install', 'jet-plugins-wizard' );
			}

		}

		/**
		 * Return available skins list
		 *
		 * @return array
		 */
		public function get_skins() {

			if ( ! empty( $this->skins ) ) {
				return $this->skins;
			}

			$this->skins = jet_plugins_wizard_settings()->get( array( 'skins', 'advanced' ) );

			return $this->skins;
		}

		/**
		 * Setup processed skin data
		 *
		 * @param  string $slug Skin slug.
		 * @param  array  $data Skin data.
		 * @return void
		 */
		public function the_skin( $slug = null, $data = array() ) {
			$data['slug'] = $slug;
			$this->skin = $data;
		}

		/**
		 * Retrun processed skin data
		 *
		 * @return array
		 */
		public function get_skin() {
			return $this->skin;
		}

		/**
		 * Get info by current screen.
		 *
		 * @param  string $key Key name.
		 * @return mixed
		 */
		public function get_skin_data( $key = null ) {

			if ( empty( $this->skin ) ) {
				$skin = isset( $_GET['skin'] ) ? esc_attr( $_GET['skin'] ) : false;

				if ( ! $skin ) {
					return false;
				}

				$data = jet_plugins_wizard_settings()->get( array( 'skins', 'advanced', $skin ) );
				$this->the_skin( $skin, $data );
			}

			if ( empty( $this->skin[ $key ] ) ) {
				return false;
			}

			return $this->skin[ $key ];
		}

		/**
		 * Returns skin plugins list
		 *
		 * @param  string $slug Skin name.
		 * @return string
		 */
		public function get_skin_plugins( $slug = null ) {

			$skins = $this->get_skins();
			$skin  = isset( $skins[ $slug ] ) ? $skins[ $slug ] : false;

			if ( ! $skin ) {
				return '';
			}

			$plugins = $skin[ 'full' ];

			if ( empty( $plugins ) ) {
				return '';
			}

			$registered  = jet_plugins_wizard_settings()->get( array( 'plugins' ) );
			$plugins_str = '';
			$format      = '<div class="jet-plugins-wizard-skin-plugins__item">%s</div>';

			foreach ( $plugins as $plugin ) {

				$plugin_data = isset( $registered[ $plugin ] ) ? $registered[ $plugin ] : false;

				if ( ! $plugin_data ) {
					continue;
				}

				$plugins_str .= sprintf( $format, $plugin_data['name'] );
			}

			return $plugins_str;
		}

		/**
		 * Return value from ini_get and ensure thats it integer.
		 *
		 * @param  string $key Key to retrieve from ini_get.
		 * @return int
		 */
		public function ini_get_int( $key = null ) {
			$val = ini_get( $key );
			return intval( $val );
		}

		/**
		 * Validae server requirements.
		 *
		 * @return string
		 */
		public function server_notice( $when = 'always' ) {

			$data = array(
				array(
					'arg'     => null,
					'_cb'     => 'phpversion',
					'rec'     => '5.4',
					'units'   => null,
					'name'    => esc_html__( 'PHP version', 'jet-plugins-wizard' ),
					'compare' => 'version_compare',
				),
				array(
					'arg'     => 'memory_limit',
					'_cb'     => array( $this, 'ini_get_int' ),
					'rec'     => 128,
					'units'   => 'Mb',
					'name'    => esc_html__( 'Memory limit', 'jet-plugins-wizard' ),
					'compare' => array( $this, 'val_compare' ),
				),
				array(
					'arg'     => 'max_execution_time',
					'_cb'     => 'ini_get',
					'rec'     => 60,
					'units'   => 's',
					'name'    => esc_html__( 'Max execution time', 'jet-plugins-wizard' ),
					'compare' => array( $this, 'val_compare' ),
				),
			);

			$format     = '<li class="jet-plugins-wizard-server__item%5$s">%1$s: %2$s%3$s &mdash; <b>%4$s</b></li>';
			$result     = '';
			$has_errors = false;

			foreach ( $data as $prop ) {

				if ( null !== $prop['arg'] ) {
					$val = call_user_func( $prop['_cb'], $prop['arg'] );
				} else {
					$val = call_user_func( $prop['_cb'] );
				}

				$compare = call_user_func( $prop['compare'], $val, $prop['rec'] );

				if ( -1 === $compare ) {

					$msg        = sprintf( esc_html__( '%1$s%2$s Recommended', 'jet-plugins-wizard' ), $prop['rec'], $prop['units'] );
					$scs        = '';
					$has_errors = true;

					$this->set_wizard_errors( $prop['arg'] );
				} else {
					$msg = esc_html__( 'Ok', 'jet-plugins-wizard' );
					$scs = ' check-success';
				}

				$result .= sprintf( $format, $prop['name'], $val, $prop['units'], $msg, $scs );

			}

			if ( 'always' === $when ) {
				return sprintf( '<ul class="jet-plugins-wizard-server">%s</ul>', $result );
			}

			if ( 'errors' === $when && $has_errors ) {
				$message = sprintf(
					'<div class="jet-plugins-wizard-server-error">%1$s</div>',
					__( 'Not all of your server parameters met requirements. You can continue the installation process, but it will take more time and can probably drive to bugs:', 'jet-plugins-wizard' )
				);
				return sprintf( '%2$s<ul class="jet-plugins-wizard-server">%1$s</ul>', $result, $message );
			}

		}

		/**
		 * Save wizard error.
		 *
		 * @param string $arg Norie to ada
		 */
		public function set_wizard_errors( $arg = null ) {

			$errors = wp_cache_get( 'errors', 'jet-plugins-wizard' );
			if ( ! $errors ) {
				$errors[ $arg ] = $arg;
			}
			wp_cache_set( 'errors', $errors, 'jet-plugins-wizard' );

		}

		/**
		 * Compare 2 values.
		 *
		 * @return int
		 */
		public function val_compare( $a, $b ) {

			$a = intval( $a );
			$b = intval( $b );

			if ( $a > $b ) {
				return 1;
			}

			if ( $a === $b ) {
				return 0;
			}

			if ( $a < $b ) {
				return -1;
			}

		}

		/**
		 * Returns start skin installation button HTML.
		 *
		 * @param  string $skin Skin slug.
		 * @return string
		 */
		public function get_install_skin_button( $skin = '' ) {

			$url    = jet_plugins_wizard()->get_page_link( array( 'step' => 2, 'skin' => $skin ) );
			$label  = esc_html__( 'Select Skin', 'jet-plugins-wizard' );
			$format = '<a href="%1$s" data-loader="true" class="btn btn-primary"><span class="text">%2$s</span><span class="jet-plugins-wizard-loader"><span class="jet-plugins-wizard-loader__spinner"></span></span></a>';

			if ( jet_plugins_wizard_data()->is_single_skin_theme() || jet_plugins_wizard_data()->is_single_type_skin( $skin ) ) {
				$label  = esc_html__( 'Start Install', 'jet-plugins-wizard' );
			}

			if ( jet_plugins_wizard_data()->is_single_type_skin( $skin ) ) {
				$next_step = isset( $_GET['advanced-install'] ) && '1' === $_GET['advanced-install'] ? 'configure-plugins' : 3;
				$url = jet_plugins_wizard()->get_page_link( array( 'step' => $next_step, 'skin' => $skin, 'type' => 'full' ) );
			}

			return sprintf( $format, $url, $label );
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
 * Returns instance of Jet_Plugins_Wizard_Interface
 *
 * @return object
 */
function jet_plugins_wizard_interface() {
	return Jet_Plugins_Wizard_Interface::get_instance();
}

jet_plugins_wizard_interface();
