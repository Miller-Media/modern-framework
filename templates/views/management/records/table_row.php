<?php
/**
 * Plugin HTML Template
 *
 * Created:  January 4, 2018
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	Modern\Wordpress\Helpers\ActiveRecordTable			$table			The active record display table
 * @param	array												$item			The item row
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$recordClass = $table->activeRecordClass;

?>
<tr id="<?php echo $item[ $recordClass::$prefix . $recordClass::$key ] ?>">
	<?php echo $table->single_row_columns( $item ); ?>
</tr>