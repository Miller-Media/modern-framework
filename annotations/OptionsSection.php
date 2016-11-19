<?php

namespace Wordpress\Options;

/**
 * @Annotation 
 * @Target( "CLASS" )
 */
class Section
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
    
}