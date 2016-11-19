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
	 * Get Field
	 *
	 * @param	string		$settings_id 		The settings id
	 */
	public function getFieldHtml( $settings_id, $currentValue=NULL )
	{
		global $modernwordpress;
		return $modernwordpress->getTemplateContent( 'admin/settings/text-field', array( 'field' => $this, 'currentValue' => $currentValue, 'settings_id' => $settings_id ) );
	}
}