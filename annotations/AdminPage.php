<?php
/**
 * Annotation: Wordpress\Action  
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
 * @Target( "METHOD" )
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
	
}