<?php
/**
 * 1st wizard step template
 */
?>
<h2><?php jet_plugins_wizard_interface()->before_import_title(); ?></h2>
<div class="jet-plugins-wizard-msg"><?php esc_html_e( 'Each skin comes with custom demo content and predefined set of plugins. Depending upon the selected skin the wizard will install required plugins and some demo posts and pages', 'jet-plugins-wizard' ); ?></div>
<div class="jet-plugins-wizard-skins"><?php
	$skins = jet_plugins_wizard_interface()->get_skins();

	if ( ! empty( $skins ) ) {

		foreach ( $skins as $skin => $skin_data ) {
			jet_plugins_wizard_interface()->the_skin( $skin, $skin_data );
			jet_plugins_wizard()->get_template( 'skin-item.php' );
		}

	}

?></div>