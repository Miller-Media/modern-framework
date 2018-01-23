<?php
/**
 * Form template file
 *
 * Created:   December 14, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    1.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$initial_tab = isset( $form->vars['attr']['initial-tab'] ) && isset( $form[ $form->vars['attr']['initial-tab'] ] ) ? $form->vars['attr']['initial-tab'] : NULL;
$default_active = $initial_tab ? false : true;

foreach( $form as $child_name => $child ) {
	$classes = isset( $child->vars['attr']['class'] ) ? explode(' ', $child->vars['attr']['class'] ) : array();
	$classes[] = 'tab-pane';
	if ( $default_active or $child_name == $initial_tab ) {
		$classes[] = 'active';
		$default_active = false;
		$child->active = true;
	}
	$child->vars['attr']['role'] = 'tabpanel';
	$child->vars['attr']['class'] = implode( ' ', array_unique( $classes ) );
}

?>
<div role="form-tabs" <?php echo $view['form']->block($form, 'widget_container_attributes') ?>>
	<ul class="nav nav-tabs" role="tablist">
	<?php foreach ($form as $child) : ?>
		<li role="presentation" <?php if ( $child->active ) { echo 'class="active"'; } ?>><a href="#<?php echo $child->vars['id'] ?>" role="tab" data-toggle="tab"><?php echo $child->vars['title'] ?></a></li>
	<?php endforeach; ?>
	</ul>
	<div class="tab-content">
	<?php foreach ($form as $child) : ?>
		<?php echo $view['form']->widget($child) ?>
	<?php endforeach; ?>
	</div>
</div>

