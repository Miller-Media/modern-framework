<?php

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Annotations\FileCacheReader;

/**
 * Framework Class
 */
class Framework extends Plugin
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
		/* Load Annotation Reader */
		$this->reader = new FileCacheReader( new AnnotationReader(), __DIR__ . "/../cache", defined( 'MODERN_WORDPRESS_DEV' ) and MODERN_WORDPRESS_DEV );
		
		/* Init Parent */
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
		$vars = array();
		
		/**
		 * Class Annotations
		 */
		foreach( $this->reader->getClassAnnotations( $reflClass ) as $annotation )
		{
			if ( $annotation instanceof \Modern\Wordpress\Annotation )
			{
				$result = $annotation->applyToObject( $instance, $vars );
				if ( ! empty( $result ) )
				{
					$vars = array_merge( $vars, $result );
				}
			}
		}
		
		/**
		 * Property Annotations
		 */
		foreach ( $reflClass->getProperties() as $property ) 
		{
			foreach ( $this->reader->getPropertyAnnotations( $property ) as $annotation ) 
			{
				if ( $annotation instanceof \Modern\Wordpress\Annotation )
				{
					$result = $annotation->applyToProperty( $instance, $property, $vars );
					if ( ! empty( $result ) )
					{
						$vars = array_merge( $vars, $result );
					}
				}
			}
		}		
		
		/**
		 * Method Annotations
		 */
		foreach ( $reflClass->getMethods() as $method ) 
		{
			foreach ( $this->reader->getMethodAnnotations( $method ) as $annotation ) 
			{
				if ( $annotation instanceof \Modern\Wordpress\Annotation )
				{
					$result = $annotation->applyToMethod( $instance, $method, $vars );
					if ( ! empty( $result ) )
					{
						$vars = array_merge( $vars, $result );
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @Wordpress\Filter( for="cron_schedules" )
	 *
	 * @param	array		$schedules		Array of schedule frequencies
	 * @return	array
	 */
	public function cronSchedules( $schedules )
	{
		$schedules['minutely'] = array(
			'interval' => 60,
			'display' => __( 'Once Per Minute' )
		);
		
		return $schedules;
	}
	
	/**
	 * @Wordpress\Plugin( on="activation", file="framework.php" )
	 *
	 * @return	void
	 */
	public function frameworkActivated()
	{
		wp_clear_scheduled_hook( 'modern_wordpress_task_run' );
		wp_schedule_event( time(), 'minutely', 'modern_wordpress_task_run' );
	}
	
	/**
	 * @Wordpress\Plugin( on="deactivation", file="framework.php" )
	 *
	 * @return	void
	 */
	public function frameworkDeactivated()
	{
		wp_clear_scheduled_hook( 'modern_wordpress_task_run' );
	}
	
	/**
	 * @Wordpress\Action( for="modern_wordpress_task_run" )
	 */
	public function runTasks()
	{
		
	}
	
}