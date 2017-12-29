/**
 * Plugin Javascript Module
 *
 * Created     December 22, 2017
 *
 * @package    Modern Framework for Wordpress
 * @author     Kevin Carwile
 * @since      {build_version}
 */

/**
 * Controller Design Pattern
 *
 * Note: This pattern has a dependency on the "mwp" script
 * i.e. @Wordpress\Script( deps={"mwp"} )
 */
(function( $, undefined ) {
	
	"use strict";

	/**
	 * Forms Controller
	 *
	 */
	var formsController = mwp.controller( 'mwp-forms-controller', 
	{
		
		/**
		 * Initialization function
		 *
		 * @return	void
		 */
		init: function()
		{
			var ajaxurl = formsController.local.ajaxurl;			
			this.viewModel = {};
			
			mwp.on( 'views.ready', function() {
				formsController.applyToggles();
			});
		},
		
		/**
		 * Apply form toggling functionality
		 *
		 * @param	jQuery|dom|undefined		scope			The scope which to apply functionality to
		 * @return	void
		 */
		applyToggles: function( scope ) 
		{
			scope = scope ? $(scope) : $(document);
			
			scope.find('.mwp-form [form-toggles]').each( function() {				
				var element = $(this);
				if ( ! element.data( 'toggles-applied' ) ) {
					formsController.doToggles( element );
					var changeUpdate = function() { formsController.doToggles( element ); };
					element.is('div') ? element.find('input').on( 'change', changeUpdate ) : element.on( 'change', changeUpdate );
					element.data( 'toggles-applied', true );
				}
			});
		},
		
		/**
		 * Do the toggles for a given field
		 *
		 * @param	jQuery			element				jQuery wrapped dom element
		 * @return	void
		 */
		doToggles: function( element )
		{
			var value_toggles = JSON.parse( element.attr('form-toggles') );
			var toggles = {	selected: { show: [], hide: [] }, other: { show: [], hide: [] } };
			
			var current_value = $.isArray( element.val() ) ? element.val() : [ element.val() ];
			
			if ( element.is('div') ) {
				current_value = $.map( element.find( ':selected,:checked' ), function( el ) { return $(el).val().toString(); } );
			}
			
			/**
			 * If an input value toggles another field to 'show', we wanto to hide it if that value
			 * is not currently selected. So we need to sort everything out to know what needs to
			 * be hidden and shown based on the field state.
			 *
			 */
			$.each( value_toggles, function( value, actions ) {
				_.each( ['show','hide'], function( action ) {
					if ( typeof actions[action] !== 'undefined' ) {
						var selectors = $.isArray( actions[action] ) ? actions[action] : [ actions[action] ];
						var arr = toggles[ ( current_value.indexOf( (value).toString() ) >= 0 ? 'selected' : 'other' ) ][ action ];
						arr.push.apply( arr, selectors );
					}
				});				
			});

			/* Do the toggles */
			_.each( toggles.other.show, function( selector ) { $(selector).hide(); } );
			_.each( toggles.other.hide, function( selector ) { $(selector).show(); } );
			_.each( toggles.selected.show, function( selector ) { $(selector).show(); } );
			_.each( toggles.selected.hide, function( selector ) { $(selector).hide(); } );
			
		}
	
	});
	
	
})( jQuery );
 