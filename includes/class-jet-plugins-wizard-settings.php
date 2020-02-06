<?php
/**
 * Settings manager
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Jet_Plugins_Wizard_Settings' ) ) {

	/**
	 * Define Jet_Plugins_Wizard_Settings class
	 */
	class Jet_Plugins_Wizard_Settings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Manifest file content
		 *
		 * @var array
		 */
		private $all_settings = null;

		/**
		 * External settings
		 *
		 * @var array
		 */
		private $external_settings = array();

		/**
		 * Manifest defaults
		 *
		 * @var array
		 */
		private $defaults = null;
			/**
		 * Has registered external config
		 *
		 * @var boolean
		 */
		private $has_external = false;

		/**
		 * Get settings from array.
		 *
		 * @param  array  $settings Settings trail to get.
		 * @return mixed
		 */
		public function get( $settings = array() ) {

			$all_settings = $this->get_all_settings();

			if ( ! $all_settings ) {
				return false;
			}

			if ( ! is_array( $settings ) ) {
				$settings = array( $settings );
			}

			$count  = count( $settings );
			$result = $all_settings;

			for ( $i = 0; $i < $count; $i++ ) {

				if ( empty( $result[ $settings[ $i ] ] ) ) {
					return false;
				}

				$result = $result[ $settings[ $i ] ];

				if ( $count - 1 === $i ) {
					return $result;
				}

			}

		}

		/**
		 * Add new 3rd party configuration
		 * @param  array  $config [description]
		 * @return [type]         [description]
		 */
		public function register_external_config( $config = array() ) {
			$this->external_settings = array_merge( $this->external_settings, $config );
		}
		/**
		 * Return external config status
		 * @return boolean [description]
		 */
		public function has_external() {
			return $this->has_external;
		}
		/**
		 * Get mainfest
		 *
		 * @return mixed
		 */
		public function get_all_settings() {

			if ( null !== $this->all_settings ) {
				return $this->all_settings;
			}
			$this->has_external      = true;
			$settings = $this->external_settings;

			$all_settings = array(
				'license' => isset( $settings['license'] ) ? $settings['license'] : $this->get_defaults( 'license' ),
				'plugins' => isset( $settings['plugins'] ) ? $settings['plugins'] : $this->get_defaults( 'plugins' ),
				'skins'   => isset( $settings['skins'] )   ? $settings['skins']   : $this->get_defaults( 'skins' ),
				'texts'   => isset( $settings['texts'] )   ? $settings['texts']   : $this->get_defaults( 'texts' ),
			);

			$this->all_settings = $this->maybe_update_remote_data( $all_settings );

			return $this->all_settings;
		}

		/**
		 * Maybe update remote settings data
		 *
		 * @param  array $settings Plugins settings
		 * @return array
		 */
		public function maybe_update_remote_data( $settings ) {

			if ( ! empty( $settings['plugins']['get_from'] ) ) {
				$settings['plugins'] = $this->get_remote_data( $settings['plugins']['get_from'], 'jet_wizard_plugins' );
			}

			if ( ! empty( $settings['skins']['get_from'] ) ) {
				$settings['skins'] = $this->get_remote_data( $settings['skins']['get_from'], 'jet_wizard_skins' );
			}

			return $settings;

		}

		/**
		 * Get remote data for wizard
		 *
		 * @param  [type] $url           [description]
		 * @param  [type] $transient_key [description]
		 * @return [type]                [description]
		 */
		public function get_remote_data( $url, $transient_key ) {

			$data = get_site_transient( $transient_key );
			if ( $this->has_external() ) {
				$data = false;
			}

			if ( ! $data ) {

				$response = wp_remote_get( $url, array(
					'timeout'   => 60,
					'sslverify' => false,
				) );

				$data = wp_remote_retrieve_body( $response );
				$data = json_decode( $data, true );

				if ( empty( $data ) ) {
					$data = array();
				}

				if ( ! $this->has_external() ) {
					set_site_transient( $transient_key, $data, 2 * DAY_IN_SECONDS );
				}

			}

			return $data;

		}

		/**
		 * Clear transien data cahces
		 *
		 * @return [type] [description]
		 */
		public function clear_transient_data() {
			set_site_transient( 'jet_wizard_plugins', null );
			set_site_transient( 'jet_wizard_skins', null );
		}

		/**
		 * Get wizard defaults
		 *
		 * @param  string $part What part of manifest to get (optional - if empty return all)
		 * @return array
		 */
		public function get_defaults( $part = null ) {

			if ( null === $this->defaults ) {
				include jet_plugins_wizard()->path( 'includes/config/default-config.php' );

				$this->defaults = array(
					'license' => $license,
					'plugins' => $plugins,
					'skins'   => $skins,
					'texts'   => $texts,
				);

			}

			if ( ! $part ) {
				return $this->defaults;
			}

			if ( isset( $this->defaults[ $part ] ) ) {
				return $this->defaults[ $part ];
			}

			return array();
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
 * Returns instance of Jet_Plugins_Wizard_Settings
 *
 * @return object
 */
function jet_plugins_wizard_settings() {
	return Jet_Plugins_Wizard_Settings::get_instance();
}
