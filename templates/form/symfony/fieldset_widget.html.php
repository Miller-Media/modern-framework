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

?>
<fieldset <?php echo $view['form']->block($form, 'widget_attributes') ?>>
	<?php if ( $legend ) { ?>
	<legend><?php echo $legend ?></legend>
	<?php } ?>
	<?php echo $view['form']->block($form, 'form_widget') ?>
</fieldset>

