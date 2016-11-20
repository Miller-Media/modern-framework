<?php
/**
 * Plugin HTML Template
 *
 * @param 	array 		$args     	Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
 * @var		string		$title		The widget title
 * @var		string		$content	The widget content
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<?php echo $args[ 'before_widget' ] ?>
<?php echo $args[ 'before_title' ] ?>
<?php echo $widget_title ?>
<?php echo $args[ 'after_title' ] ?>
<?php echo $widget_content ?>
<?php echo $args[ 'after_widget' ] ?>