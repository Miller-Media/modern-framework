<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class Plugin
{
    /**
     * @var string
	 * @Required
     */
    public $on;
    
    
}