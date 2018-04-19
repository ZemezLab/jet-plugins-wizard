<?php
/**
 * Template for service notice step.
 */
?>
<h2><?php esc_html_e( 'Installation Wizard', 'jet-plugins-wizard' ); ?></h2>
<div class="jet-plugins-wizard-msg"><?php esc_html_e( 'Demo data import wizard will guide you through the process of demo content import and recommended plugins installation. Before gettings started please activate your license key.', 'jet-plugins-wizard' ); ?></div>
<div class="jet-plugins-wizard-license-form">
	<input type="text" class="jet-plugins-wizard-input" placeholder="<?php _e( 'Please enter your license key', 'jet-plugins-wizard' ); ?>">
	<a href="#" data-loader="true" class="btn btn-primary jet-plugins-wizard-activate-license">
		<span class="text"><?php esc_html_e( 'Activate License', 'jet-plugins-wizard' ); ?></span>
		<span class="jet-plugins-wizard-loader"><span class="jet-plugins-wizard-loader__spinner"></span></span>
	</a>
	<div class="jet-plugins-wizard-license-errors"></div>
</div>
