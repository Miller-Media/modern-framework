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
<?php if (false !== $label): ?>
<?php if ($required) { $label_attr['class'] = trim((isset($label_attr['class']) ? $label_attr['class'] : '').' required'); } ?>
<?php if (!$compound) { $label_attr['for'] = $id; } ?>
<?php if (!$label) { $label = isset($label_format)
    ? strtr($label_format, array('%name%' => $name, '%id%' => $id))
    : $view['form']->humanize($name); } ?>
<label <?php foreach ($label_attr as $k => $v) { printf('%s="%s" ', $view->escape($k), $view->escape($v)); } ?>><?php echo $view->escape(false !== $translation_domain ? $view['translator']->trans($label, array(), $translation_domain) : $label) ?></label>
<?php endif ?>
