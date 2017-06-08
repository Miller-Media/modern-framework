<?php
/**
 * Plugin HTML Template
 *
 * @var		$field			array			The form field settings
 * @var 	$field_name		string			The form field name
 * @var		$field_id		string			The form field id
 * @var		$field_value	string			The form field value
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<p>
<label><?php echo $field['title'] ?></label><br>
<?php foreach( $field[ 'options' ] as $value => $label ) : ?>
<label><input id="<?php echo $field_id ?>" class="widefat" name="<?php echo $field_name ?>[]" size="40" type="checkbox" value="<?php echo $value ?>" <?php if( in_array( $value, (array) $field_value ) ) { echo "checked"; } ?>/> <?php echo esc_html( $label ) ?></label><br>
<?php endforeach; ?>
</p>