<?php

namespace Modern\Wordpress;

use \Modern\Wordpress\Pattern\Singleton;

/**
 * Plugin Class
 */
class Plugin extends Singleton
{
	/**
	 * Instance Cache - Required for all singleton subclasses
	 *
	 * @var	self
	 */
	protected static $_instance;

	/**
	 * @var string 	Plugin Path
	 */
	protected $path;
	
	/**
	 * @var Settings
	 */
	protected $settings;
	
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
	 * Set Settings
	 *
	 * @param	Settings		$settings		The settings object
	 * @return	void
	 */
	public function setSettings( $settings )
	{
		$this->settings = $settings;
	}
	
	/** 
	 * Get Settings
	 *
	 * @return	Settings
	 */
	public function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Get a plugin file path
	 *
	 * @param	string		$pathfile		The file path and name (without extension)
	 * @param	string		$type			The file type
	 * @return	string
	 */
	public function pluginFile( $pathfile, $type='php' )
	{
		return $this->path . '/' . $pathfile . '.' . $type;
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
	 * Get Template Content
	 *
	 * @param	string		$template 			The template to load (without file extension)
	 * @return	string
	 */
	public function getTemplate( $template )
	{			
		ob_start();
		
		if ( $overridden_template = locate_template( $template . '.php' ) ) 
		{
			// locate_template() returns path to file
			// if either the child theme or the parent theme have overridden the template
			load_template( $overridden_template );
		} 
		else 
		{
			// If neither the child nor parent theme have overridden the template,
			// we load the template from the 'templates' sub-directory of the directory this file is in
			load_template( dirname( __FILE__ ) . '/templates/' . $template . '.php' );
		}
		
		return ob_get_clean();
	}
	
}
