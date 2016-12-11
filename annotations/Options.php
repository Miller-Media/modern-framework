<?php
/**
 * Annotation: Wordpress\Options  
 *
 * Created:    Nov 20, 2016
 *
 * @package    Modern Wordpress Framework
 * @author     Kevin Carwile
 * @since      0.1.2
 */

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "CLASS" )
 */
class Options extends \Modern\Wordpress\Annotation
{
    /**
     * @var string
     */
    public $menu;
	
	/**
	 * @var string
	 */
	public $title;
	
	/**
	 * @var string
	 */
	public $capability = 'manage_options';
	
	/**
	 * Apply to Object
	 *
	 * @param	object		$instance		The object which is documented with this annotation
	 * @param	array		$vars			Persisted variables returned by previous annotations
	 * @return	array|NULL
	 */
	public function applyToObject( $instance, $vars )
	{
		if ( $instance instanceof \Modern\Wordpress\Plugin\Settings )
		{
			$menu 	= $this->menu ?: $instance->getPlugin()->name;
			$title 	= $this->title ?: $menu . ' ' . __( 'Options' );
			$capability = $this->capability;
			$page_id = $instance->getStorageId();
			
			add_action( 'admin_menu', function() use ( $menu, $title, $capability, $page_id, $instance )
			{
				add_options_page( $title, $menu, $capability, $page_id, function() use ( $title, $page_id, $instance )
				{
					echo $instance->getPlugin()->getTemplateContent( 'admin/settings/form', array( 'title' => $title, 'page_id' => $page_id ) );
				});
			});
			
			add_action( 'admin_init', function() use ( $page_id, $instance )
			{
				register_setting( $page_id, $page_id, array( $instance, 'validate' ) );
			});
			
			return array( 'page_id' => $page_id );
		}		
	}
}