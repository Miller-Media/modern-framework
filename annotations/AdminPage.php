<?php
/**
 * Annotation: Wordpress\AdminPage
 *
 * Created:    Nov 20, 2016
 *
 * @package    Modern Wordpress Framework
 * @author     Kevin Carwile
 * @since      1.0.0
 */
 
namespace Wordpress;

/**
 * @Annotation 
 * @Target( { "METHOD", "CLASS" } )
 */
class AdminPage extends \Modern\Wordpress\Annotation
{
	/**
	 * @var string
	 * @Required
	 */
	public $title;
	
	/**
	 * @var string
	 * @Required
	 */
	public $menu;
	
	/**
	 * @var string
	 * @Required
	 */
	public $slug;
	
	/**
	 * @var mixed
	 */
	public $capability = 'manage_options';
	
	/**
	 * @var string
	 */
	public $icon = 'none';
	
	/**
	 * @var int
	 */
	public $position;
	
	/**
	 * @var string
	 */
	public $type = 'menu';
	
	/**
	 * @var string
	 */
	public $parent;
	
	/**
	 * Apply to Method
	 *
	 * @param	object					$instance		The object that the method belongs to
	 * @param	ReflectionMethod		$method			The reflection method of the object instance
	 * @param	array					$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToMethod( $instance, $method, $vars )
	{
		$annotation = $this;
		mwp_add_action( 'admin_menu', function() use ( $annotation, $instance, $method )
		{
			$add_page_func = 'add_' . $annotation->type . '_page';
			if ( is_callable( $add_page_func ) )
			{
				if ( $annotation->type == 'submenu' )
				{
					call_user_func( $add_page_func, $annotation->parent, $annotation->title, $annotation->menu, $annotation->capability, $annotation->slug, array( $instance, $method->name ), $annotation->icon, $annotation->position );
				}
				else
				{
					call_user_func( $add_page_func, $annotation->title, $annotation->menu, $annotation->capability, $annotation->slug, array( $instance, $method->name ), $annotation->icon, $annotation->position );
				}
			}
		});
	}
	
	/**
	 * Apply to Object
	 *
	 * @param	object		$instance		The object which is documented with this annotation
	 * @param	array		$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToObject( $instance, $vars )
	{
		$annotation = $this;
		mwp_add_action( 'admin_menu', function() use ( $annotation, $instance )
		{
			$add_page_func = 'add_' . $annotation->type . '_page';
			if ( is_callable( $add_page_func ) )
			{
				// Route to the correct do_{action} on the controller based on the 'do' request param
				$router_callback = function() use ( $instance ) 
				{
					$action = isset( $_REQUEST[ 'do' ] ) ? $_REQUEST[ 'do' ] : 'index';
					if( is_callable( array( $instance, 'do_' . $action ) ) )
					{
						call_user_func( array( $instance, 'do_' . $action ) );
					}
				};
				
				if ( $annotation->type == 'submenu' )
				{
					call_user_func( $add_page_func, $annotation->parent, $annotation->title, $annotation->menu, $annotation->capability, $annotation->slug, $router_callback, $annotation->icon, $annotation->position );
				}
				else
				{
					call_user_func( $add_page_func, $annotation->title, $annotation->menu, $annotation->capability, $annotation->slug, $router_callback, $annotation->icon, $annotation->position );
				}
			}
		});
		
	}
	
}