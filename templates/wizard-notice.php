<?php
/**
 * Wizard notice template.
 */

$theme = jet_plugins_wizard_settings()->get( array( 'texts', 'theme-name' ) );
?>
<div class="jet-plugins-wizard-notice notice">
	<div class="jet-plugins-wizard-notice__content"><?php
		printf( esc_html__( 'This wizard will help you to select skin, install plugins and import demo data for your %s theme. To start the install click the button below!', 'jet-plugins-wizard' ), '<b>' . $theme . '</b>' );
	?></div>
	<div class="jet-plugins-wizard-notice__actions">
		<a class="jet-plugins-wizard-btn" href="<?php echo cherry_plugin_wizard()->get_page_link(); ?>"><?php
			esc_html_e( 'Start Install', 'jet-plugins-wizard' );
		?></a>
		<a class="notice-dismiss" href="<?php echo add_query_arg( array( 'cherry_plugin_wizard_dismiss' => true, '_nonce' => cherry_plugin_wizard()->nonce() ) ); ?>"><?php
			esc_html_e( 'Dismiss', 'jet-plugins-wizard' );
		?></a>
	</div>
</div>
