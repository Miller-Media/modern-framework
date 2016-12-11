<?php
/**
 * Annotation: Wordpress\Stylesheet  
 *
 * Created:    Nov 20, 2016
 *
 * @package    Modern Wordpress Framework
 * @author     Kevin Carwile
 * @since      0.1.2
 */

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "PROPERTY" )
 */
class Stylesheet extends \Modern\Wordpress\Annotation
{
	/**
	 * @var array
	 */
	public $deps = array();
	
	/**
	 * @var mixed
	 */
	public $ver = false;
	
	/**
	 * @var string
	 */
	public $media = 'all';
	
	/**
	 * @var boolean
	 */
	public $always = false;
    
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
		if ( $instance instanceof \Modern\Wordpress\Plugin or is_callable( array( $instance, 'getPlugin' ) ) )
		{
			$plugin = ( $instance instanceof \Modern\Wordpress\Plugin ) ? $instance : $instance->getPlugin();
			$fileUrl = $plugin->fileUrl( $instance->{$property->name} );
			wp_register_style( md5( $fileUrl ), $fileUrl, $this->deps, $this->ver, $this->media );
			
			if ( $this->always )
			{
				$plugin->useStyle( $instance->{$property->name} );
			}
		}
	}
	
}