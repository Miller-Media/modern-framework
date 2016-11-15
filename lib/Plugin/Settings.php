<?php

namespace Modern\Wordpress\Plugin;

/**
 * Plugin Settings
 */
class Settings
{
	
	/**
	 * @var array 	Plugin Settings
	 */
	protected $settings;
	
	/**
	 * Settings Getter
	 *
	 * @param	string		$setting		The setting to get
	 * @return	mixed
	 */
	public function getSetting( $setting )
	{
		if ( ! isset( $this->settings ) )
		{
			$this->settings = get_option( strtolower( str_replace( '\\', '_', get_class( $this ) ) ), array() );
		}
		
		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : NULL;
	}
	
	/**
	 * Register settings page
	 *
	 * @return	void
	 */
	public function registerPage()
	{

	}
	
	/**
	 * Register settings fields
	 *
	 * @return	void
	 */
	public function registerFields()
	{

	}
	
}