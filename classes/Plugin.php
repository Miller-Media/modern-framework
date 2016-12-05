<?php
/**
 * Plugin Base Class (Singleton)
 *
 * @package 	Modern Wordpress Framework
 * @author	Kevin Carwile
 * @since	Nov 20, 2016
 */

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\Singleton;

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
	 * Check if plugin update is required
	 *
	 * @Wordpress\Action( for="init" )
	 *
	 * @return	void
	 */
	public function checkIfUpdateNeeded()
	{
		$build = $this->data( 'build-meta' );
		$install = $this->data( 'install-meta' );
		
		if ( ! is_array( $installed ) or version_compare( $install[ 'version' ], $build[ 'version' ] ) == -1 )
		{
			$this->update();
		}
	}
	
	/**
	 * Run updates when new plugin version is uploaded
	 *
	 * @return	void
	 */
	public function update()
	{
		$build = $this->data( 'build-meta' );
		$install = $this->data( 'install-meta' ) ?: array();
		
		if ( is_array( $build[ 'schema' ] ) )
		{
			if ( is_array( $build[ 'schema' ][ 'tables' ] ) )
			{
				$dbHelper = \Modern\Wordpress\DbHelper::instance()
				foreach( $schema[ 'tables' ] as $table )
				{
					$tableSql = $dbHelper->buildTableSQL( $table );
					dbDelta( $tableSql );
				}
			}
		}
		
		$install[ 'schema' ] = $build[ 'schema' ];
		$install[ 'version' ] = $build[ 'version' ];
		$this->setData( 'install-meta', $install );
	}
	
	/**
	 * Uninstall routine
	 *
	 * @return	void
	 */
	public function uninstall()
	{
		$install = $this->data( 'install-meta' ) ?: array();
		
		if ( is_array( $install[ 'schema' ] ) )
		{
			if ( is_array( $install[ 'schema' ][ 'tables' ] ) )
			{
				foreach( $install[ 'schema' ][ 'tables' ] as $table )
				{
					$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table['name']}" );
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
		if ( file_exists( $this->fileUrl( 'data/' . $key . '.php' ) ) )
		{
			$data = include $this->fileUrl( 'data/' . $key . '.php' );
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
		return $this->getData( $key );
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
		
		file_put_contents( $this->fileUrl( 'data/' . $key . '.php' ), "<?php\nreturn <<<'JSON'\n" . json_encode( $data, JSON_PRETTY_PRINT ) . "\nJSON;" );
	}
	
	/**
	 * Get a plugin file path
	 *
	 * @api
	 *
	 * @param	string				$pathfile		The file path and name
	 * @param	string|FALSE		$type			The file type or FALSE if file type is included in $pathfile
	 * @return	string
	 */
	public function pluginFile( $pathfile, $type='php' )
	{
		return $this->path . '/' . $pathfile . ( $type ? ( '.' . $type ) : '' );
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
		return plugins_url( $filename, $this->path . '/dummydir' );
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
		$fileHash = md5( $this->fileUrl( $script ) );
		
		if ( ! isset( $usedScripts[ $fileHash ] ) )
		{
			$localization = array_merge( array
			(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
			, $localization );
			
			wp_localize_script( $fileHash, 'mw_localized_data', $localization );			
			wp_enqueue_script( $fileHash );
			$usedScripts[ $fileHash ] = TRUE;
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
		if ( $overridden_template = locate_template( $template . '.php' ) ) 
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
			$templateFile = $this->pluginFile( 'templates/' . $template );
			
			if ( file_exists( $templateFile ) )
			{			
				return $this->pluginFile( 'templates/' . $template );
			}
			
			return \Modern\Wordpress\Framework::instance()->pluginFile( 'templates/' . $template );
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
			return NULL;
		}
		
		if ( is_array( $vars ) )
		{
			unset( $vars[ 'templateFile' ] );
			extract( $vars, EXTR_SKIP );
		}

		ob_start();
		include $templateFile;
		return ob_get_clean();
	}
	
	/**
	 * Internal: Framework Plugin Finder
	 *
	 * @Wordpress\Filter( for="modern_wordpress_find_plugins" )
	 *
	 * @param	array		$plugins		Found plugins
	 * @return	array
	 */
	public function rollCall( $plugins )
	{
		$plugins[] = $this;
		return $plugins;
	}
	
}
