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
	public $recordClass;
	
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
	 * @param	string		$recordClass			The active record class
	 * @param	array		$options				Optional configuration options
	 * @return	void
	 */
	public function __construct( $recordClass, $options=array() )
	{
		$this->recordClass = $recordClass;
		$pluginClass = $recordClass::$plugin_class;
		
		$this->setPlugin( $pluginClass::instance() );
		$this->options = $options;
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
		
		$adminPage->title = isset( $options['title'] ) ? $options['title'] : array_pop( explode( '\\', $this->recordClass ) ) . ' Management';
		$adminPage->menu  = isset( $options['menu'] ) ? $options['menu'] : $adminPage->title;
		$adminPage->slug  = isset( $options['slug'] ) ? $options['slug'] : sanitize_title( str_replace( '\\', '-', $this->recordClass ) );
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
		$recordClass = $this->recordClass;
		
		return array( 
			'new' => array(
				'title' => __( $recordClass::$lang_create . ' ' . $recordClass::$lang_singular ),
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
	public function createDisplayTable()
	{
		$recordClass = $this->recordClass;
		$table = $recordClass::createDisplayTable();
		$table->setController( $this );
		$plugin = $this->getPlugin();
		$controller = $this;
		
		if ( isset( $this->options['columns'] ) ) {
			$table->columns = $this->options['columns'];
		}
		else
		{
			foreach( $recordClass::$columns as $key => $opts ) {
				if ( is_array( $opts ) ) {
					$table->columns[ $recordClass::$prefix . $key ] = $key;
				}
				else
				{
					$table->columns[ $recordClass::$prefix . $opts ] = $opts;
				}
			}
		}
		
		/** Record row actions **/
		if ( isset( $this->options['templates']['row_actions'] ) ) {
			$table->rowActionsTemplate = $this->options['templates']['row_actions'];
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
		$table = $this->createDisplayTable();
		$where = isset( $this->options['where'] ) ? $this->options['where'] : array('1=1');
		
		$table->read_inputs();
		$table->prepare_items( $where );
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/table', array( 'plugin' => $this->getPlugin(), 'controller' => $this, 'table' => $table ) );
	}
	
	/**
	 * View an active record
	 * 
	 * @param	ActiveRecord			$record				The active record
	 * @return	void
	 */
	public function do_view( $record=NULL )
	{
		$class = $this->recordClass;
		
		if ( ! $record )
		{
			try
			{
				$record = $class::load( $_REQUEST[ 'id' ] );
			}
			catch( \OutOfRangeException $e ) {
 				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be loaded. Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ), 'mwp-framework' ) ) );
				return;
			}
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/view', array( 'title' => $record->viewTitle(), 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}

	/**
	 * Create a new active record
	 * 
	 * @param	ActiveRecord			$record				The active record id
	 * @return	void
	 */
	public function do_new( $record=NULL )
	{
		$controller = $this;
		$class = $this->recordClass;
		$record = $record ?: new $class;
		
		$form = $class::getForm( $record );
		
		if ( $form->isValidSubmission() ) 
		{
			$record->processForm( $form->getValues() );			
			$record->save();
			
			$form->processComplete( function() use ( $controller, $record ) {
				wp_redirect( $controller->getUrl() );
			});
		}
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/create', array( 'title' => $class::createTitle(), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this ) );
	}
	
	/**
	 * Edit an active record
	 * 
	 * @param	ActiveRecord|NULL			$record				The active record
	 * @return	void
	 */
	public function do_edit( $record=NULL )
	{
		$controller = $this;
		$class = $this->recordClass;
		
		if ( ! $record ) {
			try
			{
				$record = $class::load( $_REQUEST['id'] );
			}
			catch( \OutOfRangeException $e ) { 
 				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be loaded. Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ), 'mwp-framework' ) ) );
				return;
			}
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
		
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/edit', array( 'title' => $record->editTitle(), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}

	/**
	 * Delete an active record
	 * 
	 * @param	ActiveRecord|NULL			$record				The active record
	 * @return	void
	 */
	public function do_delete( $record=NULL )
	{
		$controller = $this;
		$class = $this->recordClass;
		
		if ( ! $record ) {
			try
			{
				$record = $class::load( $_REQUEST['id'] );
			}
			catch( \OutOfRangeException $e ) { 
 				echo $this->getPlugin()->getTemplateContent( 'component/error', array( 'message' => __( 'The record could not be loaded. Class: ' . $this->recordClass . ' ' . ', ID: ' . ( (int) $_REQUEST['id'] ), 'mwp-framework' ) ) );
				return;
			}
		}
		
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
	
		echo $this->getPlugin()->getTemplateContent( 'views/management/records/delete', array( 'title' => $record->deleteTitle(), 'form' => $form, 'plugin' => $this->getPlugin(), 'controller' => $this, 'record' => $record ) );
	}	
	
}
