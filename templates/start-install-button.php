<?php
/**
 * Start installation button
 */
?>
<a href="<?php echo jet_plugins_wizard()->get_page_link( array( 'step' => 1, 'advanced-install' => 1 ) ); ?>" data-loader="true" class="btn btn-primary start-install">
	<span class="text"><?php esc_html_e( 'Next', 'jet-plugins-wizard' ); ?></span>
	<span class="jet-plugins-wizard-loader"><span class="jet-plugins-wizard-loader__spinner"></span></span>
</a>