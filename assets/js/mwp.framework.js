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

window.mwp = _.extend( {}, Backbone.Events );

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
					mwp.trigger( controller.get( 'name' ) + '.ready', controller );
					if( typeof controller.init == 'function' ) {
						controller.init();
						mwp.trigger( controller.get( 'name' ) + '.init', controller );
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
		controllers[ name ] = new controller( { name: name } );
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
	
	/**
	 * Apply view models to a new scope
	 *
	 * @param	object		scope			DOM Scope to apply views to
	 */
	mwp.applyViews = function( scope )
	{
		if ( typeof ko !== 'undefined' )
		{
			var views = $(scope).find('[data-view-model]').addBack('[data-view-model]');
			
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
				if ( typeof controller !== 'undefined' && typeof controller.viewModel !== 'undefined' ) {
					ko.applyBindings( controller.viewModel, this );
				}
			});
		}
	}
	
	if ( typeof ko !== 'undefined' )
	{
		ko.bindingHandlers.stopBinding = {
			init: function() {
				return { controlsDescendantBindings: true };
			}
		};

		ko.virtualElements.allowedBindings.stopBinding = true;
		
		$(document).ready( function() {			
			/* Double ready means that this will be executed last */
			$(document).ready( function() {
				mwp.applyViews( document );
			});
		});
	}
	else
	{
		console.log( 'Bindings not available because knockout was not found.' );
	}
	
	$(document).ready( function() {
		mwp.trigger( 'mwp.ready' );
	});
	
})( jQuery );
 