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
 * @param	array												$actions		Actions to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="mwp-bootstrap" style="text-align:right">
	<?php foreach ( $actions as $action ) : ?>
	<a <?php 
		if ( isset( $action['attr'] ) ) {
			foreach( $action['attr'] as $k => $v ) {
				if ( is_array( $v ) ) { $v = json_encode( $v ); } printf( '%s="%s" ', $k, esc_attr( $v ) );
			}
		}
	?> href="<?php echo $controller->getUrl( isset( $action['params'] ) ? $action['params'] : array() ) ?>">
		<?php if ( isset( $action['icon'] ) ) : ?>
			<i class="<?php echo $action['icon'] ?>"></i>
		<?php endif ?>
		<?php echo $action['title'] ?>
	</a>
	<?php endforeach ?></div>