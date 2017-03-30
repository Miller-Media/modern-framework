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
	public static $table = 'queued_tasks';
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array(
		'id',
		'action',
		'data' => array( 'format' => 'JSON' ),
		'priority',
		'next_start',
		'running',
		'last_start',
		'tag',
		'fails',
		'blog_id',
	);
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key = 'id';
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = 'task_';
	
	/**
	 * @var	bool		Task completed
	 */
	public $complete = false;
	
	/**
	 * @var bool		Task aborted
	 */
	public $aborted = false;
	
	/**
	 * Execute this task
	 *
	 * @return	void
	 */
	public function execute()
	{
		do_action( $this->action, $this );
	}
	
	/**
	 * Get task title
	 */
	public function getTitle()
	{
		$implied_title = ucwords( str_replace( '_', ' ', $this->action ) );
		return apply_filters( $this->action . '_title', $implied_title, $this );
	}
	
	/**
	 * Execute a setup action
	 * 
	 * @return	void
	 */
	public function setup()
	{
		do_action( $this->action . '_setup', $this );
	}
	
	/**
	 * Complete this task
	 *
	 * @return	void
	 */
	public function complete()
	{
		$this->complete = true;		
		do_action( $this->action . '_complete', $this );
	}
	
	/**
	 * Abort the task
	 *
	 * @return 	void
	 */
	public function abort()
	{
		$this->aborted = true;
		do_action( $this->action . '_abort', $this );
	}
	
	/**
	 * Unlock the task
	 *
	 * @return	void
	 */
	public function unlock()
	{
		if ( $this->fails >= 3 )
		{
			$this->fails = 0;
			$this->save();
		}		
	}
	
	/**
	 * Run Next
	 *
	 * Increase the task priority to run next
	 *
	 * @return	void
	 */
	public function runNext()
	{
		if ( ! $this->running )
		{
			$this->unlock();
			$this->next_start = 0;
			$this->priority = 99;
			$this->save();
		}		
	}
	
	
	/**
	 * Set Task Data
	 *
	 * @param	string			$key			The data key to set
	 * @param	mixed			$value			The value to set
	 * @return	void
	 */
	public function setData( $key, $value )
	{
		$data = $this->data;
		$data[ $key ] = $value;
		$this->data = $data;
	}
	
	/**
	 * Get Task Data
	 *
	 * @param	string			$key			The data key to set
	 * @return	mixed
	 */
	public function getData( $key )
	{
		$data = $this->data;
		if ( isset( $data[ $key ] ) ) {
			return $data[ $key ];
		}
		
		return NULL;
	}
	
	/**
	 * Set the task status
	 *
	 * @param	string				$status				The task status to display in the admin
	 */
	public function setStatus( $status )
	{
		$data = $this->data;
		$data[ 'status' ] = (string) $status;
		$this->data = $data;
		$this->save();
	}

	/**
	 * Add a task to the queue
	 *
	 * @param	array|string		$config			Task configuration options
	 * @param	mixed				$data			Task data
	 * @return	void
	 */
	public static function queueTask( $config, $data=NULL )
	{
		$task = new static;
		
		if ( is_array( $config ) )
		{
			if ( ! isset( $config[ 'action' ] ) )
			{
				return FALSE;
			}
			
			$task->action = $config[ 'action' ];
			
			if ( isset( $config[ 'tag' ] ) ) {
				$task->tag = $config[ 'tag' ];
			}
			
			if ( isset( $config[ 'priority' ] ) ) {
				$task->priority = $config[ 'priority' ];
			}
			
			if ( isset( $config[ 'next_start' ] ) ) {
				$task->next_start = $config[ 'next_start' ];
			}
		}
		
		if ( is_string( $config ) )
		{
			$task->action = $config;
		}
		
		$task->blog_id = get_current_blog_id();
		$task->data = $data;
		$task->save();
	}

	/**
	 * Delete tasks from queue based on action and or tag
	 *
	 * @param	string		$action			Delete all tasks with specific action
	 * @param	string		$tag			Delete all tasks with specific tag
	 * @return	void
	 */
	public static function deleteTasks( $action, $tag=NULL )
	{
		$db = Framework::instance()->db();
		
		if ( $action === NULL and $tag === NULL )
		{
			return;
		}
		
		/* Only action provided */
		if ( $tag === NULL )
		{
			$db->query( $db->prepare( "DELETE FROM  " . $db->prefix . static::$table . " WHERE task_action=%s AND task_blog_id=%d", $action, get_current_blog_id() ) );
		}
		
		/* Only tag provided */
		elseif ( $action === NULL )
		{
			$db->query( $db->prepare( "DELETE FROM  " . $db->prefix . static::$table . " WHERE task_tag=%s AND task_blog_id=%d", $tag, get_current_blog_id() ) );		
		}
		
		/* Both action and tag provided */
		else
		{
			$db->query( $db->prepare( "DELETE FROM  " . $db->prefix . static::$table . " WHERE task_action=%s AND task_tag=%s AND task_blog_id=%d", $action, $tag, get_current_blog_id() ) );
		}
	}
	
	/**
	 * Count tasks from queue based on action and or tag
	 *
	 * @param	string		$action			Count all tasks with specific action|NULL to ignore
	 * @param	string		$tag			Count all tasks with specific tag|NULL to ignore
	 * @return	int
	 */
	public static function countTasks( $action=NULL, $tag=NULL )
	{
		$db = Framework::instance()->db();
		
		if ( $action === NULL and $tag === NULL )
		{
			return $db->get_var( "SELECT COUNT(*) FROM  " . $db->prefix . static::$table . " WHERE task_blog_id=%d", get_current_blog_id() );
		}
		
		/* Only action provided */
		if ( $tag === NULL )
		{
			return $db->get_var( $db->prepare( "SELECT COUNT(*) FROM  " . $db->prefix . static::$table . " WHERE task_action=%s AND task_blog_id=%d", $action, get_current_blog_id() ) );
		}
		
		/* Only tag provided */
		elseif ( $action === NULL )
		{
			return $db->get_var( $db->prepare( "SELECT COUNT(*) FROM  " . $db->prefix . static::$table . " WHERE task_tag=%s AND task_blog_id=%d", $tag, get_current_blog_id() ) );		
		}
		
		/* Both action and tag provided */
		else
		{
			return $db->get_var( $db->prepare( "SELECT COUNT(*) FROM  " . $db->prefix . static::$table . " WHERE task_action=%s AND task_tag=%s AND task_blog_id=%d", $action, $tag, get_current_blog_id() ) );
		}
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
				SELECT * FROM {$db->prefix}" . static::$table . " 
					WHERE task_running=0 AND task_next_start <= %d AND task_fails < 3 AND task_blog_id=%d
					ORDER BY task_priority DESC, task_last_start ASC, task_id ASC", time(), get_current_blog_id()
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
