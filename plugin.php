<?php
/**
 * Plugin Name: Modern Framework for Wordpress
 * Version: 1.3.3
 * Provides: lib-modern-framework
 * Description: Provides an object oriented utility framework for modern wordpress plugins.
 * Author: Kevin Carwile
 * Author URI: http://www.miller-media.com/
 * License: GPL2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/* Optional development config */
if ( basename( __DIR__ ) == 'modern-framework' and file_exists( __DIR__ . '/dev_config.php' ) ) {
	include_once __DIR__ . '/dev_config.php'; 
}

/**
 * Executing code in an anonymous function to scope the variables
 *
 * @return	void
 */
call_user_func( function() {

	global $_mwp_version;
	$plugin_meta = array();
	
	/**
	 * Keep the $_mwp_version global variable up to date with the most recent framework version found
	 */
	if ( file_exists( __DIR__ . '/data/plugin-meta.php' ) )
	{
		$data = include __DIR__ . '/data/plugin-meta.php';
		$plugin_meta = json_decode( $data, true );
		if ( isset( $plugin_meta[ 'version' ] ) )
		{
			if ( empty( $_mwp_version ) or version_compare( $_mwp_version, $plugin_meta[ 'version' ] ) === -1 )
			{
				$_mwp_version = $plugin_meta[ 'version' ];
			}
		}
	}
	
	/** 
	 * When activating a new plugin, check if it has a bundled modern wordpress framework and attempt
	 * to include it now because it might resolve as the most current version, and be needed for the plugin
	 * to activate successfully.
	 */
	global $pagenow;
	if ( $pagenow == 'plugins.php' and isset( $_REQUEST['action'] ) and $_REQUEST['action'] == 'activate' and ! did_action( 'plugins_loaded' ) )
	{
		$activating_plugin = $_REQUEST['plugin'];
		$activating_plugin_path = dirname( WP_PLUGIN_DIR . '/' . plugin_basename( trim( $activating_plugin ) ) );
		if ( file_exists( $activating_plugin_path . '/framework/modern-framework.php' ) )
		{
			include_once $activating_plugin_path . '/framework/plugin.php';
		}
	}
	
	/**
	 * Only attempt to load the framework which is the most up to date after
	 * all plugins have had a chance to report their bundled framework version.
	 *
	 * Also: If we are in mwp development mode, then we should never load any
	 * other version than that.
	 *
	 * @return	void
	 */
	add_action( 'plugins_loaded', function() use ( $plugin_meta, &$_mwp_version )
	{
		// Let's always skip including bundled frameworks if we are in development
		$in_development = ( defined( 'MODERN_WORDPRESS_DEV' ) and \MODERN_WORDPRESS_DEV );
		if ( $in_development and basename( __DIR__ ) != 'modern-framework' )
		{
			return;
		}

		// Let's skip loading framework versions that are not the newest we know we have, unless we are in development
		if (
			! $in_development and
			! empty( $_mwp_version ) and 
			version_compare( $_mwp_version, $plugin_meta[ 'version' ] ) === 1 
		)
		{
			return;
		}

		/* Load Only Once, Ever */
		if ( ! class_exists( 'ModernWordpressFramework' ) )
		{

			/* Include packaged autoloader if present */
			if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
				require_once 'vendor/autoload.php';
			}
			
			/* Include global functions */
			require_once 'includes/mwp-global-functions.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			
			/* Optional development config */
			if ( basename( __DIR__ ) == 'modern-framework' and file_exists( __DIR__ . '/dev_config.php' ) ) {
				include_once __DIR__ . '/dev_config.php'; 
			}
			
			$annotationRegistry = 'Doctrine\Common\Annotations\AnnotationRegistry';
			$annotationRegistry::registerFile( __DIR__ . "/annotations/AdminPage.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/AjaxHandler.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Plugin.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Action.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Filter.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/MetaBox.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Shortcode.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Options.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/OptionsSection.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/OptionsField.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/PostType.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/RestRoute.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Stylesheet.php" );
			$annotationRegistry::registerFile( __DIR__ . "/annotations/Script.php" );

			class ModernWordpressFramework
			{
				public static function init()
				{
					/* FAAP: Framework As A Plugin :) */
					$framework = \Modern\Wordpress\Framework::instance();		
					$framework->setPath( rtrim( plugin_dir_path( __FILE__ ), '/' ) );
					$framework->attach( $framework );
					
					if ( is_admin() ) {
						$framework->attach( \Modern\Wordpress\Controller\Tasks::instance() );
					}	
						
					do_action( 'modern_wordpress_init' );
				}		
			}
			
			ModernWordpressFramework::init();
		}
	}, 0 );

});