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
<style>
  .mwp-bootstrap-form {
	padding: 25px 10px;
  }
  .mwp-bootstrap-form .tab-content {
	padding: 15px;
  }
  .mwp-bootstrap-form .mwp-form-tabs {
	margin-bottom: 20px;
	margin-top: 20px;
  }
</style>


<div class="mwp-bootstrap mwp-bootstrap-form">
	<?php echo $form_html ?>
</div>