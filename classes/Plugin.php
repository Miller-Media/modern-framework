<?php
/**
 * Plugin Base Class (Singleton)
 *
 * Created:    Nov 20, 2016
 *
 * @package   Modern Wordpress Framework
 * @author    Kevin Carwile
 * @since     1.0.0
 */

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Pattern\Singleton;
use Modern\Wordpress\Framework;

/**
 * All modern wordpress plugins should extend this class.
 */
abstract class Plugin extends Singleton
{
	/**
	 * @var string 	Plugin path
	 */
	protected $path;
	
	/**
	 * @var array 	Settings cache
	 */
	protected $settings = array();
	
	/**
	 * @var array	Script Handles Cache
	 */
	public static $scriptHandles = array();	 
	
	/**
	 * @var array	Script Handles Cache
	 */
	public static $styleHandles = array();
	
	/** 
	 * Set the base plugin path
	 *
	 * @api
	 *
	 * @param	string		$path		The plugin base path
	 * @return	void
	 */
	public function setPath( $path )
	{
		$this->path = $path;
	}
	
	/**
	 * Get the base plugin path
	 *
	 * @api
	 *
	 * @return	string
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
	 */
	public function getPlugin()
	{
		return $this;
	}
	
	/**
	 * Check if plugin version has been updated
	 *
	 * @Wordpress\Action( for="init" )
	 *
	 * @return	void
	 */
	public function _versionUpdateCheck()
	{
		if ( ! Framework::instance()->isDev() )
		{
			$plugin_meta = $this->data( 'plugin-meta' );
			if ( is_array( $plugin_meta ) and isset( $plugin_meta[ 'version' ] ) and $plugin_meta[ 'version' ] )
			{
				$install = $this->data( 'install-meta' );
				if ( ! is_array( $install ) or version_compare( $install[ 'version' ], $plugin_meta[ 'version' ] ) == -1 )
				{
					update_site_option( 'mwp_cache_latest', time() );
					$this->versionUpdated();
				}
			}
		}
	}
	
	/**
	 * Run updates when new plugin version is uploaded
	 *
	 * @return	void
	 */
	public function versionUpdated()
	{
		$plugin_meta = $this->data( 'plugin-meta' );
		$build_meta = $this->data( 'build-meta' );
		$install = $this->data( 'install-meta' ) ?: array();
		
		/* Update table definitions in database if needed */
		$this->updateSchema();
		
		/* Update installed version number */
		$install[ 'version' ] = $plugin_meta[ 'version' ];
		
		/* Update install meta */
		$this->setData( 'install-meta', $install );
		
		/* Clear the annotations cache */
		Framework::instance()->clearAnnotationsCache();
	}

	/**
	 * Update the schema for this plugin
	 * 
	 * @param	bool		$execute		Whether to execute the database changes or not
	 * @return	array						Strings containing the results of the various update queries
	 */
	public function updateSchema( $execute=TRUE )
	{
		global $wpdb;
		$build_meta = $this->data( 'build-meta' );
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$dbHelper = \Modern\Wordpress\DbHelper::instance();
		
		$delta_updates = array();
		
		/* Update global table definitions in database if needed */
		if ( isset( $build_meta[ 'tables' ] ) and is_array( $build_meta[ 'tables' ] ) )
		{
			foreach( $build_meta[ 'tables' ] as $table )
			{
				$tableSql = $dbHelper->buildTableSQL( $table, FALSE );
				$updates = dbDelta( $tableSql, $execute );
				if ( $updates ) {
					$delta_updates[ $wpdb->base_prefix . $table['name'] ] = $updates;
				}
			}
		}
		
		/* Update multisite specific table definitions in database if needed */
		if ( isset( $build_meta[ 'ms_tables' ] ) and is_array( $build_meta[ 'ms_tables' ] ) )
		{
			if ( function_exists( 'is_multisite' ) and is_multisite() )
			{
				// Update tables in site specific contexts
				$sites_func = function_exists( 'get_sites' ) ? 'get_sites' : 'wp_get_sites';
				if ( function_exists( $sites_func ) )
				{
					require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
					
					$sites = call_user_func( $sites_func );
					$plugin_path = $this->pluginSlug() . '/plugin.php';
					
					foreach( $sites as $site )
					{
						$site_data = (array) $site;
						$site_id = (int) $site_data['blog_id'];
						switch_to_blog( $site_id );
						
						if ( is_plugin_active_for_network( $plugin_path ) or is_plugin_active( $plugin_path ) )
						{
							// Create tables for this site
							foreach( $build_meta[ 'ms_tables' ] as $table )
							{
								$tableSql = $dbHelper->buildTableSQL( $table, TRUE );
								$updates = dbDelta( $tableSql, $execute );
								if ( $updates ) {
									$delta_updates[ $wpdb->prefix . $table ] = $updates;
								}
							}
						}
						
						restore_current_blog();
					}
				}
			}
			else
			{
				// Update tables under global context
				foreach( $build_meta[ 'ms_tables' ] as $table )
				{
					$tableSql = $dbHelper->buildTableSQL( $table, FALSE );
					$updates = dbDelta( $tableSql, $execute );
					if ( $updates ) {
						$delta_updates[ $wpdb->base_prefix . $table ] = $updates;
					}					
				}
			}
		}
		
		return $delta_updates;
	}
	
	/**
	 * Uninstall routine
	 *
	 * @return	void
	 */
	public function uninstall()
	{
		$build_meta = $this->data( 'build-meta' ) ?: array();
		
		// Remove global tables on uninstall
		if ( is_array( $build_meta[ 'tables' ] ) )
		{
			foreach( $build_meta[ 'tables' ] as $table )
			{
				$this->db->query( "DROP TABLE IF EXISTS {$this->db->base_prefix}{$table['name']}" );
			}
		}
		
		// Remove multisite specific tables on uninstall
		if ( isset( $build_meta[ 'ms_tables' ] ) and is_array( $build_meta[ 'ms_tables' ] ) )
		{
			if ( function_exists( 'is_multisite' ) and is_multisite() )
			{
				// Update tables in site specific contexts
				$sites_func = function_exists( 'get_sites' ) ? 'get_sites' : 'wp_get_sites';
				if ( function_exists( $sites_func ) )
				{
					$sites = call_user_func( $sites_func );
					
					foreach( $sites as $site )
					{
						$site_data = (array) $site;
						switch_to_blog( (int) $site_data['blog_id'] );
						
						// Drop tables
						foreach( $build_meta[ 'ms_tables' ] as $table )
						{
							$this->db->query( "DROP TABLE IF EXISTS {$this->db->prefix}{$table['name']}" );
						}
						
						restore_current_blog();
					}
				}
			}
			else
			{
				// Update tables under global context
				foreach( $build_meta[ 'ms_tables' ] as $table )
				{
					$this->db->query( "DROP TABLE IF EXISTS {$this->db->base_prefix}{$table['name']}" );
				}
			}
		}		
		
		$this->setData( 'install-meta', NULL );
	}
	
	/**
	 * Add a settings store to the plugin
	 *
	 * @api
	 *
	 * @param	Settings		$settings		The settings object
	 * @return	void
	 */
	public function addSettings( $settings )
	{
		$this->settings[ $settings->key ] = $settings;
		$settings->setPlugin( $this );
	}
	
	/** 
	 * Get one or more plugin settings stores
	 *
	 * @api
	 *
	 * @param	string|NULL		$key		The settings access key
	 * @return	Settings|array|NULL
	 */
	public function getSettings( $key='main' )
	{
		/* Return a specific settings id */
		if ( $key !== NULL )
		{
			if ( isset( $this->settings[ $key ] ) )
			{
				return $this->settings[ $key ];
			}
			
			return NULL;
		}
		
		/* Return all settings */
		return $this->settings;
	}
	
	/**
	 * Get a plugin setting by name (search if necessary)
	 *
	 * @api
	 *
	 * @param	string		$name		Setting name
	 * @param	string		$key		Optional key of settings to look in
	 * @return	mixed|NULL
	 */
	public function getSetting( $name, $key=NULL )
	{
		/* Get from specific settings page */
		if ( $key !== NULL )
		{
			return $this->getSettings( $key )->getSetting( $name );
		}
		
		/* Search all settings */
		foreach( $this->getSettings( NULL ) as $settings )
		{
			$value = $settings->getSetting( $name );
			if ( $value !== NULL )
			{
				return $value;
			}
		}
		
		return NULL;
	}
	
	/**
	 * Set the value of a settings option
	 *
	 * @api
	 *
	 * @param	string		$name			Setting name
	 * @param	mixed		$val			Setting value
	 * @param	string		$key			Settings key
	 * @return	Settings
	 * @throws	ErrorException
	 */
	public function setSetting( $name, $val, $key='main' )
	{
		if ( $settings = $this->getSettings( $key ) )
		{
			$settings->setSetting( $name, $val );
			return $settings;
		}
		else
		{
			throw new ErrorException( 'Invalid settings key "' . $key . '". Does not exist!' );
		}
	}
	
	/**
	 * Get data from persistent store
	 * 
	 * @api
	 *
	 * @param	string		$key		The data key to load
	 * @param	string|NULL	$subdir		The subdirectory to load the data key from or NULL for base /data dir
	 * @return	mixed|NULL
	 */
	public function getData( $key, $subdir=NULL )
	{
		if ( file_exists( $this->getPath() . '/data/' . $key . '.php' ) )
		{
			$data = include $this->getPath() . '/data/' . $key . '.php';
			if ( $data )
			{
				return json_decode( $data, TRUE );
			}
		}
		
		return NULL;
	}
	
	/**
	 * Alias for getData()
	 *
	 * @param	string		$key		The data key to load
	 * @param	string|NULL	$subdir		The subdirectory to load the data key from or NULL for base /data dir
	 * @return	mixed|NULL
	 */ 
	public function data( $key, $subdir=NULL )
	{
		return $this->getData( $key, $subdir );
	}
	
	/**
	 * Save data to persistent store
	 *
	 * @api
	 *
	 * @param	string		$key		The data key to save
	 * @param	mixed		$data		The data to save
	 * @param	string|NULL	$subdir		The subdirectory to save the data to or NULL for base /data dir
	 * @return	mixed|NULL
	 * @throws 	\ErrorException
	 */
	public function setData( $key, $data, $subdir=NULL )
	{
		$data_dir = $this->getPath() . '/data' . ( $subdir !== NULL ? '/' . $subdir : '' );
		if ( ! is_dir( $data_dir ) )
		{
			if ( mkdir( $data_dir ) === FALSE )
			{
				throw new \ErrorException( 'Unable to create the plugin data directory.' );
			}
		}
		
		file_put_contents( $data_dir . '/' . $key . '.php', "<?php\nreturn <<<'JSON'\n" . json_encode( $data, JSON_PRETTY_PRINT ) . "\nJSON;\n" );
	}
	
	/**
	 * Get the plugin slug (dirname)
	 *
	 * @api
	 *
	 * @return	string
	 */
	public function pluginSlug()
	{
		return basename( $this->getPath() );
	}
	
	/**
	 * Get a plugin file path
	 *
	 * @api
	 *
	 * @param	string				$pathfile		The file path and name relative to the plugin path
	 * @param	string|FALSE		$type			The file type or NULL if file type is included in $pathfile
	 * @return	string
	 */
	public function pluginFile( $pathfile, $type=NULL )
	{
		return $this->getPath() . '/' . $pathfile . ( $type ? ( '.' . $type ) : '' );
	}
	
	/**
	 * Get a plugin file url
	 *
	 * @api
	 *
	 * @param	string		$filename		The file path and name (including extension)
	 * @return	string
	 */
	public function fileUrl( $filename )
	{
		return plugins_url( $filename, $this->getPath() . '/dir' );
	}
	
	/**
	 * Use a registered script
	 *
	 * @api
	 *
	 * @param	string		$script				The script to use
	 * @param	array		$localization		Localization data to pass to the script
	 * @return	void
	 */
	public function useScript( $script, $localization=array() )
	{
		static $usedScripts = array();
		static $existingLocalization = FALSE;
		
		$fileHash = md5( $this->fileUrl( $script ) );	
		$handle = isset( static::$scriptHandles[ $fileHash ] ) ? static::$scriptHandles[ $fileHash ] : $fileHash;
		
		if ( ! isset( $usedScripts[ $handle ] ) )
		{			
			if ( ! empty( $localization ) )
			{
				wp_localize_script( $handle, 'mw_localized_data', $localization );
				$existingLocalization = TRUE;
			}
			
			if ( empty( $localization ) and $existingLocalization )
			{
				wp_localize_script( $handle, 'mw_localized_data', array() );
				$existingLocalization = FALSE;
			}				
			
			wp_enqueue_script( $handle );
			$usedScripts[ $handle ] = TRUE;
		}
	}
	
	/**
	 * Use a registered stylesheet
	 *
	 * @api
	 *
	 * @param	string		$style				The stylesheet to use
	 * @return	void
	 */
	public function useStyle( $style )
	{
		static $usedStyles = array();
		$fileHash = md5( $this->fileUrl( $style ) );
		$handle = isset( static::$styleHandles[ $fileHash ] ) ? static::$styleHandles[ $fileHash ] : $fileHash;
		
		if ( ! isset( $usedStyles[ $fileHash ] ) )
		{
			wp_enqueue_style( $fileHash );
			$usedStyles[ $fileHash ] = TRUE;
		}
	}

	/**
	 * Get the location of a template file
	 *
	 * @api
	 *
	 * @param	string		$template 			Plugin template to look for (without file extension)
	 * @return	string
	 */
	public function getTemplate( $template )
	{
		if ( $overridden_template = locate_template( $this->pluginSlug() . '/' . $template . '.php' ) ) 
		{
			// locate_template() returns path to file
			// if either the child theme or the parent theme have overridden the template
			return $overridden_template;
		} 
		else 
		{
			// If neither the child nor parent theme have overridden the template,
			// we load the template from the 'templates' directory of this plugin,
			// or alternatively fall back to the modern wordpress framework template
			$templateFile = $this->pluginFile( 'templates/' . $template, 'php' );
			
			if ( file_exists( $templateFile ) )
			{			
				return $templateFile;
			}
			
			return Framework::instance()->pluginFile( 'templates/' . $template, 'php' );
		}
	}

	/**
	 * Get the content of a template
	 *
	 * @api
	 *
	 * @param	string		$template 			Plugin template to load (without file extension)
	 * @param	array		$vars				Variables to extract and make available to template
	 * @return	string
	 */
	public function getTemplateContent( $template, $vars=array() )
	{
		$templateFile = $this->getTemplate( $template );
		
		if ( ! file_exists( $templateFile ) )
		{
			return "[missing template file: {$this->pluginSlug()}/templates/{$template}]";
		}
		
		if ( is_array( $vars ) )
		{
			extract( $vars, EXTR_SKIP );
		}

		ob_start();
		include $templateFile;
		$templateContent = ob_get_clean();
		
		return apply_filters( 'mwp_tmpl', $templateContent, $this->pluginSlug(), $template, $vars );
	}
	
	/**
	 * Create a new form
	 *
	 * @param	string		$name		The plugin name
	 * @return	Form
	 */
	public function createForm( $name )
	{
		$form = new \Modern\Wordpress\Helper\Form( $name, $this );
		$form = $form->applyFilters( 'create', $form );
		return $form;
	}
	
	/**
	 * Ensure that the framework task runner is set up
	 *
	 * @Wordpress\Plugin( on="activation", file="plugin.php" )
	 *
	 * @return	void
	 */
	public function _pluginActivated()
	{
		wp_clear_scheduled_hook( 'modern_wordpress_queue_run' );
		wp_clear_scheduled_hook( 'modern_wordpress_queue_maintenance' );
		wp_schedule_event( time(), 'minutely', 'modern_wordpress_queue_run' );
		wp_schedule_event( time(), 'hourly', 'modern_wordpress_queue_maintenance' );
		$this->updateSchema();
	}

	/**
	 * Clear the queue schedule on framework deactivation
	 *
	 * @Wordpress\Plugin( on="deactivation", file="plugin.php" )
	 *
	 * @return	void
	 */
	public function _pluginDeactivated()
	{
		
	}
	
	/**
	 * Internal: Framework Plugin Finder
	 *
	 * @Wordpress\Filter( for="modern_wordpress_find_plugins" )
	 *
	 * @param	array		$plugins		Found plugins
	 * @return	array
	 */
	public function _rollCall( $plugins )
	{
		$plugins[] = $this;
		return $plugins;
	}
	
}
