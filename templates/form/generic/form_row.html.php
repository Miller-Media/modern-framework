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
<div class="form-row">
    <?php echo $view['form']->label($form) ?>
    <?php echo $view['form']->widget($form) ?>
    <?php echo $view['form']->errors($form) ?>
</div>
