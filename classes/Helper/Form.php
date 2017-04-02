<?php
/**
 * Plugin Class File
 *
 * Created:   January 25, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.1.4
 */
namespace Modern\Wordpress\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Framework;

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
	 * @var	string		Output themes
	 */
	public $themes = array();
	
	/**
	 * Set template
	 *
	 * @param	string|array		$themes		The form themes (or themes) to pick templates from
	 * @return	this							Chainable
	 */
	public function setTheme( $themes )
	{
		$themes = (array) $themes;
		$this->themes = $themes;
		
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
		$this->plugin = $plugin ?: Framework::instance();
		
		$engines = array();
		
		if ( isset( $plugin ) )
		{
			$engines[] = new \Modern\Wordpress\Symfony\TemplateEngine( $plugin );
		}
		
		$engines[] = new \Modern\Wordpress\Symfony\TemplateEngine( Framework::instance() );
		$templateEngine = new \Symfony\Component\Templating\DelegatingEngine( $engines );
		$formBuilder = Framework::instance()->getFormFactory()->createBuilder();
		
		$this->setTemplateEngine( $templateEngine );
		$this->setFormBuilder( $formBuilder );
		$this->setEngines( $engines );
	}
	
	/**
	 * @var		EngineInterface
	 */
	protected $templateEngine;
	
	/**
	 * @var	FormRenderHelper
	 */
	public $renderHelper;
	
	/**
	 * Set the template rendering engine
	 *
	 * @param	EngineInterface			$templateEngine				The template rendering engine
	 * @return	void
	 */
	public function setTemplateEngine( \Symfony\Component\Templating\EngineInterface $templateEngine )
	{
		$this->templateEngine = $templateEngine;
	}
	
	/**
	 * Get template rendering engine
	 *
	 * @return	EngineInterface
	 */
	public function getTemplateEngine()
	{
		return $this->templateEngine;
	}
	
	/** 
	 * @var		FormBuilderInterface
	 */
	protected $formBuilder;
	
	/**
	 * Set the form builder
	 *
	 * @param	FormBuilderInterface		$formBuilder			The form builder
	 * @return	void
	 */
	public function setFormBuilder( \Symfony\Component\Form\FormBuilderInterface $formBuilder )
	{
		$this->formBuilder = $formBuilder;
	}
	
	/** 
	 * Get the form builder
	 *
	 * @return	\Symfony\Component\Form\FormBuilderInterface
	 */
	public function getFormBuilder()
	{
		return $this->formBuilder;
	}
	
	/**
	 * @var		array
	 */
	protected $engines = array();
	
	/**
	 * Set the template engines cache
	 *
	 * @param	array		$engines			The form view
	 * @return	void
	 */
	public function setEngines( $engines )
	{
		$this->engines = $engines;
	}
	
	/** 
	 * Get the template engines
	 *
	 * @return	array
	 */
	public function getEngines()
	{
		return $this->engines;
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
	 * Get the plugin slug for use in hooks
	 *
	 *@return	string
	 */
	public function getPluginSlug()
	{
		return str_replace( '-', '_', $this->getPlugin()->pluginSlug() );
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
		
		// pick a semi-unique scope to differentiate between possible fields on other forms with the same name
		if ( ! isset( $field[ 'scope' ] ) ) {
			$field[ 'scope' ] = substr( md5( $this->getPluginSlug() . '_' . $this->name ), 10, 4 );
		}
		
		$field = $this->applyFilters( 'field', $field );
		
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
	public $form_submission;
	
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
	 *
	 * @return	bool
	 */
	public function isSubmitted()
	{
		$form_submission = $this->getSubmissionData();
		
		return ( 
			isset( $_REQUEST[ 'form_id' ] ) and 
			$_REQUEST[ 'form_id' ] == $this->getPluginSlug() . '_' . $this->name and 
			$form_submission !== false
		);
	}
	
	/**
	 * Check for valid form submission
	 *
	 * @return	bool
	 */
	public function isValidSubmission()
	{
		if ( ! class_exists( 'Piklist_Form' ) ) {
			return false;
		}
		
		if ( $this->isSubmitted() )
		{
			$form_submission = $this->getSubmissionData();
			
			if ( $this->useNonce ) {
				\Piklist_Form::check_nonce();
			}
			
			$valid = ( 
				$form_submission[ 'valid' ] and
				(
					! $this->useNonce or
					\Piklist_Form::valid()
				)
			);
			
			return $this->applyFilters( 'valid', $valid );
		}
		
		return false;
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
			
			$values = $this->applyFilters( 'values', $values ); 
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
		$errors = array();

		if ( is_array( $form_submission = $this->getSubmissionData() ) ) {
			foreach( $form_submission[ 'fields_data' ] as $scope => $fields ) {
				foreach( $fields as $field_name => $field ) {
					$_errors = \Piklist_Validate::get_errors( $field );
					if ( ! empty( $_errors ) ) {
						$errors[ $field_name ] = $_errors;
					}
				}
			}
		}
		
		return $this->applyFilters( 'errors', $errors );
	}
	
	/**
	 * Get form output
	 *
	 * @return	string
	 */
	public function render()
	{
		$template_vars = $this->applyFilters( 'render', array( 
			'formWrapper' => $this,
			'form' => $this->getFormBuilder()->getForm()->createView(),
		) );
		
		$this->renderHelper = new \Modern\Wordpress\Helper\FormRenderHelper( 
			new \Symfony\Component\Form\FormRenderer( 
				new \Symfony\Component\Form\Extension\Templating\TemplatingRendererEngine(
					$this->getTemplateEngine(), array_merge( $this->themes, array( 'form' ) )
				)
			)
		);
		
		foreach( $this->engines as $engine ) {
			$engine->addHelpers( array( $this->renderHelper ) );
		}
		
		return $this->renderHelper->form( $template_vars[ 'form' ], $template_vars );
	}
	
	/**
	 * Signal that the processing of a successful form submission is complete, allowing hooks to run
	 *
	 * @param	callback		$callback			An executable callback to filter and run
	 * @return	mixed
	 */
	public function processComplete( $callback )
	{
		$callback = $this->applyFilters( 'processed', $callback );
		return call_user_func( $callback );
	}
	
	/**
	 * Apply a standard set of filters
	 *
	 * @param	string		$action			The action being filtered
	 * @return	mixed
	 */
	public function applyFilters( $action, $value )
	{
		// allow global modifications for this action
		$value = apply_filters( 'mwp_form_' . $action, $value, $this );
		
		// allow plugin specific modifications for this action
		$value = apply_filters( 'mwp_form_' . $action . '_' . $this->getPluginSlug(), $value, $this );
		
		// allow form specific modifications for this action
		$value = apply_filters( 'mwp_form_' . $action . '_' . $this->getPluginSlug() . '_' . $this->name, $value, $this );
		
		return $value;
	}
	
}
