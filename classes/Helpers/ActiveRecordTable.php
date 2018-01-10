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

namespace Modern\Wordpress\Helpers; 

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
	 * Various information about the current table.
	 *
	 */
	public $_args;

	/**
	 * Various information needed for displaying the pagination.
	 *
	 */
	public $_pagination_args = array();

	/**
	 * The current screen.
	 *
	 */
	public $screen;

	/**
	 * Cached bulk actions.
	 *
	 */
	public $_actions;

	/**
	 * Cached pagination output.
	 *
	 */
	public $_pagination;

	/**
	 * The view switcher modes.
	 *
	 */
	public $modes = array();

	/**
	 * Stores the value returned by ->get_column_info().
	 *
	 */
	public $_column_headers;

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
	 * @var	array			Sortable columns
	 */
	public $sortableColumns = array();
	
	/**
	 * @var	array			Searchable columns
	 */
	public $searchableColumns = array();
	
	/**
	 * @var	string			Hard filters (ActiveRecord where clauses)
	 */
	public $hardFilters = array();
	
	/**
	 * @var array			Optional display handlers for columns
	 */
	public $handlers = array();
	
	/**
	 * @var	bool
	 */
	public $displayTopNavigation = true;
	
	/**
	 * @var	bool
	 */
	public $displayBottomNavigation = true;
	
	/**
	 * @var	bool
	 */
	public $displayTopHeaders = true;
	
	/**
	 * @var	bool
	 */
	public $displayBottomHeaders = false;	
	
	/**
	 * @var	Modern\Wordpress\Plugin
	 */
	protected $plugin;
	
	/**
	 * @var	string
	 */
	public $tableTemplate = 'views/management/records/table';
	
	/**
	 * @var	string
	 */
	public $rowTemplate = 'views/management/records/table_row';
	
	/**
	 * @var	string
	 */
	public $rowActionsTemplate = 'views/management/records/row_actions';
	
	/**
	 * @var	ActiveRecordController
	 */
	protected $controller;
	
	/**
	 * @var	string
	 */
	public $viewModel = 'mwp-forms-controller';
	
	/**
	 * @var	string
	 */
	public $sequencingColumn;
	
	/**
	 * @var	string
	 */
	public $parentColumn;
	
	/**
	 * @var string
	 */
	public $actionsColumn;
	
	/**
	 * Set the controller
	 */
	public function setController( $controller )
	{
		$this->controller = $controller;
	}
	
	/**
	 * Get the controller
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Set the plugin
	 */
	public function setPlugin( $plugin )
	{
		$this->plugin = $plugin;
	}
	
	/**
	 * Get the plugin
	 */
	public function getPlugin()
	{
		if ( ! isset( $this->plugin ) ) {
			$recordClass = $this->activeRecordClass;
			$pluginClass = $recordClass::$plugin_class;
			if ( class_exists( $pluginClass ) and is_subclass_of( $pluginClass, 'Modern\Wordpress\Plugin' ) ) {
				$this->plugin = $pluginClass::instance();
			}
		}
		
		return $this->plugin;
	}
	
    /**
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     */
    public function __construct( $args=array() )
	{
		if ( isset( $args['recordClass'] ) ) {
			$this->activeRecordClass = $args['recordClass'];
			unset( $args['recordClass'] );
		}
		
		//Set parent defaults
		parent::__construct( $args );		
    }
	
	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @return array
	 */
	public function get_column_info() 
	{
		return parent::get_column_info();
	}
	
	/**
	 * Generates content for a single row of the table
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) 
	{
		$recordClass = $this->activeRecordClass;
		
		if ( $this->rowTemplate ) {
			echo $this->getPlugin()->getTemplateContent( $this->rowTemplate, array( 'table' => $this, 'item' => $item ) );
		} else {
			parent::single_row( $item );
		}
	}
	
	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	public function single_row_columns( $item ) {
		return parent::single_row_columns( $item );
	}
	
	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function no_items() {
		_e( 'No ' . ( $this->_args['plural'] ?: 'items' ) . ' found.' );
	}
	
	/**
	 * Display the table
	 *
	 * @since 3.1.0
	 * @access public
	 */
	public function display() 
	{
		if ( $this->tableTemplate ) {
			echo $this->getPlugin()->getTemplateContent( $this->tableTemplate, array( 'table' => $this ) );
		} else {
			parent::display();
		}
	}
	
	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 3.1.0
	 * @access protected
	 * @param string $which
	 */
	public function display_tablenav( $which ) {
		return parent::display_tablenav( $which );
	}
	
	/**
	 * @var	array
	 */
	public $tableClasses = array( 'widefat', 'fixed', 'striped' );
	
	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	public function get_table_classes() {
		return $this->tableClasses;
	}
	
	/**
	 * Add table css class
	 *
	 * @param	string		$classname			The classname to add
	 * @return	void
	 */
	public function addTableClass( $classname )
	{
		if ( ! in_array( $classname, $this->tableClasses ) ) {
			$this->tableClasses[] = $classname;
		}
	}
	
	/**
	 * Remove a table css class
	 *
	 * @param	string		$classname			The classname to remove
	 * @return	void
	 */
	public function removeTableClass( $classname )
	{
		$tableClasses = array_flip( $this->tableClasses );
		unset( $tableClasses[$classname] );
		$this->tableClasses = array_flip( $tableClasses );
	}
	
	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @param object $item        The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 * @return string The row actions HTML, or an empty string if the current column is the primary column.
	 */
	public function handle_row_actions( $item, $column_name, $primary ) 
	{
		$default_row_actions = parent::handle_row_actions( $item, $column_name, $primary );
		
		if ( $this->getController() ) {		
			$button_col = $this->actionsColumn ?: $primary;
			if ( $column_name === $button_col and $this->getController() ) {
				$default_row_actions .= $this->getControllerActionsHTML( $item, $default_row_actions );
			}
		} 
		
		return $default_row_actions;
 	}
	
	/**
	 * Get the row actions for an item
	 *
	 * @param	array		$item 						The item being acted upon
	 * @param	string		$default_row_actions		The default provided core row actions
	 * @return	string
	 */
	public function getControllerActionsHTML( $item, $default_row_actions='' )
	{
		if ( $controller = $this->getController() ) {
			try {
				$recordClass = $this->activeRecordClass;
				$record = $recordClass::load( $item[ $recordClass::$prefix . $recordClass::$key ] );
				return $this->getPlugin()->getTemplateContent( $this->rowActionsTemplate, array( 
					'controller' => $controller, 
					'record' => $record, 
					'table' => $this, 
					'actions' => $record->getControllerActions(), 
					'default_row_actions' => $default_row_actions,
				));
			} catch( \OutOfRangeException $e ) { }
		}
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
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return $this->sortableColumns;
	}
	
	/**
	 * Get searchable columns
	 * 
	 * @return	array
	 */
	public function get_searchable_columns() {
		return $this->searchableColumns;
	}
	
	/**
	 * Display the pagination.
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items = $this->_pagination_args['total_items'];
		$total_pages = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		if ( 'top' === $which && $total_pages > 1 ) {
			$this->screen->render_screen_reader_content( 'heading_pagination' );
		}

		$output = '<span class="displaying-num">' . sprintf( _n( '%s ' . ( $this->_args['singular'] ?: 'item' ), '%s ' . ( $this->_args['plural'] ?: 'items' ), $total_items ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();
		$removable_query_args = wp_removable_query_args();

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

		$current_url = remove_query_arg( $removable_query_args, $current_url );

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span></span>';

		$disable_first = $disable_last = $disable_prev = $disable_next = false;

 		if ( $current == 1 ) {
			$disable_first = true;
			$disable_prev = true;
 		}
		if ( $current == 2 ) {
			$disable_first = true;
		}
 		if ( $current == $total_pages ) {
			$disable_last = true;
			$disable_next = true;
 		}
		if ( $current == $total_pages - 1 ) {
			$disable_last = true;
		}

		if ( $disable_first ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( remove_query_arg( 'paged', $current_url ) ),
				__( 'First page' ),
				'&laquo;'
			);
		}

		if ( $disable_prev ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
				__( 'Previous page' ),
				'&lsaquo;'
			);
		}

		if ( 'bottom' === $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
		} else {
			$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		if ( $disable_next ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
				__( 'Next page' ),
				'&rsaquo;'
			);
		}

		if ( $disable_last ) {
			$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
		} else {
			$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
				esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
				__( 'Last page' ),
				'&raquo;'
			);
		}

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

		echo $this->_pagination;
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
			foreach( $_POST[ $this->_args['singular'] ] as $item_id )
			{
				try
				{
					$item = $class::load( $item_id );
					if ( is_callable( array( $item, $action ) ) ) {
						call_user_func( array( $item, $action ) );
					}
				}
				catch( \Exception $e ) { }
			}
		}
	}
	
	/**
	 * Read inputs
	 *
	 * This method will read script input parameters to the script and set the state 
	 * of the table accordingly.
	 *
	 * @return	 void
	 */
	public function read_inputs()
	{
		if ( isset( $_REQUEST['orderby'] ) and in_array( $_REQUEST['orderby'], array_map( function( $arr ) { return is_array( $arr ) ? $arr[0] : $arr; }, $this->get_sortable_columns() ) ) ) {
			$this->sortBy = $_REQUEST['orderby'];
		}
		
		if ( isset( $_REQUEST['order'] ) and in_array( strtolower( $_REQUEST['order'] ), array( 'asc', 'desc' ) ) ) {
			$this->order = $_REQUEST['order'];
		}
		
		if ( $searchable_columns = $this->get_searchable_columns() ) 
		{
			if ( isset( $_REQUEST['s'] ) and $_REQUEST['s'] ) 
			{
				$phrase = $_REQUEST['s'];
				$clauses = array();
				$where = array();
				foreach( $searchable_columns as $column_name => $column_config ) 
				{
					$column_config = is_array( $column_config ) ? $column_config : array( 'type' => 'contains', 'combine_words' => 'OR' );
					$type = is_array( $column_config ) and isset( $column_config['type'] ) ? $column_config['type'] : 'contains';
					
					switch( $type ) 
					{
						case 'contains':
						
							if ( isset( $column_config['combine_words'] ) ) {
								$word_clauses = array();
								foreach( explode( ' ', $phrase ) as $word ) {
									$word_clauses[] = 'LOWER(' . $column_name . ') LIKE %s';
									$where[] = '%' . mb_strtolower( $word ) . '%';								
								}
								$clauses[] = '(' . implode( ( strtolower( $column_config['combine_words'] ) == 'or' ? ' OR ' : ' AND ' ), $word_clauses ) . ')';
							} else {
								$clauses[] = 'LOWER(' . $column_name . ') LIKE %s';
								$where[] = '%' . mb_strtolower( $phrase ) . '%';
							}
							break;
							
						case 'equals':
						
							$clauses[] = '$column_name = %s';
							$where[] = $phrase;
							break;
							
					}
				}
				array_unshift( $where, '(' . implode( ') OR (', $clauses ) . ')' );
				$this->hardFilters[] = $where;
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
		$compiled      = $class::compileWhereClause( array_merge( $this->hardFilters, array( $where ) ) );
		$per_page      = $this->perPage;
		$start_at      = $current_page > 0 ? ( $current_page - 1 ) * $per_page : 0;
		$prefix        = $class::$site_specific ? $db->prefix : $db->base_prefix;
		
		$query          = "SELECT * FROM {$prefix}{$class::$table} WHERE {$compiled['where']}";
		$prepared_query = ! empty( $compiled[ 'params' ] ) ? $db->prepare( $query, $compiled[ 'params' ] ) : $query;		
		
		$total_items   = $db->get_var( str_replace( 'SELECT * ', 'SELECT COUNT(*) ', $prepared_query ) );
		$this->items   = $db->get_results( $prepared_query . " ORDER BY {$sortBy} {$sortOrder} LIMIT {$start_at}, {$per_page}", ARRAY_A );
		
		/**
		 * Register our pagination options & calculations.
		 */
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $this->perPage,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * Get the view model attr
	 */
	public function getViewModelAttr()
	{
		if ( $this->viewModel ) {
			return ' data-view-model="' . $this->viewModel . '"';
		}
		
		return '';
	}
	
	/**
	 * Get the sequencing data bind attribute 
	 * 
	 * @return	string
	 */
	public function getSequencingBindAttr( $func='sequenceableRecords', $options=array() )
	{
		if ( $this->sequencingColumn ) {
			return ' data-bind="' . $func . ': ' . esc_attr( json_encode( array( 
				'class' => $this->activeRecordClass, 
				'column' => $this->sequencingColumn, 
				'parent' => $this->parentColumn,
				'options' => $options ?: null,
			))) . 
			'"';
		}
		
		return '';
	}
	
	/**
	 * Get the table display and return it
	 *
	 * @return	string
	 */
	public function getDisplay()
	{
		ob_start();
		$this->display();
		return ob_get_clean();
	}
	
}
