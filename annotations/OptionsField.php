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
		$settings->setDefault( $this->name, $this->default );
		return $settings->getPlugin()->getTemplateContent( 'admin/settings/' . $this->type . '-field', array( 'field' => $this, 'settings' => $settings ) );
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
		
		if ( $instance instanceof \Modern\Wordpress\Plugin\Settings and isset( $page_id ) and isset( $section_id ) )
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