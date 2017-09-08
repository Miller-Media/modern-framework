/**
 * Plugin Javascript Module
 *
 * Created     December 30, 2016
 *
 * @package    Modern Framework for Wordpress
 * @author     Kevin Carwile
 * @since      1.0.3
 */
;(function( $, undefined ) {
	
	"use strict";

	window.mwp = _.extend( {}, Backbone.Events );

	var controllers = {};
	var models = {};
	var collections = {};
	var local = mw_localized_data;
	
	var BaseModel = Backbone.Model.extend(
	{
		/**
		 * Return self for knockback binding compatibility
		 *
		 * @return	this
		 */
		model: function() {
			return this;
		}
	},
	{
		/**
		 * Allow model methods to be overridden
		 *
		 * When a method is overridden, the overriding function will be passed the function it is overriding as its
		 * first parameter, which can be called to retrieve the value of the overridden (parent) method.
		 *
		 * @param	string|object		method			The method name to override, or an object with properties corresponding to methods to override
		 * @param	function			newMethod		The new function to override the method with
		 * @return	void
		 */
		override: function( method, newMethod )
		{
			var self = this;
			
			if ( typeof method == 'object' ) {
				$.each( method, function( method, newMethod ) {
					self.override( method, newMethod );
				});
			}
			else if ( typeof method == 'string' ) {
				var parentMethod = this.prototype[method] || function(){};
				this.prototype[method] = function() {
					return _.wrap( _.bind( parentMethod, this ), _.bind( newMethod, this ) ).apply( this, arguments );
				};
			}
		}
	});
	
	/**
	 * Base Classes
	 */
	mwp.classes = {
	
		/**
		 * Model
		 */
		Model: BaseModel,
		
		/**
		 * Controller (singleton)
		 */
		Controller: BaseModel.extend({
		
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
				controller.local = $.extend( {}, local, mw_localized_data );
				
				$(document).ready( function() {
					mwp.trigger( controller.get( 'name' ) + '.ready', controller );
					if( typeof controller.init == 'function' ) {
						controller.init();
						controller.viewModel._controller = controller;
						mwp.trigger( controller.get( 'name' ) + '.init', controller );
					}
				});
			}
		}),
		
		/**
		 * Collection
		 */
		Collection: Backbone.Collection.extend({
		
		})
		
	};
	
	/**
	 * Controller Registration
	 */
	mwp.controller = _.extend( function( name, properties, classProperties ) 
	{
		if ( name in controllers ) {
			throw new Error( 'Controller already exists. Use mwp.controller.get( name ) to get a controller' );
		}
		var controller = mwp.classes.Controller.extend( properties, classProperties );
		controllers[ name ] = new controller( { name: name } );
		return controllers[ name ];	
	}, 
	{
		/**
		 * Get a controller
		 *
		 * @param	string				name		The name of a registered controller
		 * @return	Backbone.Model
		 */
		get: function( name )
		{
			if ( typeof controllers[ name ] !== 'undefined' ) {
				return controllers[ name ];
			}
			
			return undefined;
		}
	});
	
	/**
	 * Model Registration
	 */
	mwp.model = _.extend( function( name, properties, classProperties )
	{
		models[ name ] = mwp.classes.Model.extend( properties, classProperties );
		return models[ name ];
	},
	{
		/**
		 * Get a model
		 *
		 * @param	string				name		The name of a registered model
		 * @return	Backbone.Model
		 */
		get: function( name )
		{
			if ( typeof models[ name ] !== 'undefined' ) {
				return models[ name ];
			}
			
			return undefined;
		},
		
		/**
		 * Set a model
		 *
		 * @param	string				name			The registered name
		 * @param	Backbone.Model		model			The model
		 * @return	Backbone.Model
		 */
		set: function( name, model )
		{
			models[ name ] = model;
			return model;
		}
	});
	
	/**
	 * Collection Registration
	 */
	mwp.collection = _.extend( function( name, properties, classProperties )
	{
		collections[ name ] = mwp.classes.Collection.extend( properties, classProperties );
		return collections[ name ];
	},
	{
		/**
		 * Get a collection
		 *
		 * @param	string				name		The name of a registered model
		 * @return	Backbone.Model
		 */
		get: function( name )
		{
			if ( typeof collections[ name ] !== 'undefined' ) {
				return collections[ name ];
			}
			
			return undefined;
		},
		
		/**
		 * Set a collection
		 *
		 * @param	string					name		The registered name
		 * @param	Backbone.Collection		model
		 * @return	Backbone.Collection
		 */
		set: function( name, collection )
		{
			collections[ name ] = collection;
			return collection
		}
	});
	
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
 