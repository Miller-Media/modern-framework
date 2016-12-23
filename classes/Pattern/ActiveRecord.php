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
	public static $table;
	
	/**
	 * @var	array		Table columns
	 */
	public static $columns = array();
	
	/**
	 * @var	string		Table primary key
	 */
	public static $key;
	
	/**
	 * @var	string		Table column prefix
	 */
	public static $prefix = '';
	
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
		/* Ensure we are getting a defined property */
		if ( in_array( $property, static::$columns ) or array_key_exists( $property, static::$columns ) )
		{
			/* Proceed if we have a value to return */
			if ( array_key_exists( static::$prefix . $property, $this->_data ) )
			{
				/* Retrieve the value */
				$value = $this->_data[ static::$prefix . $property ];
				
				/* Check if there are any optional params assigned to this property */
				if ( array_key_exists( $property, static::$columns ) )
				{
					$options = static::$columns[ $property ];
					
					/* Special format conversion needed? */
					if ( isset( $options[ 'format' ] ) )
					{
						switch( $options[ 'format' ] )
						{
							case 'JSON':
								
								$value = json_decode( $value, true );
								break;
								
							case 'ActiveRecord':
								
								$class = $options[ 'class' ];
								try {
									$value = $class::load( $value );
								}
								catch( \OutOfRangeException $e ) {
									$value = NULL;
								}
								break;
						}
					}
				}
				
				/* Return the value */
				return $value;
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
	 * @throws	InvalidArgumentException
	 */
	public function __set( $property, $value )
	{
		/* Ensure we are setting a defined property */
		if ( in_array( $property, static::$columns ) or array_key_exists( $property, static::$columns ) )
		{
			/* Check if there are any optional params assigned to this property */
			if ( array_key_exists( $property, static::$columns ) )
			{
				$options = static::$columns[ $property ];
				
				/* Special format conversion needed? */
				if ( isset( $options[ 'format' ] ) )
				{
					switch( $options[ 'format' ] )
					{
						case 'JSON':
							
							$value = json_encode( $value );
							break;
							
						case 'ActiveRecord':
							
							$class = $options[ 'class' ];
							
							if ( is_object( $value ) )
							{
								if ( $value instanceof \Modern\Wordpress\Pattern\ActiveRecord and is_a( $value, $class ) )
								{
									$value = $value->id();
								}
								else
								{
									if ( ! $value instanceof \Modern\Wordpress\Pattern\ActiveRecord )
									{
										throw new \InvalidArgumentException( 'Object is not a subclass of Modern\Wordpress\Pattern\ActiveRecord' );
									}
									throw new \InvalidArgumentException( 'Object expected to be an active record of type: ' . $class . ' but it is a: ' . get_class( $value ) );
								}
							}
							break;
					}
				}
			}
			
			/* Set the value */
			$this->_data[ static::$prefix . $property ] = $value;
		}
	}
	
	/** 
	 * Get the active record id
	 *
	 * @return	int|NULL
	 */
	public function id()
	{
		if ( isset( $this->_data[ static::$prefix . static::$key ] ) )
		{
			return $this->_data[ static::$prefix . static::$key ];
		}
		
		return NULL;
	}
	
	/**
	 * Load record by id
	 *
	 * @param	int 	$id			Record id
	 * @return	ActiveRecord
	 * @throws	OutOfRangeException		Throws exception if record could not be located
	 */
	public static function load( $id )
	{
		if ( ! $id )
		{
			throw new \OutOfRangeException( 'Invalid ID' );
		}
		
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
		
		throw new \OutOfRangeException( 'Unable to find a record with the id: ' . $id );
	}
	
	/**
	 * Load multiple records
	 *
	 * @param	array|string		$where 			Where clause with associated replacement values
	 * @param	string				$order			Order by ( include field + ASC or DESC ) ex. "field_name DESC"
	 * @param   int|array           $limit          Limit clause. If an int is provided, it should be the number of records to limit by
	 *                                              If an array is provided, the first number will be the start record and the second number will be the limit
	 * @return	array
	 */
	public static function loadWhere( $where, $order=NULL, $limit=NULL )
	{
		if ( is_string( $where ) )
		{
			$where = array( $where );
		}
		
		$db = Framework::instance()->db();
		$results = array();
		$compiled = static::compileWhereClause( $where );
		
		/* Get results of the prepared query */
		$query = "SELECT * FROM " . $db->prefix . static::$table . " WHERE " . $compiled[ 'where' ];
		
		if ( $order !== NULL )
		{
			$query .= " ORDER BY " . $order;
		}
		
		if ( $limit !== NULL )
		{
			if ( is_array( $limit ) )
			{
				$query .= " LIMIT " . $limit[0] . ", " . $limit[1];
			}
			else
			{
				$query .= " LIMIT " . $limit;
			}
		}
		
		$prepared_query = ! empty( $compiled[ 'params' ] ) ? $db->prepare( $query, $compiled[ 'params' ] ) : $query;
		$rows = $db->get_results( $prepared_query, ARRAY_A );
		
		if ( ! empty( $rows ) )
		{
			foreach( $rows as $row )
			{
				$record = static::loadFromRowData( $row );
				$results[] = $record;
			}
		}
		
		return $results;
	}
	
	/**
	 * Count records
	 *
	 * @param	array|string		$where 			Where clause with associated replacement values
	 * @return	array
	 */
	public static function countWhere( $where )
	{
		if ( is_string( $where ) )
		{
			$where = array( $where );
		}
		
		$db = Framework::instance()->db();
		$compiled = static::compileWhereClause( $where );
		
		/* Get results of the prepared query */
		$query = "SELECT COUNT(*) FROM " . $db->prefix . static::$table . " WHERE " . $compiled[ 'where' ];
		$prepared_query = ! empty( $compiled[ 'params' ] ) ? $db->prepare( $query, $compiled[ 'params' ] ) : $query;
		$count = $db->get_var( $prepared_query );
		
		return $count;
	}
	
	/**
	 * Compile a where clause with params
	 *
	 * @param	array		$where			Where clauses
	 * @return	array
	 */
	public static function compileWhereClause( $where )
	{
		$params = array();
		$clauses = array();
		$compiled = array
		(
			'where' => "1=0",
			'params' => array(),
		);
		
		$where = array( $where );
		$called_class_slug = strtolower( str_replace( '\\', '_', get_called_class() ) );
		
		/* Apply filters */
		$where = apply_filters( 'active_record_where', $where, get_called_class() );
		$where = apply_filters( 'active_record_where' . $called_class_slug, $where );
		
		/* Iterate the clauses to compile the query and replacement values */
		foreach( $where as $clause )
		{
			if ( is_array( $clause ) )
			{
				$clauses[] = array_shift( $clause );
				if ( ! empty( $clause ) )
				{
					$compiled[ 'params' ] = array_merge( $compiled[ 'params' ], $clause );
				}
			}
			else
			{
				$clauses[] = $clause;
			}
		}
		
		if ( ! empty( $clauses ) )
		{
			$compiled[ 'where' ] = '('. implode( ') AND (', $clauses ) . ')';
		}
		
		return $compiled;
	}
	
	/**
	 * Create a table for viewing active records
	 */
	public static function createDisplayTable()
	{
		$table = new \Modern\Wordpress\Helper\ActiveRecordTable;
		$table->activeRecordClass = get_called_class();
		return $table;
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
			
			$record->setDirectly( $column, $value );
		}
		
		/* Cache the record in the multiton store */
		if ( isset( $row_data[ static::$prefix . static::$key ] ) and $row_data[ static::$prefix . static::$key ] )
		{
			static::$multitons[ $row_data[ static::$prefix . static::$key ] ] = $record;
		}
		
		return $record;
	}
	
	/**
	 * Set internal data properties directly
	 *
	 * @param	string		$property		The property to set
	 * @param	mixed		$value			The value to set
	 * @return	void
	 */
	public function setDirectly( $property, $value )
	{
		/* Ensure we are setting a defined property */
		if ( in_array( $property, static::$columns ) or array_key_exists( $property, static::$columns ) )
		{
			$this->_data[ static::$prefix . $property ] = $value;
		}
	}
	
	/**
	 * Get internal data properties directly
	 *
	 * @param	string		$property		The property to set
	 * @return	void
	 */
	public function getDirectly( $property )
	{
		/* Ensure we are setting a defined property */
		if ( in_array( $property, static::$columns ) or array_key_exists( $property, static::$columns ) )
		{
			if ( array_key_exists( static::$prefix . $property, $this->_data ) )
			{
				return $this->_data[ static::$prefix . $property ];
			}
		}
		
		return NULL;
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
