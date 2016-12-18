<?php
/**
 * ActiveRecord Class
 *
 * Created:   December 18, 2016
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.0.1
 */
namespace Modern\Wordpress\Pattern;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \Modern\Wordpress\Framework;

/**
 * An active record design pattern
 *
 */
abstract class ActiveRecord
{
	/**
	 * @var	array		Multitons cache (needs to be defined in subclasses also)
	 */
	protected static $multitons = array();
	
	/**
	 * @var	string		Table name
	 */
	protected static $table;
	
	/**
	 * @var	array		Table columns
	 */
	protected static $columns = array();
	
	/**
	 * @var	string		Table primary key
	 */
	protected static $key;
	
	/**
	 * @var	string		Table column prefix
	 */
	protected static $prefix = '';
	
	/**
	 * @var	array		Record data
	 */
	protected $_data = array();
	
	/**
	 * Property getter
	 *
	 * @param	string		$property		The property to get
	 * @return	mixed
	 */
	public function __get( $property )
	{
		if ( in_array( $property, static::$columns ) )
		{
			if ( array_key_exists( static::$prefix . $property, $this->_data ) )
			{
				return $this->_data[ static::$prefix . $property ];
			}
		}
		
		return NULL;
	}

	/**
	 * Property setter
	 *
	 * @param	string		$property		The property to set
	 * @param	mixed		$value			The value to set
	 * @return	void
	 */
	public function __set( $property, $value )
	{
		if ( in_array( $property, static::$columns ) )
		{
			$this->_data[ static::$prefix . $property ] = $value;
		}
	}
	
	/**
	 * Load record by id
	 *
	 * @param	int 	$id			Record id
	 * @return	ActiveRecord
	 * @throws	ErrorException		Throws exception if record could not be located
	 */
	public static function load( $id )
	{
		if ( isset( static::$multitons[ $id ] ) )
		{
			return static::$multitons[ $id ];
		}
		
		$db = Framework::instance()->db();
		$row = $db->get_row( $db->prepare( "SELECT * FROM " . $db->prefix . static::$table . " WHERE " . static::$prefix . static::$key . "=%d", $id ), ARRAY_A );
		
		if ( $row )
		{
			return static::loadFromRowData( $row );
		}
		
		throw new \ErrorException( 'Unable to find a record with the id: ' . $id );
	}
	
	/**
	 * Load record from row data
	 *
	 * @param	array		$row_data		Row data from the database
	 * @return	ActiveRecord
	 */
	public static function loadFromRowData( $row_data )
	{
		/* Look for cached record in multiton store */
		if ( isset( $row_data[ static::$prefix . static::$key ] ) and $row_data[ static::$prefix . static::$key ] )
		{
			if ( isset( static::$multitons[ $row_data[ static::$prefix . static::$key ] ] ) )
			{
				return static::$multitons[ $row_data[ static::$prefix . static::$key ] ];
			}
		}
		
		/* Build the record */
		$record = new static;
		foreach( $row_data as $column => $value )
		{
			if ( static::$prefix and substr( $column, 0, strlen( static::$prefix ) ) == static::$prefix )
			{
				$column = substr( $column, strlen( static::$prefix ) );
			}
			
			$record->$column = $value;
		}
		
		/* Cache the record in the multiton store */
		if ( isset( $row_data[ static::$prefix . static::$key ] ) and $row_data[ static::$prefix . static::$key ] )
		{
			static::$multitons[ $row_data[ static::$prefix . static::$key ] ] = $record;
		}
		
		return $record;
	}
	
	/**
	 * Save the record
	 *
	 * @return	bool
	 */
	public function save()
	{
		$db = Framework::instance()->db();
		$self = get_called_class();
		$row_key = static::$prefix . static::$key;
		
		if ( ! isset( $this->_data[ $row_key ] ) or ! $this->_data[ $row_key ] )
		{
			$format = array_map( function( $value ) use ( $self ) { return $self::dbFormat( $value ); }, $this->_data );
			
			if ( $db->insert( $db->prefix . static::$table, $this->_data, $format ) === FALSE )
			{
				return FALSE;
			}
			else
			{
				$this->_data[ $row_key ] = $db->insert_id;
				static::$multitons[ $this->_data[ $row_key ] ] = $this;
				return TRUE;
			}
		}
		else
		{
			$format = array_map( function( $value ) use ( $self ) { return $self::dbFormat( $value ); }, $this->_data );
			$where_format = static::dbFormat( $this->_data[ $row_key ] );
			
			if ( $db->update( $db->prefix . static::$table, $this->_data, array( $row_key => $this->_data[ $row_key ] ), $format, $where_format ) === FALSE )
			{
				return FALSE;
			}
			
			return TRUE;
		}
	}
	
	/**
	 * Delete a record
	 *
	 * @return	bool
	 */
	public function delete()
	{
		$row_key = static::$prefix . static::$key;
		
		if ( isset( $this->_data[ $row_key ] ) and $this->_data[ $row_key ] )
		{
			$db = Framework::instance()->db();
			$id = $this->_data[ $row_key ];
			$format = static::dbFormat( $id );
			
			if ( $db->delete( $db->prefix . static::$table, array( $row_key => $id ), $format ) )
			{
				unset( static::$multitons[ $id ] );
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Get the database placeholder format for a value type
	 *
	 * @param	mixed		$value			The value to check
	 * @return	string	
	 */
	public static function dbFormat( $value )
	{
		if ( is_int( $value ) ) {
			return '%d';
		}
		
		if ( is_float( $value ) ) {
			return '%f';
		}
		
		return '%s';
	}
	
}
