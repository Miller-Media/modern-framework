<?php

namespace Wordpress;

/**
 * @Annotation 
 * @Target( "METHOD" )
 */
class Action
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