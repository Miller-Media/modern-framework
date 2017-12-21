<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 20, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'component/error' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$code		The error code
 * @param	string		$message	The error message
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<!-- html content -->
<div class="mwp-bootstrap">
  <p class="bg-danger"><?php echo $message ?></p>
</div>