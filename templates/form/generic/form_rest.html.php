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
<?php foreach ($form as $child): ?>
    <?php if (!$child->isRendered()): ?>
        <?php echo $view['form']->row($child) ?>
    <?php endif; ?>
<?php endforeach; ?>
