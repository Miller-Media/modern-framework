<?php
/**
 * Form template file
 *
 * Created:   April 3, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.3.12
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<?php echo $row_prefix ?>
<div <?php 
	foreach ($row_attr as $k => $v) { 
		if ($v === true) {
			printf('%s="%s" ', $view->escape($k), $view->escape($k));
		} elseif ($v !== false){
			printf('%s="%s" ', $view->escape($k), $view->escape($v));
		} 
	} ?>>
	<?php echo $prefix; ?>
    <?php echo $view['form']->label($form) ?>
	<?php echo $field_prefix ?>
    <?php echo $view['form']->widget($form) ?>
    <?php echo $view['form']->errors($form) ?>
	<?php echo $field_suffix ?>
	<?php echo $suffix ?>
</div>
<?php echo $row_suffix ?>
