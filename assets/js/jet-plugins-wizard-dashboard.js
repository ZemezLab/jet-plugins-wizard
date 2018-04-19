( function( $ ) {

	'use strict';

	var tmWizardDashboard = {

		init: function() {
			$( document ).on( 'click.tmWizardDashboard', '.wizard-plugin__link', tmWizardDashboard.processPlugin );
		},

		processPlugin: function() {

			var $this      = $( this ),
				$plugin    = $this.closest( '.wizard-plugin' ),
				data       = {
					action: 'jet_plugins_wizard_process_single_plugin'
				};

			if ( $this.hasClass( 'in-progress' ) ) {
				return;
			}

			$this.addClass( 'in-progress' );

			data.slug         = $plugin.attr( 'data-slug' );
			data.path         = $plugin.attr( 'data-path' );
			data.pluginAction = $this.attr( 'data-action' );

			$.ajax({
				url: ajaxurl,
				type: 'get',
				dataType: 'json',
				data: data
			}).done( function( response ) {

				$this.removeClass( 'in-progress' );

				if ( true === response.success ) {
					$plugin.replaceWith( response.data.message );
				} else {
					$this.replaceWith( response.data.message );
				}
			});
		}

	};

	tmWizardDashboard.init();

}( jQuery ) );