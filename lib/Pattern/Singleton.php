<?php

namespace Modern\Wordpress\Pattern;

/**
 * Singleton 
 */
class Singleton
{
	/**
	 * @var	Instance Cache
	 */
	protected static $_instance;
	
	/**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct() 
	{
	}
	
	/**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }
	
	/**
	 * Create Instance
	 *
	 * @return	self
	 */
	public function instance()
	{
		if ( ! isset( static::$_instance ) )
		{
			static::$_instance = new static();
		}
		
		return static::$_instance;
	}
}