<?php

namespace Modern\Wordpress\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\Singleton;

/**
 * Plugin Settings
 */
abstract class Settings extends Singleton
{
	
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var array 	Plugin Settings
	 */
	protected $settings;
	
	/**
	 * @var	Plugin
	 */
	protected $plugin;
	
	/**
	 * @var string	Plugin Settings ID
	 */
	public $id;
	
	/**
	 * Constructor
	 *
	 * @return	void
	 */
	protected function __construct()
	{
		if ( ! isset( $this->id ) )
		{
			$this->id = strtolower( str_replace( '\\', '_', get_class( $this ) ) );
		}
		
		parent::__construct();
	}
	
	/**
	 * Set Plugin
	 *
	 * @return	void
	 */
	public function setPlugin( \Modern\Wordpress\Plugin $plugin )
	{
		$this->plugin = $plugin;
	}
	
	/**
	 * Get Plugin
	 *
	 * @return	Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
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
			$settingsID = $this->id ?: strtolower( str_replace( '\\', '_', get_class( $this ) ) );
			$this->settings = get_option( $settingsID, array() );
		}
		
		return isset( $this->settings[ $setting ] ) ? $this->settings[ $setting ] : NULL;
	}
	
	/**
	 * Validate Settings
	 *
	 * @param	array		$data			Input data
	 * @return	array
	 */
	public function validate( $data=array() )
	{
		return $data;
	}
	
}