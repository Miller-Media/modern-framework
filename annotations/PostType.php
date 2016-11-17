<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class PostType
{
    /**
     * @var string
     */
    public $name;
	
	/**
	 * @var array
	 */
	public $supports;
	
	/**
	 * @var string
	 */
	public $label;
	
	/**
	 * @var array
	 */
	public $labels;
	
	/**
	 * @var string
	 */
	public $description;
    
	/**
	 * @var boolean
	 */
	public $public;
	
}