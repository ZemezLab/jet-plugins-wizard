<?php
/**
 * Extensions
 *
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Plugins_Wizard_Extensions' ) ) {

	/**
	 * Define Jet_Plugins_Wizard_Extensions class
	 */
	class Jet_Plugins_Wizard_Extensions {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class.
		 */
		public function __construct() {

			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_bp_redirect' ) );
			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_elementor_redirect' ) );
			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_bbp_redirect' ) );
			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_booked_redirect' ) );
			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_tribe_redirect' ) );
			add_action( 'jet-plugins-wizard/after-plugin-activation', array( $this, 'prevent_woo_redirect' ) );

			add_action( 'jet-plugins-wizard/install-finished', array( $this, 'ensure_prevent_booked_redirect' ) );

			if ( jet_plugins_wizard()->is_wizard( 4 ) ) {
				add_action( 'jet-plugins-wizard/page-after', array( $this, 'ensure_prevent_booked_redirect' ) );
			}

			add_filter( 'jet-plugins-wizard/send-install-data', array( $this, 'add_multi_arg' ), 10, 2 );

			add_action( 'init', array( $this, 'set_success_redirect_for_theme_wizard' ) );
			add_action( 'tm_dashboard_add_section', array( $this, 'add_dashboard_plugins_section' ), 25, 2 );
			add_action( 'admin_head', array( $this, 'maybe_print_dashboard_css' ), 99 );

			// Booked somitemes not processed correctly and still redirect so pervent it hard
			add_filter( 'pre_transient__booked_welcome_screen_activation_redirect', array( $this, 'hard_prevent_booked_redirect' ), 10, 2 );
		}

		/**
		 * Set hook for rewriting theme wizard success redirect
		 *
		 * @return void
		 */
		public function set_success_redirect_for_theme_wizard() {
			if ( jet_plugins_wizard_settings()->get_all_settings() ) {
				add_filter( 'ttw_success_redirect_url', array( $this, 'theme_wizard_success_redirect' ) );
				add_filter( 'mpack-wizard/success-redirect-url', array( $this, 'theme_wizard_success_redirect' ) );
				add_filter( 'jet-theme-wizard/success-redirect-url', array( $this, 'theme_wizard_success_redirect' ) );
			}
		}

		/**
		 * Ensure that we prevent booked plugin redirect
		 *
		 * @return void
		 */
		public function ensure_prevent_booked_redirect() {

			delete_transient( '_booked_welcome_screen_activation_redirect' );
			update_option( 'booked_welcome_screen', false );

		}

		/**
		 * Set theme wizard success redirect.
		 *
		 * @param string|bool $redirect Redirect
		 */
		public function theme_wizard_success_redirect( $redirect ) {

			$redirect = jet_plugins_wizard()->get_page_link( array( 'step' => 1, 'advanced-install' => 1 ) );
			$skin     = false;

			if ( jet_plugins_wizard_data()->is_single_skin_theme() ) {
				$skin = jet_plugins_wizard_data()->get_first_skin();
			}

			if ( false !== $skin && jet_plugins_wizard_data()->is_single_type_skin( $skin['skin'] ) ) {
				$redirect = jet_plugins_wizard()->get_page_link(
					array( 'step' => 'configure-plugins', 'skin' => $skin['skin'], 'type' => 'full' )
				);
			}

			if ( false !== $skin && ! jet_plugins_wizard_data()->is_single_type_skin( $skin['skin'] ) ) {
				$redirect = jet_plugins_wizard()->get_page_link(
					array( 'step' => 2, 'skin' => $skin['skin'] )
				);
			}

			return $redirect;
		}

		/**
		 * Hard prevent booked redirect
		 *
		 * @param  bool $pre   Pre-get value.
		 * @param  bool $value Default transient value.
		 * @return mixed
		 */
		public function hard_prevent_booked_redirect( $pre, $value ) {
			return null;
		}

		/**
		 * Maybe print dashboard CSS file
		 *
		 * @return void
		 */
		public function maybe_print_dashboard_css() {

			if ( ! isset( $_GET['page'] ) || 'tm-dashboard' !== $_GET['page'] ) {
				return;
			}

			jet_plugins_wizard()->print_inline_css( 'dashboard.css' );
			wp_enqueue_script( 'jet-plugins-wizard-dashboard' );

		}

		/**
		 * Adds required theme plugins on dashboard page.
		 *
		 * @param object $builder   Builder module instance.
		 * @param object $dashboard Dashboard plugin instance.
		 */
		public function add_dashboard_plugins_section( $builder, $dashboard ) {

			$plugins = jet_plugins_wizard_settings()->get( array( 'plugins' ) );

			if ( empty( $plugins ) ) {
				return;
			}

			ob_start();

			foreach ( $plugins as $slug => $plugin ) {
				$this->single_plugin_item( $slug, $plugin );
			}

			$content = ob_get_clean();

			$builder->register_section(
				array(
					'jet-plugins-wizard' => array(
						'title' => esc_html__( 'Recommended plugins', 'jet-plugins-wizard' ),
						'class' => 'tm-dashboard-section tm-dashboard-section--jet-plugins-wizard',
						'view'  => $dashboard->plugin_dir( 'admin/views/section.php' ),
					),
				)
			);

			$builder->register_html(
				array(
					'jet-plugins-wizard-content' => array(
						'parent' => 'jet-plugins-wizard',
						'html'   => $content,
					),
				)
			);

		}

		/**
		 * Print single plugin item for dashbaord list.
		 *
		 * @param  string $slug   Plugins slug.
		 * @param  array  $plugin Plugins data.
		 * @return void
		 */
		public function single_plugin_item( $slug, $plugin ) {

			$plugin_data = get_plugins( '/' . $slug );
			$pluginfiles = array_keys( $plugin_data );
			$installed   = true;
			$activated   = false;
			$plugin_path = null;

			if ( empty( $pluginfiles ) ) {
				$installed = false;
			} else {
				$plugin_path = $slug . '/' . $pluginfiles[0];
				$activated   = is_plugin_active( $plugin_path );
			}

			$data = array_merge(
				array(
					'slug'       => $slug,
					'pluginpath' => $plugin_path,
					'installed'  => $installed,
					'activated'  => $activated,
				),
				$plugin
			);

			jet_plugins_wizard()->get_template( 'dashboard/item.php', $data );
		}

		/**
		 * Prevent redirect after WooCommerce activation.
		 *
		 * @param  string $plugin Plugin slug.
		 * @return bool
		 */
		public function prevent_woo_redirect( $plugin ) {

			if ( 'woocommerce' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_wc_activation_redirect' );

			return true;
		}

		/**
		 * Prevent BuddyPress redirect.
		 *
		 * @return bool
		 */
		public function prevent_bp_redirect( $plugin ) {

			if ( 'buddypress' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_bp_activation_redirect' );
			delete_transient( '_bp_is_new_install' );

			return true;
		}

		/**
		 * Prevent Elementor redirect.
		 *
		 * @return bool
		 */
		public function prevent_elementor_redirect( $plugin ) {

			if ( 'elementor' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( 'elementor_activation_redirect' );

			return true;
		}



		/**
		 * Prevent BBPress redirect.
		 *
		 * @return bool
		 */
		public function prevent_bbp_redirect( $plugin ) {

			if ( 'bbpress' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_bbp_activation_redirect' );

			return true;
		}

		/**
		 * Prevent booked redirect.
		 *
		 * @return bool
		 */
		public function prevent_booked_redirect( $plugin ) {

			if ( 'booked' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_booked_welcome_screen_activation_redirect' );
			update_option( 'booked_welcome_screen', false );

			return true;
		}

		/**
		 * Prevent tribe events calendar redirect.
		 *
		 * @return bool
		 */
		public function prevent_tribe_redirect( $plugin ) {

			if ( 'the-events-calendar' !== $plugin['slug'] ) {
				return false;
			}

			delete_transient( '_tribe_tickets_activation_redirect' );
			delete_transient( '_tribe_events_activation_redirect' );

			return true;
		}

		/**
		 * Add multi-install argument.
		 *
		 * @param  array  $data   Send data.
		 * @param  string $plugin Plugin slug.
		 * @return array
		 */
		public function add_multi_arg( $data = array(), $plugin = '' ) {

			if ( in_array( $plugin, array( 'woocommerce', 'booked' ) ) ) {
				$data['activate-multi'] = true;
			}

			return $data;
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
 * Returns instance of Jet_Plugins_Wizard_Extensions
 *
 * @return object
 */
function jet_plugins_wizard_ext() {
	return Jet_Plugins_Wizard_Extensions::get_instance();
}

jet_plugins_wizard_ext();
