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
<input type="checkbox"
    <?php echo $view[ 'form' ]->block( $form, 'widget_attributes' ) ?>
    <?php if ( strlen( $value ) > 0 ): ?> value="<?php echo $view->escape($value) ?>"<?php endif ?>
    <?php if ( $checked ): ?> checked="checked"<?php endif ?>
/>
