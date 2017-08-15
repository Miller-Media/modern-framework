<?php
/**
 * Settings Class File
 *
 * @vendor: {vendor_name}
 * @package: {plugin_name}
 * @author: {plugin_author}
 * @link: {plugin_author_url}
 * @since: {date_time}
 */
namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Plugin Settings
 *
 * @Wordpress\Options( menu="MWP Framework" )
 * 
 * @Wordpress\Options\Section( title="Bootstrap Javascript" )
 * @Wordpress\Options\Field( 
 *   name="mwp_bootstrap_disable_front_js", 
 *   type="checkbox",
 *   title="Front",
 *   description="Disable inclusion of Bootstrap Javascript by MWP on the front end", 
 *   default=false 
 * )
 * @Wordpress\Options\Field( 
 *   name="mwp_bootstrap_disable_admin_js", 
 *   type="checkbox",
 *   title="Admin",
 *   description="Disable inclusion of Bootstrap Javascript by MWP on the admin side", 
 *   default=false 
 * )
 *
 * @Wordpress\Options\Section( title="Bootstrap CSS" )
 * @Wordpress\Options\Field( 
 *   name="mwp_bootstrap_disable_front_css", 
 *   type="checkbox",
 *   title="Front",
 *   description="Disable inclusion of Bootstrap CSS by MWP on the front end", 
 *   default=false 
 * )
 * @Wordpress\Options\Field( 
 *   name="mwp_bootstrap_disable_admin_css", 
 *   type="checkbox",
 *   title="Admin",
 *   description="Disable inclusion of Bootstrap CSS by MWP on the admin side", 
 *   default=false 
 * )
 */
class Settings extends \Modern\Wordpress\Plugin\Settings
{
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	

}