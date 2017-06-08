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
	<label>
		<?php echo $field[ 'title' ] ?>
		<select id="<?php echo $field_id ?>" name="<?php echo $field_name ?>">
			<?php foreach( $field[ 'options' ] as $value => $label ) : ?>
				<option value="<?php echo $value ?>" <?php if( $value == $field_value ) echo "selected"; ?>> <?php echo esc_html( $label ) ?></option>
			<?php endforeach; ?>
		</select>
	</label>
</p>