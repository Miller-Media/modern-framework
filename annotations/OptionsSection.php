<?php
/**
 * Annotation: Wordpress\Options\Section  
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
class Section extends \Modern\Wordpress\Annotation
{	
	/**
	 * @var string
	 * @Required
	 */
	public $title;
	
	/**
	 * @var string
	 */
	public $description;
    
	/**
	 * Apply to Object
	 *
	 * @param	object		$instance		The object which is documented with this annotation
	 * @param	array		$vars			Persisted variables returned by previous annotations
	 * @return	void
	 */
	public function applyToObject( $instance, $vars )
	{
		extract( $vars );
		
		if ( $instance instanceof \Modern\Wordpress\Plugin\Settings and isset( $page_id ) )
		{
			$section_id = md5( $this->title );
			$self = $this;
			add_action( 'admin_init', function() use ( $section_id, $page_id, $self )
			{
				add_settings_section( $section_id, $self->title, function() use ( $self ) { return $self->description; }, $page_id );
			});
			
			return array( 'section_id' => $section_id );
		}
	}
	
}