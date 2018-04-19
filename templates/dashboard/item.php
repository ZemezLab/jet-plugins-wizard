<?php
/**
 * Dashboard item
 */
?>
<div class="wizard-plugin" data-slug="<?php echo $data['slug']; ?>" data-path="<?php echo $data['pluginpath']; ?>">
	<div class="wizard-plugin__label">
		<?php echo $data['name']; ?>
	</div>
	<div class="wizard-plugin__actions"><?php
		if ( true !== $data['installed'] ) {
			jet_plugins_wizard()->get_template( 'dashboard/btn-install.php' );
		} elseif ( true === $data['installed'] && false === $data['activated'] ) {
			jet_plugins_wizard()->get_template( 'dashboard/btn-activate.php' );
		} elseif ( true === $data['installed'] && true === $data['activated'] ) {
			echo '<span class="dashicons dashicons-yes"></span>';
		}
	?></div>
</div>