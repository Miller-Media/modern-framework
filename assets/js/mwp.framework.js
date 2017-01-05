/**
 * Plugin Javascript Module
 *
 * Created     December 30, 2016
 *
 * @package    Modern Framework for Wordpress
 * @author     Kevin Carwile
 * @since      1.0.3
 */

"use strict";

window.mwp = {};

/**
 * Module Design Pattern
 */
(function( $, undefined ) {
	
	var controllers = {};
	var models = {};
	var collections = {};
	var local = mw_localized_data;
	
	/**
	 * Base models
	 */
	mwp.base = {
	
		Controller: Backbone.Model.extend({
		
			/**
			 * @var	object		Controller view model
			 */
			viewModel: {},
			
			/**
			 * Constructor
			 *
			 * @return	void
			 */
			initialize: function()
			{
				var controller = this;
				this.local = $.extend( local, mw_localized_data );
				
				$(document).ready( function() {
					if( typeof controller.init == 'function' ) {
						controller.init();
					}
				});
			},
			
			/**
			 * Return self for knockback binding compatibility
			 *
			 * @return	this
			 */
			model: function() {
				return this;
			}
			
		}),
		
		Model: Backbone.Model.extend({
		
			/**
			 * Return self for knockback binding compatibility
			 *
			 * @return	this
			 */
			model: function() {
				return this;
			}
			
		}),
		
		Collection: Backbone.Collection.extend({
		
		})
		
	};
	
	mwp.controller = function( name, properties, classProperties ) 
	{
		var controller = mwp.base.Controller.extend( properties, classProperties );
		controllers[ name ] = new controller;
		return controllers[ name ];
	};
		
	mwp.controller.get = function( name )
	{
		if ( typeof controllers[ name ] !== 'undefined' ) {
			return controllers[ name ];
		}
		
		return undefined;
	};
	
	mwp.model = function( name, properties, classProperties )
	{
		models[ name ] = mwp.base.Model.extend( properties, classProperties );
		return models[ name ];
	};
	
	mwp.model.get = function( name )
	{
		if ( typeof models[ name ] !== 'undefined' ) {
			return models[ name ];
		}
		
		return undefined;
	};
	
	mwp.collection = function( name, properties, classProperties )
	{
		collections[ name ] = mwp.base.Collection.extend( properties, classProperties );
		return collections[ name ];
	};
	
	mwp.collection.get = function( name )
	{
		if ( typeof collections[ name ] !== 'undefined' ) {
			return collections[ name ];
		}
		
		return undefined;
	};
	
	if ( typeof ko !== 'undefined' )
	{
		ko.bindingHandlers.stopBinding = {
			init: function() {
				return { controlsDescendantBindings: true };
			}
		};

		ko.virtualElements.allowedBindings.stopBinding = true;
		
		/* Double ready means that this will be executed last */
		$(document).ready( function() {
			$(document).ready( function() {
				var views = $('[data-view-model]');
				
				/* Wrap all views with a protective shield */
				views.each( function() {
					$(this).before( '<!-- ko stopBinding: true -->' );
					$(this).after( '<!-- /ko -->' );
				});
				
				/* Bind all views */
				views.each( function() {
					var view = $(this);
					var view_name = view.data( 'view-model' );
					var controller = mwp.controller.get( view.data( 'view-model' ) );
					if ( typeof controller !== 'undefined' && typeof controller.viewModel !== 'undefined' )
					{
						ko.applyBindings( controller.viewModel, this );
					}
				});
			});
		});
	}
	else
	{
		console.log( 'Bindings not applied because knockout was not found.' );
	}
	
})( jQuery );
 