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
		
		/* Register WP CLI */
		if ( defined( '\WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'mwp', 'Modern\Wordpress\CLI' );
		}
		
		/* Init Parent */
		parent::__construct();		
	}
	
	/**
	 * Attach instances to wordpress
	 *
	 * @api
	 *
	 * @param	object		$instance		An object instance to attach to wordpress 
	 * @return	this
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
	 * Get all modern wordpress plugins
	 *
	 * @api
	 *
	 * @param	bool		$recache		Force recaching of plugins
	 * @return	array
	 */
	public function getPlugins( $recache=FALSE )
	{
		static $plugins;
		
		if ( ! isset( $plugins ) or $recache )
		{
			$plugins = apply_filters( 'modern_wordpress_find_plugins', array() );
		}
		
		return $plugins;
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
	
	/**
	 * Generate a new plugin from the boilerplate
	 *
	 * @api
	 *
	 * @param	array		$data		New plugin data
	 * @return	this
	 * @throws	\InvalidArgumentException	Throws exception when invalid plugin data is provided
	 * @throws	\ErrorException			Throws an error when the plugin data conflicts with another plugin
	 */
	public function createPlugin( $data )
	{
		$plugin_dir = $data[ 'dir' ];
		$plugin_name = $data[ 'name' ];
		$plugin_vendor = $data[ 'vendor' ];
		$plugin_namespace = $data[ 'namespace' ];
		
		if ( ! isset( $data[ 'date' ] ) )
		{
			$data[ 'date' ] = date( 'M j, Y' );
		}
		
		if ( ! $data[ 'dir' ] )       { throw new \InvalidArgumentException( 'Invalid plugin directory.' ); }
		if ( ! $data[ 'name' ] )      { throw new \InvalidArgumentException( 'No plugin name provided.' );  }
		if ( ! $data[ 'vendor' ] )    { throw new \InvalidArgumentException( 'No vendor name provided.' );  }
		if ( ! $data[ 'namespace' ] ) { throw new \InvalidArgumentException( 'No namespace provided.' );    }
		
		if ( ! is_dir( $this->getPath() . '/boilerplate' ) )
		{
			throw new \ErrorException( "Boilerplate plugin not present. Can't create a new one." );
		}
		
		if ( is_dir( WP_PLUGIN_DIR . '/' . $plugin_dir ) )
		{
			throw new \ErrorException( 'Plugin directory is already being used.' );
		}
		
		$this->copyPlugin( $this->getPath() . '/boilerplate', WP_PLUGIN_DIR . '/' . $plugin_dir, $data );
		
		return $this;
	}
	
	/**
	 * Copy boilerplate plugin and customize the metadata
	 *
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	protected function copyPlugin( $source, $dest, $data )
	{
		// Simple copy for a file
		if ( is_file( $source ) ) 
		{
			if ( ! in_array( basename( $source ), array( 'README.md', '.gitignore' ) ) )
			{
				copy( $source, $dest );
				
				$pathinfo = pathinfo( $dest );
				if ( in_array( $pathinfo[ 'extension' ], array( 'php', 'js', 'json' ) ) )
				{
					$file_contents = file_get_contents( $dest );
					$file_contents = strtr( $file_contents, array
					( 
						'b7f88d4569eea7ab0b52f6a8c0e0e90c'  => md5( $data[ 'dir' ] ),
						'MillerMedia\Boilerplate'           => $data[ 'namespace' ],
						'MillerMedia\\\Boilerplate'         => str_replace( '\\', '\\\\', $data[ 'namespace' ] ),
						'millermedia/boilerplate'           => strtolower( str_replace( '\\', '/', $data[ 'namespace' ] ) ),
						'BoilerplatePlugin'                 => str_replace( '\\', '', $data[ 'namespace'] ) . 'Plugin',
						'{vendor_name}'                     => $data[ 'vendor' ],
						'{plugin_name}'                     => $data[ 'name' ],
						'{plugin_description}'              => $data[ 'description' ],
						'{plugin_dir}'                      => $data[ 'dir' ],
						'{plugin_author}'                   => $data[ 'author' ],
						'{plugin_author_url}'               => $data[ 'author_url' ],
						'{date_time}'                       => $data[ 'date' ],						
					) );
					file_put_contents( $dest, $file_contents );
				}
				
				return true;
			}
			
			return false;
		}

		// Make destination directory
		if ( ! is_dir( $dest ) ) 
		{
			mkdir( $dest );
		}

		// Loop through the folder
		$dir = dir( $source );
		while ( false !== $entry = $dir->read() ) 
		{
			// Skip pointers & special dirs
			if ( in_array( $entry, array( '.', '..', '.git' ) ) )
			{
				continue;
			}

			// Deep copy directories
			if ( $dest !== "$source/$entry" ) 
			{
				$this->copyPlugin( "$source/$entry", "$dest/$entry", $data );
			}
		}

		// Clean up
		$dir->close();
		return true;
	} 	
	
}