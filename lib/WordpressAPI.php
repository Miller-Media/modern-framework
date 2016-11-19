<?php

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

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
			
		/**
		 * Class
		 */
		$classAnnotations = $this->reader->getClassAnnotations( $reflClass );
		foreach( $classAnnotations as $annotation )
		{
			/* Wordpress Options Page */
			if ( $annotation instanceof \Wordpress\Options )
			{
				if ( $instance instanceof \Modern\Wordpress\Plugin\Settings )
				{
					$menu 	= $annotation->menu ?: $instance->getPlugin()->name;
					$title 	= $annotation->title ?: $menu . ' ' . __( 'Options' );
					$capability = $annotation->capability;
					$page_id = $instance->id;
					
					add_action( 'admin_menu', function() use ( $title, $menu, $capability, $page_id, $instance, $annotation )
					{
						add_options_page( $title, $menu, $capability, $page_id, function() use ( $title, $page_id )
						{
							echo '<div class="wrap">' .
									'<h1>' . $title . '</h1>' .
									'<form action="options.php" method="post">';
									settings_fields( $page_id );
									do_settings_sections( $page_id );
									submit_button();
							echo 	'</form>' .
								 '</div>';
						});
					});
					
					add_action( 'admin_init', function() use ( $page_id, $instance, $annotation )
					{
						register_setting( $page_id, $page_id, array( $instance, 'validate' ) );
					});
				}
			}
			
			else if ( $annotation instanceof \Wordpress\Options\Section )
			{
				if ( $instance instanceof \Modern\Wordpress\Plugin\Settings and isset( $page_id ) )
				{
					$section_id = md5( $annotation->title );
					add_action( 'admin_init', function() use ( $section_id, $page_id, $annotation )
					{
						add_settings_section( $section_id, $annotation->title, function() use ( $annotation ) { return $annotation->description; }, $page_id );
					});
				}
			}
			
			else if ( $annotation instanceof \Wordpress\Options\Field )
			{
				if ( $instance instanceof \Modern\Wordpress\Plugin\Settings and isset( $page_id ) and isset( $section_id ) )
				{
					add_action( 'admin_init', function() use ( $page_id, $section_id, $annotation, $instance )
					{
						add_settings_field( md5( $page_id . $annotation->name ), $annotation->title, function() use ( $page_id, $section_id, $annotation, $instance )
						{
							echo call_user_func( array( $annotation, 'getFieldHtml' ), $page_id, $instance->getSetting( $annotation->name ) );
						}
						, $page_id, $section_id );
					});
				}
			}
		}
		
		/**
		 * Properties
		 */
		foreach ( $reflClass->getProperties() as $property ) 
		{
			$propAnnotations = $this->reader->getPropertyAnnotations( $property );
			
			foreach ( $propAnnotations as $annotation ) 
			{
				/* Wordpress Post Type */
				if ( $annotation instanceof \Wordpress\PostType )
				{
					add_action( 'init', function() use ( $annotation, $instance, $property )
					{
						/* Register Post Type */
						register_post_type( $annotation->name, $instance->{$property->name} );
						
						/* Add Post Type Support */
						if ( ! empty( $annotation->supports ) )
						{
							add_post_type_support( $annotation->name, $annotation->supports );
						}
					});
				}
			}
		}		
		
		/**
		 * Methods
		 */
		foreach ( $reflClass->getMethods() as $method ) 
		{
			$methodAnnotations = $this->reader->getMethodAnnotations( $method );
			
			foreach ( $methodAnnotations as $annotation ) 
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

			}
		}
		
		return $this;
	}
	
}