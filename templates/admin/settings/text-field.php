<?php
/**
 * Plugin HTML Template
 *
 * 
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<input id='<?php echo $field->name ?>' name='<?php echo $settings_id ?>[<?php echo $field->name ?>]' size='40' type='text' value='<?php echo $currentValue ?>' />