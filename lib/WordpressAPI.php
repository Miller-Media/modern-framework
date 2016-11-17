<?php

namespace Modern\Wordpress;

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Annotations\FileCacheReader;

use \Modern\Wordpress\Pattern\Singleton;

/**
 * Wordpress API Class
 */
class WordpressAPI extends Singleton
{

	/**
	 * Instance Cache - Required for all singleton subclasses
	 *
	 * @var	self
	 */
	protected static $_instance;
	
	/** 
	 * @var Annotations Reader
	 */
	protected $reader;
	
	/**
	 * Constructor
	 */
	protected function __construct()
	{
		$this->reader = new FileCacheReader( new AnnotationReader(), __DIR__ . "/../cache", defined( 'MODERN_WORDPRESS_DEBUG' ) and MODERN_WORDPRESS_DEBUG );
		parent::__construct();
	}
	
	/**
	 * Attach instance methods to wordpress api
	 *
	 * @param	object		$instance		An object instance to attach to wordpress 
	 * @return	object
	 */
	public function attach( $instance )
	{
		$reflClass = new \ReflectionClass( get_class( $instance ) );

		foreach ( $reflClass->getMethods() as $method ) 
		{
			$methodAnnotations = $this->reader->getMethodAnnotations( $method );
			
			foreach ( $methodAnnotations AS $annotation ) 
			{
				/* Wordpress Action */
			    if ( $annotation instanceof \Wordpress\Action ) 
			    {
					add_action( $annotation->for, array( $instance, $method->name ), $annotation->priority, $annotation->args );
			    } 
				
				/* Wordpress Filter */
			    else if ( $annotation instanceof \Wordpress\Filter ) 
			    {
					add_filter( $annotation->for, array( $instance, $method->name ), $annotation->priority, $annotation->args );
			    }
				
				/* Wordpress Shortcode */
				else if ( $annotation instanceof \Wordpress\Shortcode )
				{
					add_shortcode( $annotation->name, array( $instance, $method->name ) );
				}
				
				/* Wordpress Post Type */
				else if ( $annotation instanceof \Wordpress\PostType )
				{
					add_action( 'init', function() use ( $annotation, $instance, $method )
					{
						/* Register Post Type */
						register_post_type( $annotation->name, call_user_func( array( $instance, $method->name ) ) );
						
						/* Add Post Type Support */
						if ( ! empty( $annotation->supports ) )
						{
							add_post_type_support( $annotation->name, $annotation->supports );
						}
					});
				}
			}
		}
		
		return $instance;
	}
	
}