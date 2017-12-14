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
 * @param	Modern\Wordpress\Helpers\ActiveRecordTable			$table			The active record display table
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="wrap">

	<h1><?php echo $controller->adminPage->title ?></h1>

	<?php 
		if ( isset( $controller->options['templates']['action_buttons'] ) ) { 
			echo $plugin->getTemplateContent( $controller->options['templates']['action_buttons'], array( 'plugin' => $plugin, 'controller' => $controller, 'table' => $table ) );
		} 
	?>
	<form method="post">
		<?php echo $table->getDisplay() ?>
	</form>
	
</div>