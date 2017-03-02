<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace Modern\Wordpress\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Tasks Controller
 *
 * @Wordpress\AdminPage( title="Tasks Management", menu="Wordpress Tasks", slug="mwp-tasks", type="management" )
 */
class Tasks extends \Modern\Wordpress\Pattern\Singleton
{
	/**
	 * @var	object			Singleton instance
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
		$this->plugin = $plugin ?: \Modern\Wordpress\Framework::instance();
	}
	
	/**
	 * Tasks Management Index
	 * 
	 * @return	string
	 */
	public function do_index()
	{
		$table = \Modern\Wordpress\Task::createDisplayTable();
		$table->columns = array( 'task_action' => 'Task Action', 'task_tag' => 'Tag', 'task_last_start' => 'Last Ran', 'task_next_start' => 'Next Start', 'task_running' => 'Activity', 'task_fails' => 'Fails', 'task_data' => 'Status' );
		$table->bulkActions = array( 'delete' => 'Delete' );
		$table->sortBy = 'task_next_start';
		$table->sortOrder = 'ASC';
		
		$table->handlers = array
		(
			'task_last_start' => function( $task ) {
				if ( $task[ 'task_last_start' ] <= 0 ) {
					return __( "Never", 'modern-framework' );
				}
				
				return get_date_from_gmt( date( 'Y-m-d H:i:s', $task[ 'task_next_start' ] ), 'F j, Y H:i:s' );
			},
			'task_next_start' => function( $task ) {
				if ( $task[ 'task_next_start' ] > 0 )
				{
					return get_date_from_gmt( date( 'Y-m-d H:i:s', $task[ 'task_next_start' ] ), 'F j, Y H:i:s' );
				}
				
				return __( "ASAP", 'modern-framework' );
			},
			'task_running' => function( $task ) {
				if ( $task[ 'task_running' ] ) {
					return __( "Running", 'modern-framwork' );
				}
				
				return __( "Idle", 'modern-framework' );
			},
			'task_data' => function( $task ) {
				$data = json_decode( $task[ 'task_data' ], true );
				
				if ( isset( $data[ 'status' ] ) ) {
					return $data[ 'status' ];
				}
				
				return '--';
			},
		);
		
		$table->prepare_items();
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/tasks', array( 'table' => $table ) );
	}
	
}
