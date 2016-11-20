<?php

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\Singleton;

/**
 * Plugin Class
 */
abstract class Plugin extends Singleton
{
	/**
	 * @var string 	Plugin Path
	 */
	protected $path;
	
	/**
	 * @var array
	 */
	protected $settings = array();
	
	/** 
	 * Set Plugin Path
	 *
	 * @return	void
	 */
	public function setPath( $path )
	{
		$this->path = $path;
	}
	
	/**
	 * Get Plugin Path
	 */
	public function getPath()
	{
		return $this->path;
	}
	
	/**
	 * Set Settings
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
	 * Get Settings
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
	 * Get Setting
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
	 * Set Setting
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
	 * Get a plugin file path
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
	 * @param	string		$filename		The file path and name (including extension)
	 * @return	string
	 */
	public function fileUrl( $filename )
	{
		return plugins_url( $filename, $this->path . '/dummydir' );
	}
	
	/**
	 * Use Script
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
	 * Use Stylesheet
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
	 * Get Template
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
	 * Get Template Content
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
	
}
