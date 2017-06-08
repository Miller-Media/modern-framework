<?php
/**
 * Plugin HTML Template
 *
 * Created:  June 8, 2017
 *
 * @package  Modern Framework for Wordpress
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'widget/dashboard', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
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
use Modern\Wordpress\Framework;

$notices = array();

if ( $_POST['mwp_clear_caches'] ) 
{
	Framework::instance()->clearAnnotationsCache();
	$notices[] = __( "Temporary caches have been cleared.", 'modern-framework' );
}

if ( $_POST['mwp_update_schema'] )
{
	foreach( apply_filters( 'modern_wordpress_find_plugins', array() ) as $plugin )
	{
		$plugin->updateSchema();
	}
	$notices[] = __( "Database table schemas have been brought up to date.", 'modern-framework' );
}

?>

<div style="float: right; display: inline-block;">
	<?php foreach ( $notices as $message ) : ?>
		<div class="notice updated"><p><?php echo esc_html( $message ) ?></p></div>
	<?php endforeach; ?>
	<form method="post" style="margin-bottom: 10px">
		<input name="mwp_clear_caches" type="hidden" value="1" />
		<input class="button" value="Clear Caches" type="submit" style="width: 100%;"/>
	</form>
	<form method="post">
		<input name="mwp_update_schema" type="hidden" value="1" />
		<input class="button" value="Update DB Schema" type="submit" style="width: 100%;" />
	</form>
</div>

<a href="<?php echo admin_url( 'tools.php?page=mwp-tasks' ) ?>">Tasks Pending</a>: <?php echo Task::countWhere( 'task_completed=0' ) ?>

<div style="clear:both"></div>
