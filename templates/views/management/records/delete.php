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
 * @param	Modern\Wordpress\Helpers\Form						$form			The form that was built
 * @param	Modern\Wordpress\Plugin								$plugin			The plugin that created the controller
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The active record controller
 * @param	Modern\Wordpress\Pattern\ActiveRecord				$record			The active record being edited
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="wrap">
	<?php echo $form->render() ?>
</div>