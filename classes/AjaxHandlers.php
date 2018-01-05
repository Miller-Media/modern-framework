<?php
/**
 * Plugin Class File
 *
 * Created:   January 4, 2018
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * AjaxHandlers Class
 */
class AjaxHandlers extends \Modern\Wordpress\Pattern\Singleton
{
	/**
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
	 */
	public function getPlugin()
	{
		return $this->plugin;
	}
	
	/**
	 * Set plugin
	 *
	 * @return	this			Chainable
	 */
	public function setPlugin( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	/**
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->setPlugin( $plugin ?: \Modern\Wordpress\Framework::instance() );
	}
	
	/**
	 * Load available studio projects
	 *
	 * @Wordpress\AjaxHandler( action="mwp_resequence_records", for={"users"} )
	 *
	 * @return	void
	 */
	public function resequenceRecords()
	{
		wp_send_json( $_REQUEST );
	}
}
