<?php
/**
 * Skin item template
 */

$skin = jet_plugins_wizard_interface()->get_skin_data( 'slug' );

?>
<div class="jet-plugins-wizard-skin-item">
	<div class="jet-plugins-wizard-skin-item-content">
		<?php if ( jet_plugins_wizard_interface()->get_skin_data( 'thumb' ) ) : ?>
		<div class="jet-plugins-wizard-skin-item__thumb">
			<img src="<?php echo jet_plugins_wizard_interface()->get_skin_data( 'thumb' ); ?>" alt="">
		</div>
		<?php endif; ?>
		<div class="jet-plugins-wizard-skin-item__summary">
			<h4 class="jet-plugins-wizard-skin-item__title"><?php echo jet_plugins_wizard_interface()->get_skin_data( 'name' ); ?></h4>
			<div class="jet-plugins-wizard-skin-item__actions">
				<?php echo jet_plugins_wizard_interface()->get_install_skin_button( $skin ); ?>
				<a href="<?php echo jet_plugins_wizard_interface()->get_skin_data( 'demo' ) ?>" data-loader="true" class="btn btn-default"><span class="text"><?php
					esc_html_e( 'View Demo', 'jet-plugins-wizard' );
				?></span><span class="jet-plugins-wizard-loader"><span class="jet-plugins-wizard-loader__spinner"></span></span></a>
			</div>
		</div>
	</div>
</div>