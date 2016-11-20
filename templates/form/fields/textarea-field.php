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

<textarea id="<?php echo $field_id ?>" name="<?php echo $field_name ?>" class="widefat" rows="16" cols="20" type="textarea"><?php echo $field_value ?></textarea>