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
	mwp.controller.model( 'mwp-forms-controller', 
	{
		
		/**
		 * Initialization function
		 *
		 * @return	void
		 */
		init: function()
		{
			var self = this;
			var ajaxurl = this.local.ajaxurl;			
			
			this.viewModel = {};
			
			mwp.on( 'views.ready', function() {
				self.applyToggles();
			});
		},
		
		/**
		 * Resequence records
		 *
		 */
		resequenceRecords: function( event, ui, sortableElement, config )
		{
			var self = this;
			var sortedArray = sortableElement.sortable( 'toArray' );
			
			$.post( this.local.ajaxurl, {
				nonce: this.local.ajaxnonce,
				action: 'mwp_resequence_records',
				class: config.class,
				sequence: sortedArray
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
			var self = this;
			scope = scope ? $(scope) : $(document);
			
			scope.find('.mwp-form [form-toggles]').each( function() {				
				var element = $(this);
				if ( ! element.data( 'toggles-applied' ) ) {
					self.doToggles( element );
					var changeUpdate = function() { self.doToggles( element ); };
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
	
	/**
	 * Add forms related knockout bindings
	 *
	 */
	$.extend( ko.bindingHandlers, 
	{
		sequenceableRecords: {
			init: function( element, valueAccessor ) 
			{
				var config = ko.unwrap( valueAccessor() );
				if ( typeof $.fn.sortable !== 'undefined' ) 
				{
					var sortableElement = config.find ? $(element).find(config.find) : $(element);
					var options = $.extend( {
						placeholder: 'mwp-sortable-placeholder'
					}, config.options || {} );
					
					var updateCallback = config.callback || function( event, ui, sortableElement, config ) {
						var formsController = mwp.controller.get( 'mwp-forms-controller' );
						formsController.resequenceRecords( event, ui, sortableElement, config );
					};
					
					try {
						sortableElement.sortable( options );
						sortableElement.on( 'sortupdate', function( event, ui ) {
							if ( typeof updateCallback === 'function' ) {
								updateCallback( event, ui, sortableElement, config );
							}
						});
					}
					catch(e) {
						console.log( e );
					}
				}
			}
		}
	});	
})( jQuery );
 