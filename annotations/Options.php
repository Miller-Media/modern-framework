<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "CLASS" )
 */
class Options
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
	
}