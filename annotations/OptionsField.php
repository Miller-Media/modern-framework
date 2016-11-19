<?php

namespace Wordpress\Options;

/**
 * @Annotation 
 * @Target( "CLASS" )
 */
class Field
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
	 * Get Field
	 *
	 * @param	\Modern\Wordpress\Plugin\Settings		$settings 			The settings store
	 */
	public function getFieldHtml( $settings )
	{
		return \Modern\Wordpress\WordpressAPI::instance()->getTemplateContent( 'admin/settings/' . $this->type . '-field', array( 'field' => $this, 'settings' => $settings ) );
	}
}