<?php
/**
 * Plugin Class File
 *
 * Created:   March 2, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.2.4
 */
namespace Modern\Wordpress\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Active Record Controller
 */
class ActiveRecordController
{
	
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	string
	 */
	public static $recordClass;
	
	/**
	 * @var	array
	 */
	public $options = array();
	
	/**
 	 * Get plugin
	 *
	 * @return	\Modern\Wordpress\Plugin
	 */
	public function getPlugin()
	{
		if ( ! isset( $this->plugin ) ) {
			$this->plugin = \Modern\Wordpress\Framework::instance();
		}
		
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
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $options=array() )
	{
		$this->options = $options;
		if ( isset( $options['adminPage'] ) ) {
			$this->registerAdminPage( $options['adminPage'] );
		}
	}
	
	/**
	 * @var	Wordpress\AdminPage
	 */
	public $adminPage;
	
	/**
	 * Register the controller as an admin page
	 *
	 * @param	array			$options			Admin page options
	 */
	public function registerAdminPage( $options=array() )
	{
		$adminPage = new \Wordpress\AdminPage;
		
		$adminPage->title = isset( $options['title'] ) ? $options['title'] : array_pop( explode( '\\', static::$recordClass ) ) . ' Management';
		$adminPage->menu  = isset( $options['menu'] ) ? $options['menu'] : $adminPage->title;
		$adminPage->slug  = isset( $options['slug'] ) ? $options['slug'] : sanitize_title( str_replace( '\\', '-', static::$recordClass ) );
		$adminPage->capability = isset( $options['capability'] ) ? $options['capability'] : $adminPage->capability;
		$adminPage->icon = isset( $options['icon'] ) ? $options['icon'] : $adminPage->icon;
		$adminPage->position = isset( $options['position'] ) ? $options['position'] : NULL;
		$adminPage->type = isset( $options['type'] ) ? $options['type'] : $adminPage->type;
		$adminPage->parent = isset( $options['parent'] ) ? $options['parent'] : $adminPage->parent;
		
		$adminPage->applyToObject( $this, array() );
		
		$this->adminPage = $adminPage;
		return $this->adminPage;
	}
	
	/**
	 * Get action buttons
	 *
	 * @return	array
	 */
	public function getActionButtons()
	{
		return array( 
			'new' => array(
				'title' => __( 'Create New', 'mwp-framework' ),
				'href' => $this->getUrl( array( 'do' => 'new' ) ),
				'class' => 'btn btn-primary',
			)
		);
	}
	
	/**
	 * Get the action menu for this controller
	 *
	 * @return	string
	 */
	public function getActionsHtml()
	{
		return $this->getPlugin()->getTemplateContent( 'views/management/records/table_actions', array( 'plugin' => $plugin, 'class' => $class, 'controller' => $this, 'buttons' => $this->getActionButtons() ) );
	}
	
	/**
	 * Get the active record display table
	 *
	 * @return	Modern\Wordpress\Helpers\ActiveRecordTable
	 */
	public function getDisplayTable()
	{
		$class = static::$recordClass;
		$table = $class::createDisplayTable();
		$plugin = $this->getPlugin();
		$controller = $this;
		
		if ( isset( $this->options['columns'] ) ) {
			$table->columns = $this->options['columns'];
		}
		else
		{
			foreach( $class::$columns as $key => $opts ) {
				if ( is_array( $opts ) ) {
					$table->columns[ $class::$prefix . $key ] = $key;
				}
				else
				{
					$table->columns[ $class::$prefix . $opts ] = $opts;
				}
			}
		}
		
		/** Record row buttons **/
		if ( ! isset( $this->options['templates']['row_buttons'] ) or $this->options['templates']['row_buttons'] !== FALSE ) 
		{
			$buttons_template = isset( $this->options['templates']['row_buttons'] ) ? $this->options['templates']['row_buttons'] : 'views/management/records/row_buttons';
			$table->columns[ 'buttons' ] = '';
			$table->handlers[ 'buttons' ] = function( $row ) use ( $class, $controller, $plugin, $buttons_template ) {
				return $plugin->getTemplateContent( $buttons_template, array( 'plugin' => $plugin, 'class' => $class, 'row' => $row, 'controller' => $controller ) );				
			};
		}
		
		if ( isset( $this->options['sortable'] ) ) {
			$table->sortableColumns = $this->options['sortable'];
		}
		
		if ( isset( $this->options['searchable'] ) ) {
			$table->searchableColumns = $this->options['searchable'];
		}
		
		if ( isset( $this->options['bulk_actions'] ) ) {
			$table->bulkActions = $this->options['bulk_actions'];
		} else {
			$table->bulkActions = array(
				'delete' => 'Delete'
			);
		}
		
		if ( isset( $this->options['sort_by'] ) ) {
			$table->sortBy = $this->options['sort_by'];
		} 
		
		if ( isset( $this->options['sort_order'] ) ) {
			$table->sortOrder = $this->options['sort_order'];
		}
		
		if ( isset( $this->options['handlers'] ) ) {
			$table->handlers = array_merge( $table->handlers, $this->options['handlers'] );
		}
		
		return $table;
	}
	
	/**
	 * Get the controller url
	 *
	 * @param	array			$args			Optional query args
	 */
	public function getUrl( $args=array() )
	{
		return add_query_arg( $args, menu_page_url( $this->adminPage->slug, false ) );
	}
	
	/**
	 * Index Page
	 * 
	 * @return	string
	 */
	public function do_index()
	{
		$table = $this->getDisplayTable();
		$where = isset( $this->options['where'] ) ? $this->options['where'] : array('1=1');
		
		$table->read_inputs();
		$table->prepare_items( $where );
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/table', array( 'plugin' => $this->getPlugin(), 'controller' => $this, 'table' => $table ) );
	}
	
	/**
	 * View an active record
	 * 
	 * @param	int			$record_id				The active record id
	 * @return	void
	 */
	public function do_view( $record_id=NULL )
	{
		$class = static::$recordClass;
		$record_id = $record_id ?: ( isset( $_REQUEST[ 'id' ] ) ? $_REQUEST[ 'id' ] : 0 );
		$record = NULL;
		
		if ( $record_id )
		{
			try
			{
				$record = $class::load( $record_id );
			}
			catch( \OutOfRangeException $e ) { }
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/view', array( 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}

	/**
	 * Create a new active record
	 * 
	 * @param	int			$record_id				The active record id
	 * @return	void
	 */
	public function do_new()
	{
		$controller = $this;
		$class = static::$recordClass;		
		$form = $class::getForm();
		
		if ( $form->isValidSubmission() ) 
		{
			$record = new $class;
			$record->processForm( $form->getValues() );			
			$record->save();
			
			$form->processComplete( function() use ( $controller ) {
				wp_redirect( $controller->getUrl() );
			});
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/create', array( 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this ) );
	}
	
	/**
	 * Edit an active record
	 * 
	 * @param	int			$record_id				The active record id
	 * @return	void
	 */
	public function do_edit( $record_id=NULL )
	{
		$controller = $this;
		$class = static::$recordClass;
		$record_id = $_REQUEST['id'];
		
		try
		{
			$record = $class::load( $record_id );
		}
		catch( \OutOfRangeException $e ) { 
			echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be found.', 'mwp-framework' ) ) );
			return;
		}
		
		$form = $class::getForm( $record );
		
		if ( $form->isValidSubmission() ) 
		{
			$record->processForm( $form->getValues() );			
			$record->save();
			
			$form->processComplete( function() use ( $controller ) {
				wp_redirect( $controller->getUrl() );
			});
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}

	/**
	 * Delete an active record
	 * 
	 * @param	int			$record_id				The active record id
	 * @return	void
	 */
	public function do_delete( $record_id=NULL )
	{
		$controller = $this;
		$class = static::$recordClass;
		$record_id = $_REQUEST['id'];
		
		try
		{
			$record = $class::load( $record_id );
			$form = $record->getDeleteForm();
			
			if ( $form->isValidSubmission() )
			{
				if ( $form->getForm()->getClickedButton()->getName() == 'confirm' ) {
					$record->delete();
				}
				
				$form->processComplete( function() use ( $controller ) {
					wp_redirect( $controller->getUrl() );
				});
			}
		}
		catch( \OutOfRangeException $e ) { 
			echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be found.', 'mwp-framework' ) ) );
			return;
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/delete', array( 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}	
	
}
