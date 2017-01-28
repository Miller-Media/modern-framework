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
	 * @var submitButton
	 */
	public $submitButton = "Save";
	
	/**
	 * @var	string		Output template
	 */
	public $template = 'form/form';
	
	/**
	 * Set template
	 *
	 * @param	string		$template			The new template
	 * @return	this							Chainable
	 */
	public function setTemplate( $template )
	{
		$this->template = $template;
		return $this;
	}
	
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
	public function __construct( $name, \Modern\Wordpress\Plugin $plugin=NULL )
	{
		$this->name = $name;
		$this->plugin = $plugin ?: \Modern\Wordpress\Framework::instance();
	}
	
	/**
	 * @var	array		Form fields
	 */
	public $fields = array();
	
	/**
	 * @var	bool		CSRF Protect
	 */
	public $useNonce = true;
	
	/**
	 * Enable or disable wordpress nonce validation
	 *
	 * @param	bool			$bool			Either true for ON or false for OFF
	 * @return	this							Chainable
	 */
	public function nonce( $bool )
	{
		$this->useNonce = $bool;
		return $this;
	}

	/**
	 * Add a field to the form
	 *
	 * @param	array		$field			The piklist field definition
	 * @return	this						Chainable
	 */
	public function addField( $field )
	{
		if ( ! class_exists( 'Piklist' ) )
		{
			return $this;
		}
		
		if ( ! isset( $field[ 'field' ] ) or ! $field[ 'field' ] ) {
			return $this;
		}
		
		if ( ! isset( $field[ 'scope' ] ) ) {
			$field[ 'scope' ] = 'data';
		}
		
		$field = apply_filters( 'mwp_form_field_' . $this->name, $field, $this );
		
		// Add our own validation rules
		static::addFieldValidation( $field );
		
		$this->fields[ $field[ 'field' ] ] = $field;
		
		return $this;
	}
	
	/**
	 * Recursively add mwp callbacks to a field that will sanitize and validate input
	 *
	 * @param	array		$field			The field definition
	 * @return	void
	 */
	public static function addFieldValidation( &$field )
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
				static::addFieldValidation( $_field );
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
		
		$this->form_submission = $this->piklistContext( function() { return \Piklist_Validate::check(); } );
		
		return $this->form_submission;
	}
	
	/**
	 * Check if form was submitted
	 *
	 * @return	bool
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
		
		if ( ! class_exists( 'Piklist_Form' ) ) {
			return false;
		}
		
		if ( $this->useNonce ) {
			\Piklist_Form::check_nonce();
		}
		
		$valid = ( 
			$form_submission !== false and 
			is_array( $form_submission ) and 
			$form_submission[ 'valid' ] and
			(
				! $this->useNonce or
				\Piklist_Form::valid()
			)
		);
		
		return apply_filters( 'mwp_form_valid_' . $this->name, $valid, $this );
	}
	
	/**
	 * Get submitted form values
	 *
	 * @return	array
	 */
	public function getValues()
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
			
			$values = apply_filters( 'mwp_form_values_' . $this->name, $values, $this ); 
		}
		
		return $values;
	}
	
	/**
	 * Get form submission errors
	 *
	 * @return	array			Fields that had errors
	 */
	public function getErrors()
	{
		$form = $this;
		return $this->piklistContext( function() use ( $form ) 
		{
			$errors = array();

			if ( is_array( $form_submission = $form->getSubmissionData() ) ) {
				foreach( $form_submission[ 'fields_data' ] as $scope => $fields ) {
					foreach( $fields as $field_name => $field ) {
						$_errors = \Piklist_Validate::get_errors( $field );
						if ( ! empty( $_errors ) ) {
							$errors[ $field_name ] = $_errors;
						}
					}
				}
			}
			
			return apply_filters( 'mwp_form_errors_' . $form->name, $errors, $form );
		});
	}
	
	/**
	 * Get form output
	 *
	 * @return	string
	 */
	public function render()
	{
		$form = $this;
		return $this->piklistContext( function() use ( $form ) 
		{
			if ( ! class_exists( 'Piklist' ) )
			{
				return "Form cannot be displayed because it requires the 'Piklist' plugin to be installed.";
			}
			
			// This makes sure the piklist static states represent this form
			\Piklist_Validate::check();
			
			$form_rows = array();
			
			foreach( $form->fields as $field_name => $field )
			{
				$form_rows[ $field_name ] = $form->getPlugin()->getTemplateContent( 'form/form-row', array( 
					'field_name' => $field_name, 
					'field' => $field, 
					'field_html' => \Piklist_Form::render_field( $field, true ) 
				) );
			}
			
			if ( $form->submitButton ) {
				$form_rows[] = \Piklist_Form::render_field( array(
					'type' => 'submit',
					'value' => $form->submitButton,
					'attributes' => array(
						'class' => 'button-primary',
					)
				), true ); 
			}

			ob_start();
			
			// Piklist saves the added fields to a transient and outputs its own hidden form inputs for validation
			\Piklist_Form::save_fields();
			
			$hidden_fields = ob_get_clean();
			
			return $form->getPlugin()->getTemplateContent( $this->template, array( 'form' => $form, 'form_rows' => $form_rows, 'hidden_fields' => $hidden_fields ) );
		});
	}
	
	/**
	 * Execute some piklist code in context of this form (by switching Piklist prefixes)
	 *
	 * @param		callback			$callback			A callable
	 * @return		mixed
	 */
	public function piklistContext( $callback )
	{
		// save the current prefix
		//$_prefix = \Piklist::$prefix;
		
		// set the prefix to this form id
		//\Piklist::$prefix = $this->name;
		
		// execute the callback
		$result = call_user_func( $callback );
		
		// change the prefix back
		//\Piklist::$prefix = $_prefix;
		
		// done.
		return $result;
	}
	
}
