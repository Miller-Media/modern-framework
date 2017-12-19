<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 13, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	Modern\Wordpress\Plugin								$plugin			The plugin that created the controller
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The active record controller
 * @param	array												$buttons		Buttons to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="mwp-bootstrap" style="text-align:right">
	<?php foreach( $buttons as $button ) : ?>
	<a href="<?php echo $button['href'] ?>" class="<?php echo $button['class']?>"><?php echo $button['title'] ?></a>
	<?php endforeach ?>
</div>