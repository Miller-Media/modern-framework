<?php
/**
 * Annotation Class
 *
 * Created:    Nov 20, 2016
 *
 * @package   Modern Wordpress Framework
 * @author    Kevin Carwile
 * @since     0.1.2
 */

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Provides placeholder methods for annotations.
 */
abstract class Annotation
{
	/**
	 * Apply to Object
	 *
	 * @param	object		$instance		The object which is documented with this annotation
	 * @param	array		$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToObject( $instance, $vars )
	{
		
	}
	
	/**
	 * Apply to Property
	 *
	 * @param	object					$instance		The object that the property belongs to
	 * @param	ReflectionProperty		$property		The reflection property of the object instance
	 * @param	array					$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToProperty( $instance, $property, $vars )
	{	
	
	}
	
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
		
	}	
	
}
