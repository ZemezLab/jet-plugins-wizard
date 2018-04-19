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
		 * Get mainfest
		 *
		 * @return mixed
		 */
		public function get_all_settings() {

			if ( null !== $this->all_settings ) {
				return $this->all_settings;
			}

			if ( empty( $this->external_settings ) ) {
				return false;
			}

			$settings = $this->external_settings;

			$this->all_settings = array(
				'license' => isset( $settings['license'] ) ? $settings['license'] : $this->get_defaults( 'license' ),
				'plugins' => isset( $settings['plugins'] ) ? $settings['plugins'] : $this->get_defaults( 'plugins' ),
				'skins'   => isset( $settings['skins'] )   ? $settings['skins']   : $this->get_defaults( 'skins' ),
				'texts'   => isset( $settings['texts'] )   ? $settings['texts']   : $this->get_defaults( 'texts' ),
			);

			return $this->all_settings;
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
