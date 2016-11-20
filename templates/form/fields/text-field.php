<?php
/**
 * Plugin HTML Template
 *
 * @var 	$field_name		string			The form field name
 * @var		$field_id		string			The form field id
 * @var		$field_value	string			The form field value
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<input id="<?php echo $field_id ?>" class="widefat" name="<?php echo $field_name ?>" size="40" type="text" value="<?php echo $field_value ?>" />