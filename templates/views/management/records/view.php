<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 13, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    1.4.0
 *
 * @param	string												$title			The provided title
 * @param	Modern\Wordpress\Plugin								$plugin			The plugin associated with the active records/view
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The associated controller displaying this view
 * @param	Modern\Wordpress\Pattern\ActiveRecordController		$record			The active record to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>


<div class="wrap">
	<h1><?php echo $title ?></h1>
	<?php echo $content ?>
</div>