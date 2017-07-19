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
<?php if (count($errors) > 0): ?>
    <ul class="form-errors">
        <?php foreach ($errors as $error): ?>
            <li><?php echo $error->getMessage() ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif ?>
