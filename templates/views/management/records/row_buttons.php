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
 * @param	string												$class			The active record class
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The active record controller
 * @param	array												$row			The active record row data
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="mwp-bootstrap" style="text-align:right">
	<a href="<?php echo $controller->getUrl( array( 'do' => 'edit', 'id' => $row[ $class::$prefix . $class::$key ] ) ) ?>" class="btn btn-default" title="Edit"><i class="glyphicon glyphicon-pencil"></i></a>
</div>