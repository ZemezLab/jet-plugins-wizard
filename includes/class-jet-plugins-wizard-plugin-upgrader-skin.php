<?php
/**
 * Plugin installer skin class.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Jet_Plugins_Wizard_Plugin_Upgrader_Skin extends Plugin_Installer_Skin {

	/**
	 * Holder for installation source type.
	 *
	 * @var string
	 */
	public $source = 'wordpress';

	/**
	 * Result type
	 *
	 * @var string
	 */
	public $result_type = 'success';

	/**
	 * Construtor for the class.
	 *
	 * @param array $args Options array.
	 */
	public function __construct( $args = array() ) {
		$this->source = isset( $args['source'] ) ? $args['source'] : $this->source;
		parent::__construct( $args );
	}

	/**
	 * Output markup after plugin installation processed.
	 */
	public function after() {}

	/**
	 *  Output header markup.
	 */
	public function header() {
		if ( $this->done_header ) {
			return;
		}
		$this->done_header = true;
		echo '<div class="jet-plugins-wizard-install-results">';
			echo '<div class="jet-plugins-wizard-install-results__trigger">';
				esc_html_e( 'Details', 'jet-plugins-wizard' );
			echo '</div>';
			echo '<ul>';
	}

	/**
	 *  Output footer markup.
	 */
	public function footer() {
		if ( $this->done_footer ) {
			return;
		}
		$this->done_footer = true;
		echo '</ul>';
		echo '</div>';
	}

	/**
	 *
	 * @param string|WP_Error $errors
	 */
	public function error( $errors ) {

		if ( ! $this->done_header ) {
			$this->header();
		}

		if ( is_string( $errors ) ) {
			$this->feedback( $errors );
		} elseif ( is_wp_error( $errors ) && $errors->get_error_code() ) {

			$this->set_result_type( $errors->errors );

			foreach ( $errors->get_error_messages() as $message ) {
				if ( $errors->get_error_data() && is_string( $errors->get_error_data() ) ) {
					$this->feedback( $message . ' ' . esc_html( strip_tags( $errors->get_error_data() ) ) );
				} else {
					$this->feedback( $message );
				}
			}
		}
	}

	/**
	 * Set warning or error result type.
	 *
	 * @param array $errors Errors array
	 */
	public function set_result_type( $errors ) {

		if ( array_key_exists( 'folder_exists', $errors ) ) {
			$this->result_type = 'warning';
		} else {
			$this->result_type = 'error';
		}

	}

	/**
	 *
	 * @param string $string
	 */
	public function feedback( $string ) {

		if ( isset( $this->upgrader->strings[ $string ] ) )
			$string = $this->upgrader->strings[ $string ];

		if ( false !== strpos( $string, '%' ) ) {
			$args = func_get_args();
			$args = array_splice( $args, 1 );

			if ( $args ) {
				$args = array_map( 'strip_tags', $args );
				$args = array_map( 'esc_html', $args );
				$string = vsprintf( $string, $args );
			}
		}
		if ( empty( $string ) ) {
			return;
		}

		if ( is_wp_error( $string ) ) {

			if ( $string->get_error_data() && is_string( $string->get_error_data() ) ) {
				$string = $string->get_error_message() . ': ' . $string->get_error_data();
			} else {
				$string = $string->get_error_message();
			}

		}

		printf( '<li>%s</li>', $string );
	}

}