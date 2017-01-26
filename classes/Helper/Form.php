<?php
/**
 * Plugin Class File
 *
 * Created:   January 25, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */
namespace Modern\Wordpress\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Form Class
 */
class Form
{	
	/**
	 * @var 	\Modern\Wordpress\Plugin		Provides access to the plugin instance
	 */
	protected $plugin;
	
	/**
	 * @var	string
	 */
	public $name;	 
	
	/**
	 * @var	string
	 */
	public $method = "POST";
	
	/**
	 * @var	string
	 */
	public $action = "";
	
	/**
	 * @var submitString
	 */
	public $submitString = "Save";
	
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
	 * Constructor
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin			The plugin to associate this class with, or NULL to auto-associate
	 * @return	void
	 */
	public function __construct( $name=NULL, \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->name = $name;
		$this->plugin = $plugin ?: \Modern\Wordpress\Framework::instance();
	}
	
	/**
	 * @var	array		Form fields
	 */
	public $fields = array();

	/**
	 * Add a field to the form
	 *
	 * @param	array		$field			The piklist field definition
	 * @return	bool
	 */
	public function addField( $field )
	{
		if ( ! class_exists( 'Piklist' ) )
		{
			return;
		}
		
		if ( ! isset( $field[ 'field' ] ) or ! $field[ 'field' ] ) {
			return false;
		}
		
		if ( ! isset( $field[ 'scope' ] ) ) {
			$field[ 'scope' ] = 'data';
		}
		
		// Add our own validation rules
		static::addCallbacks( $field );
		
		$this->fields[ $field[ 'field' ] ] = $field;
	}
	
	/**
	 * Recursively add mwp callbacks to sanitize and validate fields
	 *
	 * @param	array		$field			The field definition
	 * @return	void
	 */
	public static function addCallbacks( &$field )
	{
		if ( isset( $field[ 'choices' ] ) ) {
			$field[ 'validate' ][] = array( 'type' => 'mwp_validate_choices' );
		}
		
		if ( $field[ 'type' ] == 'number' ) {
			$field[ 'sanitize' ][] = array( 'type' => 'mwp_sanitize_number' );
			$field[ 'validate' ][] = array( 'type' => 'mwp_validate_number' );
		}
		
		
		// Recursively add callbacks to sub fields
		if ( isset( $field[ 'fields' ] ) and is_array( $field[ 'fields' ] ) ) {
			foreach( $field[ 'fields' ] as &$_field ) {
				static::addCallbacks( $_field );
			}
		}
	}
	
	/**
	 * @var array		Form Submission Cache
	 */
	protected $form_submission;
	
	/**
	 * Get the form submission data
	 *
	 * @return	array|false
	 */
	public function getSubmissionData()
	{
		if ( ! class_exists( 'Piklist_Validate' ) )
		{
			return false;
		}
		
		if ( isset( $this->form_submission ) )
		{
			return $this->form_submission;
		}
		
		$this->form_submission = \Piklist_Validate::check();
		return $this->form_submission;
	}
	
	/**
	 * Check if form was submitted
	 */
	public function isSubmitted()
	{
		return $this->getSubmissionData() !== false;
	}
	
	/**
	 * Check for valid form submission
	 *
	 * @return	bool
	 */
	public function isValidSubmission()
	{
		$form_submission = $this->getSubmissionData();
		return ( $form_submission !== false and is_array( $form_submission ) and $form_submission[ 'valid' ] );
	}
	
	/**
	 * Get submitted form values
	 *
	 * @return	array
	 */
	public function values()
	{
		$values = array();
		$form_submission = $this->getSubmissionData();
		
		if ( $form_submission !== false )
		{
			foreach( $form_submission[ 'fields_data' ] as $scope => $fields ) {
				foreach( $fields as $field_name => $field ) {
					if ( in_array( $field_name, array_keys( $this->fields ) ) ) {
						$values[ $field_name ] = $field[ 'request_value' ];
					}
				}
			}
		}
		
		return $values;
	}
	
	/**
	 * Get form submission errors
	 */
	public function errors()
	{
		$errors = array();

		if ( is_array( $form_submission = $this->getSubmissionData() ) )
		{
			foreach( $form_submission[ 'fields_data' ] as $scope => $fields ) {
				foreach( $fields as $field_name => $field ) {
					$_errors = \Piklist_Validate::get_errors( $field );
					if ( ! empty( $_errors ) )
					{
						$errors[ $field_name ] = $_errors;
					}
				}
			}
		}
		
		return $errors;
	}
	
	/**
	 * Get form output
	 *
	 * @return	string
	 */
	public function render()
	{
		if ( ! class_exists( 'Piklist' ) )
		{
			return "This form cannot be displayed because it requires the 'Piklist' plugin to be installed.";
		}
		
		$form_rows = array();
		
		foreach( $this->fields as $field_name => $field )
		{
			$form_rows[ $field_name ] = $this->plugin->getTemplateContent( 'form/form-row', array( 
				'field_name' => $field_name, 
				'field' => $field, 
				'field_html' => \Piklist_Form::render_field( $field, true ) 
			) );
		}
		
		if ( $this->submitString ) {
			$form_rows[] = \Piklist_Form::render_field( array(
				'type' => 'submit',
				'value' => $this->submitString,
				'attributes' => array(
					'class' => 'button-primary',
				)
			), true ); 
		}

		ob_start();
		\Piklist_Form::save_fields();
		$hidden_fields = ob_get_clean();
		
		return $this->plugin->getTemplateContent( 'form/form', array( 'form' => $this, 'form_rows' => $form_rows, 'hidden_fields' => $hidden_fields ) );		
	}
	
}
