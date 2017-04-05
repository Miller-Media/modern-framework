<?php
/**
 * Framework Class (Singleton)
 * 
 * Created:    Nov 20, 2016
 *
 * @package    Modern Wordpress Framework
 * @author     Kevin Carwile
 * @since      1.0.0
 */

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Doctrine\Common\Annotations\AnnotationReader;
use \Doctrine\Common\Annotations\FileCacheReader;

/**
 * Provides access to core framework methods and features. 
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
		$this->reader = new FileCacheReader( new AnnotationReader(), __DIR__ . "/../annotations/cache", defined( 'MODERN_WORDPRESS_DEV' ) and MODERN_WORDPRESS_DEV );
		
		/* Register WP CLI */
		if ( defined( '\WP_CLI' ) && \WP_CLI ) {
			\WP_CLI::add_command( 'mwp', 'Modern\Wordpress\CLI' );
		}
		
		/* Init Parent */
		parent::__construct();		
	}
	
	/**
	 * Return the database
	 *
	 * @return		wpdb
	 */
	public function db()
	{
		global $wpdb;
		return $wpdb;
	}
	
	/**
	 * Run updates when new plugin version is uploaded
	 *
	 * @Wordpress\Action( for="init" )
	 *
	 * @return	void
	 */
	public function ensureActivated()
	{
		if ( wp_get_schedule( 'modern_wordpress_queue_run' ) == false ) 
		{
			$this->frameworkActivated();
		}
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
	 * Clear annotation reader cache upon plugin updates, etc
	 *
	 * @return	void
	 */
	public function clearAnnotationsCache()
	{
		array_map( 'unlink', glob( __DIR__ . "/../annotations/cache/*" ) );
	}
	
	/**
	 * Initialize other resources before the wordpress init action
	 * 
	 * @Wordpress\Action( for="modern_wordpress_init" )
	 * 
	 * @return	void
	 */
	public function loadOtherResources()
	{
		$form_validators = new \Modern\Wordpress\Helper\Form\Validators;
		$this->attach( $form_validators );		
	}
	
	/**
	 * Register framework resources and dependency chains
	 * 
	 * @Wordpress\Action( for="wp_enqueue_scripts", priority=0 )
	 * @Wordpress\Action( for="admin_enqueue_scripts", priority=0 )
	 * @Wordpress\Action( for="login_enqueue_scripts", priority=0 )
	 */
	public function enqueueScripts()
	{
		wp_register_script( 'knockout', $this->fileUrl( 'assets/js/knockout.min.js' ) );
		wp_register_script( 'knockback', $this->fileUrl( 'assets/js/knockback.min.js' ), array( 'underscore', 'backbone', 'knockout' ) );
		wp_register_script( 'mwp-bootstrap', $this->fileUrl( 'assets/js/mwp.bootstrap.min.js', array( 'jquery' ) ) );
		wp_register_style( 'mwp-bootstrap-theme', $this->getSetting( 'bootstrap_theme' ) ?: $this->fileUrl( 'assets/css/bootstrap-theme.min.css' ) );
		wp_register_style( 'mwp-bootstrap', $this->fileUrl( 'assets/css/mwp-bootstrap.min.css' ) );
		
		wp_register_script( 'mwp', $this->fileUrl( 'assets/js/mwp.framework.js' ), array( 'jquery', 'underscore', 'backbone', 'knockout' ) );
		wp_localize_script( 'mwp', 'mw_localized_data', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		));
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
	 * Add a one minute time period to the wordpress cron schedule
	 *
	 * @Wordpress\Filter( for="cron_schedules" )
	 *
	 * @param	array		$schedules		Array of schedule frequencies
	 * @return	array
	 */
	public function cronSchedules( $schedules )
	{
		$schedules[ 'minutely' ] = array(
			'interval' => 60,
			'display' => __( 'Once Per Minute' )
		);
		
		return $schedules;
	}
	
	/**
	 * Setup the queue schedule on framework activation
	 *
	 * @Wordpress\Plugin( on="activation", file="plugin.php" )
	 *
	 * @return	void
	 */
	public function frameworkActivated()
	{
		wp_clear_scheduled_hook( 'modern_wordpress_queue_run' );
		wp_clear_scheduled_hook( 'modern_wordpress_queue_maintenance' );
		wp_schedule_event( time(), 'minutely', 'modern_wordpress_queue_run' );
		wp_schedule_event( time(), 'hourly', 'modern_wordpress_queue_maintenance' );
	}
	
	/**
	 * Clear the queue schedule on framework deactivation
	 *
	 * @Wordpress\Plugin( on="deactivation", file="plugin.php" )
	 *
	 * @return	void
	 */
	public function frameworkDeactivated()
	{
		wp_clear_scheduled_hook( 'modern_wordpress_queue_run' );
		wp_clear_scheduled_hook( 'modern_wordpress_queue_maintenance' );
	}
	
	/**
	 * Run any queued tasks
	 *
	 * @Wordpress\Action( for="modern_wordpress_queue_run" )
	 *
	 * @return	void
	 */
	public function runTasks()
	{		
		$db = $this->db();
		$begin_time = time();
		$max_execution_time = ini_get( 'max_execution_time' );
		
		/* Attempt to increase execution time if it is set to less than 60 seconds */
		if ( $max_execution_time < 60 ) {
			if ( set_time_limit( 60 ) ) {
				$max_execution_time = 60;
			}
		}
		
		/* Run tasks */
		while 
		( 
			/* We have a task to run */
			$task = Task::popQueue() and
			
			/* and we have time to run it */
			( time() - $begin_time < $max_execution_time - 10 )
		)
		{
			$data = $task->data;
			$data[ 'status' ] = NULL;
			$task->data = $data;
			$task->last_start = time();
			$task->running = 1;
			$task->save();
			
			if ( has_action( $task->action ) )
			{
				// Allow the task to bootstrap if needed
				$task->setup();
				
				try
				{
					while
					( 
						! $task->completed and ! $task->aborted and             // task is not yet complete
						time() >= $task->next_start and                         // task has not been rescheduled for the future
						( time() - $begin_time < $max_execution_time - 10 )     // there is still time to run it
					)
					{
						$task->execute();
						$task->save();
					}
					
					if ( $task->aborted )
					{
						$task->running = 0;
						$task->fails = 3;
						$task->save();
					}
					else
					{
						$task->running = 0;
						$task->fails = 0;
						$task->save();
					}
				}
				catch( \Exception $e )
				{
					$data = $task->data;
					$data[ 'status' ] = $e->getMessage();
					$task->data = $data;
					$task->save();
				}
			}
		}
	}
	
	/**
	 * Perform task queue maintenance
	 *
	 * @Wordpress\Action( for="modern_wordpress_queue_maintenance" )
	 *
	 * @return	void
	 */
	public function runTasksMaintenance()
	{
		Task::runMaintenance();
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
		$plugin_dir = $data[ 'slug' ];
		$plugin_name = $data[ 'name' ];
		$plugin_vendor = $data[ 'vendor' ];
		$plugin_namespace = $data[ 'namespace' ];
		
		if ( ! $data[ 'slug' ] )      { throw new \InvalidArgumentException( 'Invalid plugin slug.' ); }
		if ( ! $data[ 'name' ] )      { throw new \InvalidArgumentException( 'No plugin name provided.' );  }
		if ( ! $data[ 'vendor' ] )    { throw new \InvalidArgumentException( 'No vendor name provided.' );  }
		if ( ! $data[ 'namespace' ] ) { throw new \InvalidArgumentException( 'No namespace provided.' );    }
		
		if ( ! is_dir( $this->getPath() . '/boilerplate' ) )
		{
			throw new \ErrorException( "Boilerplate plugin not present. Can't create a new one.", 1 );
		}
		
		if ( is_dir( WP_PLUGIN_DIR . '/' . $plugin_dir ) )
		{
			throw new \ErrorException( 'Plugin directory is already being used.', 2 );
		}
		
		$this->copyPluginFiles( $this->getPath() . '/boilerplate', WP_PLUGIN_DIR . '/' . $plugin_dir, $data );
		
		/* Create an alias file for the test suite, etc... */
		file_put_contents( WP_PLUGIN_DIR . '/' . $plugin_dir . '/' . $data[ 'slug' ] . '.php', "<?php\n\nrequire_once 'plugin.php';" );
		
		/* Include autoloader so we can instantiate the plugin */
		include_once WP_PLUGIN_DIR . '/' . $plugin_dir . '/vendor/autoload.php';
		
		$pluginClass = $plugin_namespace . '\Plugin';
		$plugin = $pluginClass::instance();
		$plugin->setPath( WP_PLUGIN_DIR . '/' . $plugin_dir );
		$plugin->setData( 'plugin-meta', $data );
		
		return $this;
	}
	
	/**
	 * Copy boilerplate plugin and customize the metadata
	 *
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @param	    array    $data      Plugin metadata
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	protected function copyPluginFiles( $source, $dest, $data )
	{
		// Simple copy for a file
		if ( is_file( $source ) ) 
		{
			if ( ! in_array( basename( $source ), array( 'README.md', '.gitignore' ) ) )
			{
				copy( $source, $dest );
				
				$pathinfo = pathinfo( $dest );
				if ( isset( $pathinfo[ 'extension' ] ) and in_array( $pathinfo[ 'extension' ], array( 'php', 'js', 'json', 'css' ) ) )
				{
					file_put_contents( $dest, $this->replaceMetaContents( file_get_contents( $dest ), $data ) );
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
				$this->copyPluginFiles( "$source/$entry", "$dest/$entry", $data );
			}
		}

		// Clean up
		$dir->close();
		return true;
	}
	
	/**
	 * Create new javascript module
	 *
	 * @param	string		$slug		The plugin slug
	 * @param	string		$name		The javascript module name
	 * @return	void
	 * @throws	\ErrorException
	 */
	public function createJavascript( $slug, $name )
	{
		if ( ! file_exists( WP_PLUGIN_DIR . '/modern-framework/boilerplate/assets/js/main.js' ) )
		{
			throw new \ErrorException( "The boilerplate plugin is not present. \nTry using: $ wp mwp update-boilerplate" );
		}
		
		if ( ! is_dir( WP_PLUGIN_DIR . '/' . $slug . '/assets/js' ) )
		{
			throw new \ErrorException( 'Javascript directory is not valid: ' . $slug . '/assets/js' );
		}
		
		if ( substr( $name, -3 ) === '.js' )
		{
			$name = substr( $name, 0, strlen( $name ) - 3 );
		}
		
		$javascript_file = WP_PLUGIN_DIR . '/' . $slug . '/assets/js/' . $name . '.js';
		
		if ( file_exists( $javascript_file ) )
		{
			throw new \ErrorException( "The javascript file already exists: " . $slug . '/assets/js/' . $name . '.js' );
		}
		
		if ( ! copy( WP_PLUGIN_DIR . '/modern-framework/boilerplate/assets/js/main.js', $javascript_file ) )
		{
			throw new \ErrorException( 'Error copying file to destination: ' . $slug . '/assets/js/' . $name . '.js' );
		}
		
		$plugin_data_file = WP_PLUGIN_DIR . '/' . $slug . '/data/plugin-meta.php';
		
		if ( file_exists( $plugin_data_file ) )
		{
			$plugin_data = json_decode( include $plugin_data_file, TRUE );
			file_put_contents( $javascript_file, $this->replaceMetaContents( file_get_contents( $javascript_file ), $plugin_data ) );
		}	
	}
	
	/**
	 * Create new stylesheet
	 *
	 * @param	string		$slug		The plugin slug
	 * @param	string		$name		The stylesheet name
	 * @return	void
	 * @throws	\ErrorException
	 */
	public function createStylesheet( $slug, $name )
	{
		if ( ! file_exists( WP_PLUGIN_DIR . '/modern-framework/boilerplate/assets/css/style.css' ) )
		{
			throw new \ErrorException( "The boilerplate plugin is not present. \nTry using: $ wp mwp update-boilerplate" );
		}
		
		if ( ! is_dir( WP_PLUGIN_DIR . '/' . $slug . '/assets/css' ) )
		{
			throw new \ErrorException( 'Stylesheet directory is not valid: ' . $slug . '/assets/css' );
		}
		
		if ( substr( $name, -4 ) === '.css' )
		{
			$name = substr( $name, 0, strlen( $name ) - 4 );
		}
		
		$stylesheet_file = WP_PLUGIN_DIR . '/' . $slug . '/assets/css/' . $name . '.css';
		
		if ( file_exists( $stylesheet_file ) )
		{
			throw new \ErrorException( "The stylesheet file already exists: " . $slug . '/assets/css/' . $name . '.css' );
		}
		
		if ( ! copy( WP_PLUGIN_DIR . '/modern-framework/boilerplate/assets/css/style.css', $stylesheet_file ) )
		{
			throw new \ErrorException( 'Error copying file to destination: ' . $slug . '/assets/css/' . $name . '.css' );
		}
		
		$plugin_data_file = WP_PLUGIN_DIR . '/' . $slug . '/data/plugin-meta.php';
		
		if ( file_exists( $plugin_data_file ) )
		{
			$plugin_data = json_decode( include $plugin_data_file, TRUE );
			file_put_contents( $stylesheet_file, $this->replaceMetaContents( file_get_contents( $stylesheet_file ), $plugin_data ) );
		}	
	}

	/**
	 * Create new template snippet
	 *
	 * @param	string		$slug		The plugin slug
	 * @param	string		$name		The template name
	 * @return	void
	 * @throws	\ErrorException
	 */
	public function createTemplate( $slug, $name )
	{
		if ( ! file_exists( WP_PLUGIN_DIR . '/modern-framework/boilerplate/templates/snippet.php' ) )
		{
			throw new \ErrorException( "The boilerplate plugin is not present. \nTry using: $ wp mwp update-boilerplate" );
		}
		
		if ( ! is_dir( WP_PLUGIN_DIR . '/' . $slug . '/templates' ) )
		{
			throw new \ErrorException( 'Template directory is not valid: ' . $slug . '/templates' );
		}
		
		if ( substr( $name, -4 ) === '.php' )
		{
			$name = substr( $name, 0, strlen( $name ) - 4 );
		}
		
		$template_file = WP_PLUGIN_DIR . '/' . $slug . '/templates/' . $name . '.php';
		
		if ( file_exists( $template_file ) )
		{
			throw new \ErrorException( "The template file already exists: " . $slug . '/templates/' . $name . '.php' );
		}
		
		$parts = explode( '/', $name );		
		$basedir = WP_PLUGIN_DIR . '/' . $slug . '/templates';
		$filename = array_pop( $parts );
		foreach( $parts as $dir )
		{
			$basedir .= '/' . $dir;
			if ( ! is_dir( $basedir ) )
			{
				mkdir( $basedir );
			}
		}
		
		if ( ! copy( WP_PLUGIN_DIR . '/modern-framework/boilerplate/templates/snippet.php', $template_file ) )
		{
			throw new \ErrorException( 'Error copying file to destination: ' . $slug . '/templates/' . $name . '.php' );
		}
		
		$template_contents = file_get_contents( $template_file );
		$template_contents = str_replace( "'snippet'", "'{$name}'", $template_contents );

		$plugin_data_file = WP_PLUGIN_DIR . '/' . $slug . '/data/plugin-meta.php';		
		if ( file_exists( $plugin_data_file ) )
		{
			$plugin_data = json_decode( include $plugin_data_file, TRUE );
			$template_contents = $this->replaceMetaContents( $template_contents, $plugin_data );
		}
		
		file_put_contents( $template_file, $template_contents );
	}
	
	/**
	 * Create new php class
	 *
	 * @param	string		$slug		The plugin slug
	 * @param	string		$name		The php classname
	 * @return	void
	 * @throws	\ErrorException
	 */
	public function createClass( $slug, $name )
	{
		$plugin_data_file = WP_PLUGIN_DIR . '/' . $slug . '/data/plugin-meta.php';
		
		if ( ! file_exists( $plugin_data_file ) )
		{
			throw new \ErrorException( "No metadata available for this plugin. Namespace unknown." );
		}
		
		$plugin_data = json_decode( include $plugin_data_file, TRUE );

		if ( ! isset( $plugin_data[ 'namespace' ] ) )
		{
			throw new \ErrorException( "Namespace not defined in the plugin metadata." );
		}
		
		$namespace = $plugin_data[ 'namespace' ];
		$name = trim( str_replace( $namespace, '', $name ), '\\' );
		$parts = explode( '\\', $name );
		$classname = array_pop( $parts );
		
		if ( ! is_dir( WP_PLUGIN_DIR . '/' . $slug . '/classes' ) )
		{
			throw new \ErrorException( 'Class directory is not valid: ' . 'plugins/' . $slug . '/classes' );
		}
		
		$basedir = WP_PLUGIN_DIR . '/' . $slug . '/classes';
		foreach( $parts as $dir )
		{
			$basedir .= '/' . $dir;
			if ( ! is_dir( $basedir ) )
			{
				mkdir( $basedir );
			}
			$namespace .= '\\' . $dir;
		}
		
		$class_file = $basedir . '/' . $classname . '.php';
		
		if ( file_exists( $class_file ) )
		{
			throw new \ErrorException( "The class file already exists: " . str_replace( WP_PLUGIN_DIR, '', $class_file ) );
		}
		
		$version_tag = '{' . 'build_version' . '}';
		
		$class_contents = <<<CLASS
<?php
/**
 * Plugin Class File
 *
 * Created:   {date_time}
 *
 * @package:  {plugin_name}
 * @author:   {plugin_author}
 * @since:    $version_tag
 */
namespace $namespace;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * $classname Class
 */
class $classname
{
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected \$plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
	 */
	public function getPlugin()
	{
		return \$this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \Modern\Wordpress\Plugin \$plugin=NULL )
	{
		\$this->plugin = \$plugin;
		return \$this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	\$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \Modern\Wordpress\Plugin \$plugin=NULL )
	{
		\$this->plugin = \$plugin ?: \MillerMedia\Boilerplate\Plugin::instance();
	}
}

CLASS;
		file_put_contents( $class_file, $this->replaceMetaContents( $class_contents, $plugin_data ) );
	
	}

	/**
	 * Replace meta contents
	 *
	 * @param	string		$source		The source code to replace meta contents in
	 * @param	array		$data		Plugin meta data
	 * @return	string
	 */
	public function replaceMetaContents( $source, $data )
	{
		$data = array_merge( array( 
			'name' => '',
			'description' => '',
			'namespace' => '',
			'slug' => '',
			'vendor' => '',
			'author' => '',
			'author_url' => '',
			'date' => date( 'F j, Y' ),
			), $data );
			
		return strtr( $source, array
		( 
			'b7f88d4569eea7ab0b52f6a8c0e0e90c'  => md5( $data[ 'slug' ] ),
			'MillerMedia\Boilerplate'           => $data[ 'namespace' ],
			'MillerMedia\\\Boilerplate'         => str_replace( '\\', '\\\\', $data[ 'namespace' ] ),
			'millermedia/boilerplate'           => strtolower( str_replace( '\\', '/', $data[ 'namespace' ] ) ),
			'BoilerplatePlugin'                 => str_replace( '\\', '', $data[ 'namespace'] ) . 'Plugin',
			'{vendor_name}'                     => $data[ 'vendor' ],
			'{plugin_name}'                     => $data[ 'name' ],
			'{plugin_slug}'                     => $data[ 'slug' ],
			'{plugin_description}'              => $data[ 'description' ],
			'{plugin_dir}'                      => $data[ 'slug' ],
			'{plugin_author}'                   => $data[ 'author' ],
			'{plugin_author_url}'               => $data[ 'author_url' ],
			'{date_time}'                       => $data[ 'date' ],						
		) );
	}

}
