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
 * $content = $plugin->getTemplateContent( 'views/management/task-item', array() ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$task		The task being viewed
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$displaydate = function( $timestamp, $default='' ) {
	if ( $timestamp > 0 ) {
		return get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), 'F j, Y H:i:s' );
	}
	else {
		return $default;
	}
};

?>

<div class="wrap">
	
	<h1>Task Item Details</h1>
	<?php if ( $task !== NULL ) : ?>
		
		<div>
			<?php echo $this->getTemplateContent( 'views/management/task-submenu' ) ?>
		</div>
		
		<hr style="clear:both">
		
		<h2><?php echo $task->getTitle() ?></h2>

		<blockquote>
			<ul>
				<li><strong>Status:</strong> <?php if ( $task->completed ) { echo "<strong style='color:green'>" . $task->getStatusForDisplay() . "</strong>"; } else { echo $task->getStatusForDisplay(); } ?></li>
				<li><strong>Action:</strong> <?php echo $task->action ?></li>
				<li><strong>Tag:</strong> <?php echo $task->tag ?: 'None' ?></li>
				<li><strong>Last Start:</strong> <?php echo $task->getLastStartForDisplay(); ?></li>
				<li><strong>Next Start:</strong> <?php echo $task->getNextStartForDisplay(); ?></li>
			</ul>
		</blockquote>

		<h2>Logs</h2>
		
		<blockquote>
		<?php
			$logs = $task->getData( 'logs' );
			if ( ! empty( $logs ) )
			{
				echo "<ul style='list-style-type: disc;'>";
				foreach( $logs as $log )
				{
					?>
						<li>
							<?php echo call_user_func( $displaydate, $log[ 'time' ] ) . ": " . $log[ 'message' ] ?>
						</li>
					<?php
				}
				echo "</ul>";
			}
			else
			{
				echo "<ul style='list-style-type: disc;'><li>None</li></ul>";
			}
		?>
		</blockquote>
		
	<?php else: ?>
	
		<h1>Task Not Found</h1>
	
	<?php endif ?>
</div>
