<?php
/**
 * Annotation: Wordpress\Options\Field  
 *
 * Created:    Nov 20, 2016
 *
 * @package    Modern Wordpress Framework
 * @author     Kevin Carwile
 * @since      1.0.0
 */

namespace Wordpress\Options;

/**
 * @Annotation 
 * @Target( "CLASS" )
 */
class Field extends \Modern\Wordpress\Annotation
{
    /**
     * @var string
	 * @Required
     */
    public $name;
	
	/**
	 * @var string
	 * @Required
	 */
	public $title;
    
	/**
	 * @var string
	 * @Required
	 */
	public $type;

	/**
	 * @var array
	 */
	public $attributes;

	/**
	 * @var mixed
	 */
	public $options;
	
	/**
	 * @var mixed
	 */
	public $default;

	/**
	 * @var	string
	 */
	public $description;
	 
	/**
	 * Get Field
	 *
	 * @param	\Modern\Wordpress\Plugin\Settings		$settings 			The settings store
	 */
	public function getFieldHtml( $settings )
	{
		return $settings->getPlugin()->getTemplateContent( 'admin/settings/' . $this->type . '-field', array( 'field' => $this, 'settings' => $settings ) );
	}

	public function getFieldAttributes() {

		if ( ! $this->attributes ) {
			return '';
		}

		$attributes = '';

		foreach( $this->attributes as $name => $value ) {
			$attributes .= ( strlen( $value ) > 0 )
				? " $name=\"$value\""
				: " $name";
		}

		return $attributes;
	}

	/**
	 * Apply to Object
	 *
	 * @param	object		$instance		The object which is documented with this annotation
	 * @param	array		$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToObject( $instance, $vars )
	{
		extract( $vars );
		
		if ( $instance instanceof \Modern\Wordpress\Plugin\Settings )
		{
			$instance->setDefault( $this->name, $this->default );
			
			if ( isset( $page_id ) and isset( $section_id ) )
			{
				$self = $this;
				add_action( 'admin_init', function() use ( $page_id, $section_id, $self, $instance )
				{
					add_settings_field( md5( $page_id . $self->name ), $self->title, function() use ( $page_id, $section_id, $self, $instance )
					{
						echo call_user_func( array( $self, 'getFieldHtml' ), $instance );
					}
					, $page_id, $section_id );
				});
			}
		}
	}
	
}