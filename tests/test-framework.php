<?php
/**
 * Testing Class
 *
 * To set up testing for your wordpress plugin:
 *
 * @see: http://wp-cli.org/docs/plugin-unit-tests/
 *
 * @package Simple Forums
 */
if ( ! class_exists( 'WP_UnitTestCase' ) )
{
	die( 'Access denied.' );
}

/**
 * Test the framework
 */
class ModernWordpressFrameworkTest extends WP_UnitTestCase 
{
	/**
	 * Test that the framework is actually an instance of a modern wordpress plugin
	 */
	public function test_plugin_class() 
	{
		$framework = \Modern\Wordpress\Framework::instance();
		
		// Check that the framework is a subclass of Modern\Wordpress\Plugin 
		$this->assertTrue( $framework instanceof \Modern\Wordpress\Plugin );
	}
}
