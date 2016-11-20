<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class Plugin extends \Modern\Wordpress\Annotation
{
	/**
	 * @var string
	 * @Required
	 */
	public $on;
	
	/**
	 * @var string
	 * @Required
	 */
	public $file; 
	 
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
		if ( $instance instanceof \Modern\Wordpress\Plugin or is_callable( array( $instance, 'getPlugin' ) ) )
		{
			$plugin = ( $instance instanceof \Modern\Wordpress\Plugin ) ? $instance : $instance->getPlugin();
			
			switch( $this->on )
			{
				case 'activation':
					register_activation_hook( $plugin->getPath() . '/' . $this->file, array( $instance, $method->name ) );
					break;
					
				case 'deactivation':
					register_deactivation_hook( $plugin->getPath() . '/' . $this->file, array( $instance, $method->name ) );
					break;
				
				case 'uninstall':
					register_uninstall_hook( $plugin->getPath() . '/' . $this->file, array( $instance, $method->name ) );
					break;
			}
		}
	}
	
}