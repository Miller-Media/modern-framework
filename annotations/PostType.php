<?php
/**
 * Annotation: Wordpress\PostType  
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
 * @Target( "PROPERTY" )
 */
class PostType extends \Modern\Wordpress\Annotation
{
    /**
     * @var string
     */
    public $name;
	
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
		$self = $this;
		add_action( 'init', function() use ( $self, $instance, $property )
		{
			/* Register Post Type */
			register_post_type( $self->name, $instance->{$property->name} );			
		});	
	}
	
}