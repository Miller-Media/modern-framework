<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class Filter
{
    /**
     * @var string
     */
    public $for;
    
    /**
     * @var integer
     */
    public $priority;
    
    /**
     * @var integer
     */
    public $args;
    
}