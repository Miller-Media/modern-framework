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
<?php if ( isset( $row_prefix ) ) { echo $prefix; } ?>
<div <?php if ( isset( $row_attr ) ) { foreach ($row_attr as $k => $v) { ?>
<?php if ($v === true): ?>
<?php printf('%s="%s" ', $view->escape($k), $view->escape($k)) ?>
<?php elseif ($v !== false): ?>
<?php printf('%s="%s" ', $view->escape($k), $view->escape($v)) ?>
<?php endif ?>
<?php } } ?>>
	<?php if ( isset( $prefix ) ) { echo $prefix; } ?>
    <?php echo $view['form']->label($form) ?>
    <?php echo $view['form']->widget($form) ?>
    <?php echo $view['form']->errors($form) ?>
	<?php if ( isset( $suffix ) ) { echo $suffix; } ?>
</div>
<?php if ( isset( $row_suffix ) ) { echo $suffix; } ?>
