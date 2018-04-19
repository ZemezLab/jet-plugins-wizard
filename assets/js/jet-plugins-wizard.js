( function( $, settings ) {

	'use strict';

	var JetPluginsWizrad = {
		css: {
			plugins: '.jet-plugins-wizard-plugins',
			progress: '.jet-plugins-wizard-progress__bar',
			showResults: '.jet-plugins-wizard-install-results__trigger',
			showPlugins: '.jet-plugins-wizard-skin-item__plugins-title',
			loaderBtn: '[data-loader="true"]',
			start: '.start-install',
			storePlugins: '.store-plugins'
		},

		vars: {
			plugins: null,
			template: null,
			currProgress: 0,
			progress: null
		},

		init: function() {

			var self = this;

			self.vars.progress = $( self.css.progress );
			self.vars.percent  = $( '.jet-plugins-wizard-progress__label', self.vars.progress );

			$( document )
				.on( 'click.JetPluginsWizrad', self.css.showResults, self.showResults )
				.on( 'click.JetPluginsWizrad', self.css.showPlugins, self.showPlugins )
				.on( 'click.JetPluginsWizrad', self.css.storePlugins, self.storePlugins )
				.on( 'click.JetPluginsWizrad', self.css.loaderBtn, self.showLoader );

			if ( undefined !== settings.firstPlugin ) {
				self.vars.template = wp.template( 'wizard-item' );
				settings.firstPlugin.isFirst = true;
				self.installPlugin( settings.firstPlugin );
			}
		},

		storePlugins: function() {

			var $this   = $( this ),
				href    = $this.attr( 'href' ),
				plugins = [];

			event.preventDefault();

			$( '.tm-config-list input[type="checkbox"]:checked' ).each( function( index, el ) {
				plugins.push( $( this ).attr( 'name' ) );
			} );

			$.ajax({
				url: ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'jet_plugins_wizard_store_plugins',
					plugins: plugins
				}
			}).done( function( response ) {
				window.location = href;
			});

		},

		showLoader: function() {
			$( this ).addClass( 'in-progress' );
		},

		showPlugins: function() {
			$( this ).toggleClass( 'is-active' );
		},

		showResults: function() {
			var $this = $( this );
			$this.toggleClass( 'is-active' );
		},

		installPlugin: function( data ) {

			var $target = $( JetPluginsWizrad.vars.template( data ) );

			if ( null === JetPluginsWizrad.vars.plugins ) {
				JetPluginsWizrad.vars.plugins = $( JetPluginsWizrad.css.plugins );
			}

			$target.appendTo( JetPluginsWizrad.vars.plugins );
			console.log( data );
			JetPluginsWizrad.installRequest( $target, data );

		},

		updateProgress: function() {

			var val   = 0,
				total = parseInt( settings.totalPlugins );

			JetPluginsWizrad.vars.currProgress++;

			val = 100 * ( JetPluginsWizrad.vars.currProgress / total );
			val = Math.round( val );

			if ( 100 < val ) {
				val = 100;
			}

			JetPluginsWizrad.vars.percent.html( val + '%' );
			JetPluginsWizrad.vars.progress.css( 'width', val + '%' );

		},

		installRequest: function( target, data ) {

			var icon;

			data.action = 'jet_plugins_wizard_install_plugin';

			if ( undefined === data.isFirst ) {
				data.isFirst = false;
			}

			$.ajax({
				url: ajaxurl,
				type: 'get',
				dataType: 'json',
				data: data
			}).done( function( response ) {

				JetPluginsWizrad.updateProgress();

				if ( true !== response.success ) {
					return;
				}

				target.append( response.data.log );

				if ( true !== response.data.isLast ) {
					JetPluginsWizrad.installPlugin( response.data );
				} else {

					$( document ).trigger( 'jet-plugins-wizard-install-finished' );

					if ( 1 == settings.redirect ) {
						window.location = response.data.redirect;
					}

					target.after( response.data.message );

				}

				if ( 'error' === response.data.resultType ) {
					icon = '<span class="dashicons dashicons-no"></span>';
				} else {
					icon = '<span class="dashicons dashicons-yes"></span>';
				}

				target.addClass( 'installed-' + response.data.resultType );
				$( '.jet-plugins-wizard-loader', target ).replaceWith( icon );

			});
		}
	};

	JetPluginsWizrad.init();

}( jQuery, window.JetWizardSettings ) );