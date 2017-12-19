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
  .mwp-bootstrap-form ul.form-errors {
	background-color: #f2dede;
	padding: 10px;
	margin-top: 5px;
	border-radius: 5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	border: 1px solid #f2bbbb;
  }
  .mwp-bootstrap-form .field-description {
	margin: 5px 0 10px 0;
  }
  .mwp-bootstrap-form .form-group > label.form-label {
	font-size: 1.25em;
	line-height: 1.4em;
	text-align: right;
	font-weight: normal;
	padding: 6px;
  }
</style>


<div class="mwp-bootstrap mwp-bootstrap-form">
	<?php echo $form_html ?>
</div>