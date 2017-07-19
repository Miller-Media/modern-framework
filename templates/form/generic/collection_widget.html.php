<?php
/**
 * Form template file
 *
 * Created:   April 3, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<?php if (isset($prototype)): ?>
    <?php $attr['data-prototype'] = $view->escape($view['form']->row($prototype)) ?>
<?php endif ?>
<?php echo $view['form']->widget($form, array('attr' => $attr)) ?>
