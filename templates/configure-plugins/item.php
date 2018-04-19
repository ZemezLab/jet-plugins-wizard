<?php
/**
 * Configure plugins / single plugin item template.
 */
$is_base  = ( 'base' === $data['access'] ) ? true : false;
$checked  = ( true === $is_base || jet_plugins_wizard_data()->is_current_skin_plugin( $data['slug'] ) ) ? ' checked' : '';
$disabled = ( true === $is_base ) ? ' disabled' : '';

?>
<div class="tm-config-plugin<?php echo $disabled . $checked; ?>">
	<label>
		<input type="checkbox" name="<?php echo $data['slug']; ?>"<?php echo $disabled . $checked; ?>>
		<?php echo $data['name']; ?>
	</label>
</div>