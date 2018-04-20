<?php
/**
 * Template for service notice step.
 */
?>
<h2><?php esc_html_e( 'Installation Wizard', 'jet-plugins-wizard' ); ?></h2>
<div class="jet-plugins-wizard-msg"><?php esc_html_e( 'Demo data import wizard will guide you through the process of demo content import and recommended plugins installation. Before gettings started make sure your server complies with', 'jet-plugins-wizard' ); ?> <b><?php esc_html_e( 'WordPress minimal requirements.', 'jet-plugins-wizard' ); ?></b></div>
<h4><?php esc_html_e( 'Your system information:', 'jet-plugins-wizard' ); ?></h4>
<?php echo jet_plugins_wizard_interface()->server_notice(); ?>
<?php
	$errors = wp_cache_get( 'errors', 'jet-plugins-wizard' );
	if ( $errors ) {
		printf(
			'<div class="tm-warning-notice">%s</div>',
			esc_html__( 'Not all of your server parameters met requirements. You can continue the installation process, but it will take more time and can probably drive to bugs.', 'jet-plugins-wizard' )
		);
	}

jet_plugins_wizard()->get_template( 'start-install-button.php' );