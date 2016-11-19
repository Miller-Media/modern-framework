<?php

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

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
	 * Set Settings
	 *
	 * @param	Settings		$settings		The settings object
	 * @return	void
	 */
	public function addSettings( $settings )
	{
		$this->settings[ $settings->id ] = $settings;
		$settings->setPlugin( $this );
	}
	
	/** 
	 * Get Settings
	 *
	 * @param	string|NULL		$id			The settings id to get
	 * @return	array|Settings|NULL
	 */
	public function getSettings( $id=NULL )
	{
		/* Return a specific settings id */
		if ( isset( $id ) )
		{
			if ( isset( $this->settings[ $id ] ) )
			{
				return $this->settings[ $id ];
			}
			
			return NULL;
		}
		
		/* If no id specified and only one settings page, return it */
		if ( count( $this->settings ) == 1 )
		{
			foreach( $this->settings as $settings )
			{
				return $settings;
			}
		}
		
		/* Return all settings */
		return $this->settings;
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
			// we load the template from the 'templates' sub-directory of the directory this file is in
			return $this->pluginFile( 'templates/' . $template );
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
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		
		if ( is_array( $wp_query->query_vars ) ) 
		{
			extract( $wp_query->query_vars, EXTR_SKIP );
		}
		
		if ( is_array( $vars ) )
		{
			extract( $vars, EXTR_SKIP );
		}

		if ( isset( $s ) ) 
		{
			$s = esc_attr( $s );
		}
		
		ob_start();
		require $this->getTemplate( $template );
		return ob_get_clean();
	}
	
}
