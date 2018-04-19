<?php
/**
 * Plugin installer class.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Plugins_Wizard_Plugin_Upgrader extends Plugin_Upgrader {

	private $source = null;

	public function __construct( $skin = null ) {
		$this->source = $skin->source;
		parent::__construct( $skin );
	}

	/**
	 * Install a plugin package.
	 *
	 * @param string $package The full local path or URI of the package.
	 * @param array  $args    Optional arguments array.
	 * @return bool|WP_Error True if the install was successful, false or a WP_Error otherwise.
	 */
	public function install( $package, $args = array() ) {

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = wp_parse_args( $args, $defaults );

		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		if ( $parsed_args['clear_update_cache'] ) {
			// Clear cache so wp_update_plugins() knows about the new plugin.
			add_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9, 0 );
		}

		$this->run( array(
			'package'           => $package,
			'destination'       => WP_PLUGIN_DIR,
			'clear_destination' => false, // Do not overwrite files.
			'clear_working'     => true,
			'hook_extra'        => array(
				'type'   => 'plugin',
				'action' => 'install',
			)
		) );

		remove_action( 'upgrader_process_complete', 'wp_clean_plugins_cache', 9 );
		remove_filter( 'upgrader_source_selection', array( $this, 'check_package') );
		remove_filter( 'upgrader_source_selection', array( $this, 'maybe_adjust_source_dir' ), 1 );

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		// Force refresh of plugin update information
		wp_clean_plugins_cache( $parsed_args['clear_update_cache'] );

		return true;
	}

	/**
	 * Adjust the plugin directory name if necessary.
	 *
	 * The final destination directory of a plugin is based on the subdirectory name found in the
	 * (un)zipped source. In some cases - most notably GitHub repository plugin downloads -, this
	 * subdirectory name is not the same as the expected slug and the plugin will not be recognized
	 * as installed. This is fixed by adjusting the temporary unzipped source subdirectory name to
	 * the expected plugin slug.
	 *
	 * @since  1.0.0
	 * @param  string       $source        Path to upgrade/zip-file-name.tmp/subdirectory/.
	 * @param  string       $remote_source Path to upgrade/zip-file-name.tmp.
	 * @param  \WP_Upgrader $upgrader      Instance of the upgrader which installs the plugin.
	 * @return string $source
	 */
	public function maybe_adjust_source_dir( $source, $remote_source, $upgrader ) {

		global $wp_filesystem;

		if ( ! jet_plugins_wizard_installer()->is_wizard_request() || ! is_object( $wp_filesystem ) ) {
			return $source;
		}

		// Check for single file plugins.
		$source_files = array_keys( $wp_filesystem->dirlist( $remote_source ) );
		if ( 1 === count( $source_files ) && false === $wp_filesystem->is_dir( $source ) ) {
			return $source;
		}

		$desired_slug = isset( $upgrader->skin->options['plugin'] ) ? $upgrader->skin->options['plugin'] : false;

		if ( ! $desired_slug ) {
			return $source;
		}

		$subdir_name = untrailingslashit( str_replace( trailingslashit( $remote_source ), '', $source ) );

		if ( ! empty( $subdir_name ) && $subdir_name !== $desired_slug ) {

			$from_path = untrailingslashit( $source );
			$to_path   = trailingslashit( $remote_source ) . $desired_slug;

			if ( true === $wp_filesystem->move( $from_path, $to_path ) ) {
				return trailingslashit( $to_path );
			} else {
				return new WP_Error(
					'rename_failed',
					esc_html__( 'The remote plugin package does not contain a folder with the desired slug and renaming did not work.', 'jet-plugins-wizard' ) . ' ' . esc_html__( 'Please contact the plugin provider and ask them to package their plugin according to the WordPress guidelines.', 'jet-plugins-wizard' ),
					array( 'found' => $subdir_name, 'expected' => $desired_slug )
				);
			}

		} elseif ( empty( $subdir_name ) ) {
			return new WP_Error(
				'packaged_wrong',
				esc_html__( 'The remote plugin package consists of more than one file, but the files are not packaged in a folder.', 'jet-plugins-wizard' ) . ' ' . esc_html__( 'Please contact the plugin provider and ask them to package their plugin according to the WordPress guidelines.', 'jet-plugins-wizard' ),
				array( 'found' => $subdir_name, 'expected' => $desired_slug )
			);
		}

		return $source;
	}

	/**
	 * Grabs the plugin file from an installed plugin.
	 *
	 * @since 1.0.0
	 */
	public function plugin_info() {

		/** Return false if installation result isn't an array or the destination name isn't set */
		if ( ! is_array( $this->result ) ) {
			return $this->maybe_get_data_from_error();
		}

		if ( empty( $this->result['destination_name'] ) ) {
			return false;
		}

		/** Get the installed plugin file or return false if it isn't set */
		$plugin = get_plugins( '/' . $this->result['destination_name'] );
		if ( empty( $plugin ) ) {
			return false;
		}

		/** Assume the requested plugin is the first in the list */
		$pluginfiles = array_keys( $plugin );

		return $this->result['destination_name'] . '/' . $pluginfiles[0];

	}

	/**
	 * Try to get plugin data from error
	 *
	 * @return string|bool
	 */
	public function maybe_get_data_from_error() {

		if ( ! isset( $this->skin->result ) || ! is_wp_error( $this->skin->result ) ) {
			return false;
		}

		if ( ! isset( $this->skin->result->error_data['folder_exists'] ) ) {
			return false;
		}

		$path = $this->skin->result->error_data['folder_exists'];

		if ( ! $path ) {
			return false;
		}

		$plugin      = basename( $path );
		$plugin_data = get_plugins( '/' . $plugin );

		if ( empty( $plugin_data ) ) {
			return false;
		}

		/** Assume the requested plugin is the first in the list */
		$pluginfiles = array_keys( $plugin_data );

		return $plugin . '/' . $pluginfiles[0];
	}

}
