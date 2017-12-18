<?php
/**
 * Form template file
 *
 * Created:   December 14, 2017
 *
 * @package:  Modern Framework for Wordpress
 * @author:   Kevin Carwile
 * @since:    {build_version}
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$active = true;

foreach( $form as $child ) {
	$classes = isset( $child->vars['attr']['class'] ) ? explode(' ', $child->vars['attr']['class'] ) : array();
	$classes[] = 'tab-pane';
	if ( $active ) {
		$classes[] = 'active';
		$active = false;
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

