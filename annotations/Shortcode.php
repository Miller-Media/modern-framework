<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class Shortcode
{
    /**
     * @var string
     */
    public $name;
    
}