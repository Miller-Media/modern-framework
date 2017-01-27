<?php
/**
 * Table Helper
 *
 * Adapted from, Worpress Administration API: WP_List_Table class
 * /wp-admin/includes/class-wp-list-table.php
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 */

/**
 * Base class for displaying a list of items in an ajaxified HTML table.
 *
 * @since 3.1.0
 * @access private
 */

namespace Modern\Wordpress\Helper; 
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WP_Screen' ) ) {
	require_once ABSPATH . '/wp-admin/includes/class-wp-screen.php';
}

if ( ! function_exists ( 'convert_to_screen' ) ) {
	require_once ABSPATH . '/wp-admin/includes/template.php';
}

if ( ! function_exists ( 'get_column_headers' ) ) {
	require_once ABSPATH . '/wp-admin/includes/screen.php';
}

use Modern\Wordpress\Framework;

/**
 * Used to create tables to display and manage active records
 */
class ActiveRecordTable extends \WP_List_Table
{

	/**
	 * @var string			Active Record Classname
	 */
	public $activeRecordClass;
	
	/**
	 * @var	array			Keyed array of bulk actions to allow
	 */
	public $bulkActions = array();
	
	/**
	 * @var	int				Number of records to show per page
	 */
	public $perPage = 50;
	
	/**
	 * @var	string			Column to sort by
	 */
	public $sortBy;
	
	/**
	 * @var string			Sort order
	 */
	public $sortOrder = 'DESC';
	
	/**
	 * @var	array			Columns to display
	 */
	public $columns;
	
	/**
	 * @var array			Optional display handlers for columns
	 */
	public $handlers = array();
	
    /**
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     */
    public function __construct( $args=array() )
	{
		//Set parent defaults
		parent::__construct( array_merge
		( 
			array
			(
				'singular'  => 'item',
				'plural'    => 'items',
				'ajax'      => true
			), 
			$args 
		));
        
    }
	
	/**
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title() 
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as 
	 * possible. 
	 * 
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 * 
	 * For more detailed insight into how columns are handled, take a look at 
	 * WP_List_Table::single_row_columns()
	 * 
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 */
	public function column_default( $item, $column_name )
	{
		if ( isset( $this->handlers[ $column_name ] ) and is_callable( $this->handlers[ $column_name ] ) )
		{
			return call_user_func( $this->handlers[ $column_name ], $item, $column_name );
		}
		
		return $item[ $column_name ];
	}
	
	/**
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 */
	public function column_cb( $item )
	{
		if ( ! empty( $this->bulkActions ) )
		{
			$class = $this->activeRecordClass;
			return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', $this->_args[ 'singular' ], $item[ $class::$prefix . $class::$key ] );
		}
	}
	
	/**
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value 
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 * 
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 * 
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 */
	public function get_columns()
	{
		$columns = array();
		$class = $this->activeRecordClass;
		
		if ( ! empty( $this->bulkActions ) )
		{
			$columns[ 'cb' ] = '<input type="checkbox" />';
		}
		
		if ( isset( $this->columns ) )
		{
			$columns = array_merge( $columns, $this->columns );
			return $columns;
		}
		
		foreach( $class::$columns as $key => $column )
		{
			$slug = NULL;
			$title = NULL;
			
			if ( is_array( $column ) )
			{
				$slug = $class::$prefix . $key;
				if ( isset( $column[ 'title' ] ) and is_string( $column[ 'title' ] ) )
				{
					$title = $column[ 'title' ];
				}
			}
			elseif ( is_string( $column ) )
			{
				$slug = $class::$prefix . $column;
			}
			
			if ( ! $title )
			{
				$title = str_replace( '_', ' ', $slug );
				$title = ucwords( $title );
			}
			
			$columns[ $slug ] = $title;
		}
		
		return $columns;
	}

	/**
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 * 
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 * 
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 * 
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 */
	public function get_bulk_actions() 
	{
		return $this->bulkActions;
	}
	
	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 * 
	 * @see $this->prepare_items()
	 **************************************************************************/
	public function process_bulk_action() 
	{
		$action = $this->current_action();
		
		if ( $action and array_key_exists( $action, $this->bulkActions ) )
		{
			$class = $this->activeRecordClass;
			foreach( $_POST[ 'item' ] as $item_id )
			{
				try
				{
					$item = $class::load( $item_id );
					if ( is_callable( array( $item, $action ) ) )
					{
						call_user_func( array( $item, $action ) );
					}
				}
				catch( \Exception $e ) { }
			}
		}
	}
	
	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 * 
	 * @global WPDB $wpdb
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	public function prepare_items( $where=array( '1=1' ) ) 
	{
		$class = $this->activeRecordClass;
		
		/**
		 * REQUIRED. Now we need to define our column headers. This includes a complete
		 * array of columns to be displayed (slugs & titles), a list of columns
		 * to keep hidden, and a list of columns that are sortable. Each of these
		 * can be defined in another method (as we've done here) before being
		 * used to build the value for our _column_headers property.
		 */
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		
		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column 
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array( $columns, $hidden, $sortable );
		
		/**
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		 */
		$this->process_bulk_action();
		
		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently 
		 * looking at. We'll need this later, so you should always include it in 
		 * your own package classes.
		 */
		$current_page = $this->get_pagenum();
		
		/**
		 * REQUIRED. Build the query and fetch the database results
		 */
		$db = Framework::instance()->db();
		
		$sortBy        = isset( $this->sortBy ) ? $this->sortBy : $class::$prefix . $class::$key;
		$sortOrder     = $this->sortOrder;
		$compiled      = $class::compileWhereClause( $where );
		$per_page      = $this->perPage;
		$start_at      = $current_page > 0 ? ( $current_page - 1 ) * $per_page : 0;
		
		$query          = "SELECT * FROM {$db->prefix}{$class::$table} WHERE {$compiled['where']}";
		$prepared_query = ! empty( $compiled[ 'params' ] ) ? $db->prepare( $query, $compiled[ 'params' ] ) : $query;
		
		
		$total_items   = $db->get_var( str_replace( 'SELECT * ', 'SELECT COUNT(*) ', $prepared_query ) );
		$this->items   = $db->get_results( $prepared_query . " ORDER BY {$sortBy} {$sortOrder} LIMIT {$start_at}, {$per_page}", ARRAY_A );
		
		/**
		 * Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array
		(
			'total_items' => $total_items,
			'per_page'    => $this->perPage,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

}
