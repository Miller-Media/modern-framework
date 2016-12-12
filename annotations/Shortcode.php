<?php
/**
 * Annotation: Wordpress\Shortcode  
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
class Shortcode extends \Modern\Wordpress\Annotation
{
	/**
	 * @var string
	 */
	public $name;
	
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
		add_shortcode( $this->name, array( $instance, $method->name ) );		
	}	
	
}