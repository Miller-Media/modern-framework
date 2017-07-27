<?php
/**
 * Plugin HTML Template
 *
 * @var 	$settings		\Modern\Wordpress\Plugin\Settings			The settings store
 * @var		$field			\Wordpress\Options\Field					The options field definition
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

wp_enqueue_media();
$currentValue = $settings->getSetting( $field->name );
$attachment = ($currentValue ? get_post($currentValue) : null );

?>
    <input id="url_<?php echo $field->name; ?>" type="text" style="width:300px;" readonly value="<?php echo ($attachment ? $attachment->guid : ""); ?>"/>
    <img class="image-preview" src="<?php echo ($attachment ? $attachment->guid : ""); ?>" style="width:30px;height:auto;position:absolute;"><br>
    <input id="upload_button_<?php echo $field->name; ?>" type="button" class="button upload-button" value="<?php _e( 'Upload image' ); ?>" />
    <input type="hidden" name="<?php echo $settings->getStorageId() ?>[<?php echo $field->name ?>]" id="<?php echo $field->name; ?>" value="<?php echo ($currentValue ? $currentValue : ''); ?>" />
<?php