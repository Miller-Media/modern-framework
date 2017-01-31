<?php
/**
 * Plugin HTML Template
 *
 * Created:  January 25, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    1.1.4
 *
 * @param	Plugin		$this			The plugin instance which is loading this template
 *
 * @param	array		$field			The field definition
 * @param	string		$field_name		The field name
 * @param	string		$field_html		The rendered field html
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<?php echo $field_html ?>