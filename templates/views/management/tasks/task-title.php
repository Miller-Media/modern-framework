<?php
/**
 * Plugin HTML Template
 *
 * Created:  March 20, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    1.2.6
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'views/management/tasks/task-title', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$task		The task item
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<strong style="font-size: 1.2em;"><?php echo $task->getTitle() ?></strong>
<br>Action: <?php echo $task->action ?>
<?php if ( $task->tag ) : ?><br>Tag: <?php echo $task->tag ?><?php endif; ?>
