<?php
/**
 * Plugin Class File
 *
 * Created:   December 18, 2016
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.0.1
 */
namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Pattern\ActiveRecord;
use \Modern\Wordpress\Framework;

/**
 * Task Class
 */
class Task extends ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	protected static $table = 'queued_tasks';
	
	/**
	 * @var	array		Table columns
	 */
	protected static $columns = array(
		'id',
		'action',
		'data',
		'priority',
		'next_start',
		'running',
		'last_start',
		'tag',
		'fails',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	protected static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	protected static $prefix = 'task_';
	
	/**
	 * @var	bool		Task completed
	 */
	public $complete = false;
	
	/**
	 * Property setter
	 *
	 * @param	string		$property		The property to set
	 * @param	mixed		$value			The value to set
	 * @return	void
	 */
	public function __set( $property, $value )
	{
		if ( $property == 'data' )
		{
			$value = json_encode( $value );
		}
		
		parent::__set( $property, $value );
	}
	
	/**
	 * Property getter
	 *
	 * @param	string		$property		The property to get_browser
	 * @return	mixed
	 */
	public function __get( $property )
	{
		$value = parent::__get( $property );
		
		if ( $property == 'data' )
		{
			$value = json_decode( $value, TRUE );
		}
		
		return $value;
	}
	
	/**
	 * Load record from row data
	 *
	 * @param	array		$row_data		Row data from the database
	 * @return	ActiveRecord
	 */
	public static function loadFromRowData( $row_data )
	{
		if ( isset( $row_data[ 'task_data' ] ) )
		{
			$row_data[ 'task_data' ] = json_decode( $row_data[ 'task_data' ], TRUE );
		}
		
		return parent::loadFromRowData( $row_data );
	}
	
	/**
	 * Get the next task that needs to be run
	 *
	 * @return	Task|NULL
	 */
	public static function popQueue()
	{		
		$db = Framework::instance()->db();
		$row = $db->get_row( 
			$db->prepare( "
				SELECT * FROM {$db->prefix}queued_tasks 
					WHERE task_running=0 AND task_next_start <= %d AND task_fails < 3 
					ORDER BY task_priority DESC, task_last_start ASC, task_id ASC", time() 
			), ARRAY_A
		);
		
		if ( $row === NULL )
		{
			return NULL;
		}
		
		return static::loadFromRowData( $row );
	}
	
	/**
	 * Unlock failed tasks
	 *
	 * @return	void
	 */
	public static function runMaintenance()
	{
		$db = Framework::instance()->db();
		$db->query( "UPDATE " . $db->prefix . static::$table . " SET task_running=0, task_fails=task_fails + 1 WHERE task_running=1 AND task_last_start < " . ( time() - ( 60 * 60 ) ) );
	}
}
