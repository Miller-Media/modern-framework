<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 5, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    1.2.8
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/management/task-submenu', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$title		The provided title
 * @param	string		$content	The provided content
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Modern\Wordpress\Task;

?>

<ul class="subsubsub">
	<li class="pending">
		<a href="<?php echo add_query_arg( array( 'page' => 'mwp-tasks' ), admin_url( 'tools.php' ) ) ?>" class="<?php if ( ! isset( $_REQUEST[ 'status' ] ) and ! isset( $_REQUEST[ 'do' ] ) ) { echo "current"; } ?>">
			<?php _e( 'Pending', 'modern-framework' ) ?>
			<span class="count">(<?php echo Task::countTasks( NULL, NULL, 'pending' ) ?>)</span>
		</a> | 
	</li>
	<li class="completed">
		<a href="<?php echo add_query_arg( array( 'page' => 'mwp-tasks', 'status' => 'completed' ), admin_url( 'tools.php' ) ) ?>" class="<?php if ( isset( $_REQUEST[ 'status' ] ) and $_REQUEST[ 'status' ] == 'completed' ) { echo "current"; } ?>">
			<?php _e( 'Completed', 'modern-framework' ) ?>
			<span class="count">(<?php echo Task::countTasks( NULL, NULL, 'completed' ) ?>)</span>
		</a> | 
	</li>
	<li class="failed">
		<a href="<?php echo add_query_arg( array( 'page' => 'mwp-tasks', 'status' => 'failed' ), admin_url( 'tools.php' ) ) ?>" class="<?php if ( isset( $_REQUEST[ 'status' ] ) and $_REQUEST[ 'status' ] == 'failed' ) { echo "current"; } ?>">
			<?php _e( 'Failed', 'modern-framework' ) ?>
			<span class="count">(<?php echo Task::countTasks( NULL, NULL, 'failed' ) ?>)</span>
		</a>
	</li>
</ul>
