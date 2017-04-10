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
<div <?php echo $view['form']->block($form, 'widget_container_attributes') ?>>
<?php foreach ($form as $child): ?>
    <?php echo $view['form']->widget($child) ?>
    <?php echo $view['form']->label($child, null, array('translation_domain' => $choice_translation_domain)) ?>
<?php endforeach ?>
</div>
