<?php
/**
 * Widget Class (WP_Widget)
 *
 * Created:    Nov 20, 2016
 *
 * @package     Modern Wordpress Framework
 * @author      Kevin Carwile
 * @since       1.0.0
 */

namespace Modern\Wordpress\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Provides a base class to support rapid development of widgets.
 */
abstract class Widget extends \WP_Widget
{
	/**
	 * @var	string	Plugin name
	 */
	public $name = 'Modern Wordpress Widget';
	
	/**
	 * @var	string	Plugin description
	 */
	public $description = 'A modern wordpress widget';
	
	/**
	 * @var string	HTML class to apply to widget
	 */
	public $classname;
	
 	/**
	 * @var	Plugin	The associated plugin
	 */
	protected static $plugin;
		
	/**
	 * @var array	Settings cache
	 */
	public $settings = array();

	/**
	 * Set Plugin
	 *
	 * @param	\Modern\Wordpress\Plugin	$plugin		The plugin associated with these settings
	 * @return	void
	 */
	public function setPlugin( \Modern\Wordpress\Plugin $plugin )
	{
		static::$plugin = $plugin;
	}
	
	/**
	 * Get Plugin
	 *
	 * @return	Plugin
	 */
	public function getPlugin()
	{
		return static::$plugin;
	}
	
	/**
	 * Enable Widget On Plugin
	 *
	 * @param	Plugin		$plugin			The plugin that this widget belongs to
	 */
	public static function enableOn( \Modern\Wordpress\Plugin $plugin )
	{
		static::$plugin = $plugin;
		$classname = get_called_class();
		
		add_action( 'widgets_init', function() use ( $classname )
		{
			register_widget( $classname );
		});		
	}
	
	/**
	 * Constructor
	 *
	 * @param	string		$id_base		Root ID for all widgets of this type
	 * @param	string		$name			Name for this widget
	 * @param	array		$widget_options		Option array passed to wp_register_sidebar_widget()
	 * @param	array		$control_options	Option array passed to wp_register_widget_control()
	 * @return	void
	 * @see: https://developer.wordpress.org/reference/classes/wp_widget/
	 */
	public function __construct( $id_base=NULL, $name=NULL, $widget_options = array(), $control_options = array() )
	{
		if ( $id_base === NULL )
		{
			$id_base = strtolower( str_replace( '\\', '_', get_class( $this ) ) );
		}
		
		if ( $name === NULL )
		{
			$name = $this->name;
		}
		
		if ( ! isset( $widget_options[ 'description' ] ) )
		{
			$widget_options[ 'description' ] = $this->description;
		}
		
		if ( ! isset( $widget_options[ 'classname' ] ) and isset( $this->classname ) )
		{
			$widget_options[ 'classname' ] = $this->classname;
		}
		
		parent::__construct( $id_base, $name, $widget_options, $control_options );
	}
	
	/**
	 * Creates a settings form for the widget
	 *
	 * @param	array		$instance			The widget instance settings
	 * @return	string
	 */
	public function form( $instance )
	{
		foreach( $this->settings as $name => $field )
		{
			switch( $field[ 'type' ] )
			{
				case 'text':
				case 'textarea':
					
					echo $this->getPlugin()->getTemplateContent( 'form/fields/' . $field[ 'type' ] . '-field', array( 
						'field_name' 	=> $this->get_field_name( $name ),
						'field_id'		=> $this->get_field_id( $name ),
						'field_value' 	=> isset( $instance[ $name ] ) ? $instance[ $name ] : $field[ 'default' ],
						'field_title'	=> $field[ 'title' ],
					) );
					break;
			}
		}
	}
	
	/**
	 * Echoes the widget content.
	 *
	 * @param 	array 	$args     	Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param 	array 	$instance 	The settings for the particular instance of the widget.
	 * @return	void
	 */
	public function widget( $args, $instance ) 
	{
		echo $this->getPlugin()->getTemplateContent( 'widget/layout/standard', array( 'args' => $args, 'title' => 'Modern Wordpress Widget', 'content' => 'Override the WP_Widget::widget() method in your widget class to output customized content.' ) );
	}	
	
}