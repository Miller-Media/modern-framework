<?php
/**
 * Plugin Name: Modern Framework for Wordpress
 * Version: 1.2.5
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

use Doctrine\Common\Annotations\AnnotationRegistry;

/* Load Only Once */
if ( ! class_exists( 'ModernWordpressFramework' ) )
{

	/* Include packaged autoloader if present */
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		require_once 'vendor/autoload.php';
	}
	
	/* Include global functions */
	require_once 'includes/mwp-global-functions.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	
	/* Include Redux Framework */
	#require_once 'includes/redux/admin-init.php';
	
	/* Optional development config */
	if ( file_exists( __DIR__ . '/dev_config.php' ) ) {
		include_once __DIR__ . '/dev_config.php'; 
	}

	AnnotationRegistry::registerFile( __DIR__ . "/annotations/AdminPage.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/AjaxHandler.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Plugin.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Action.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Filter.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Shortcode.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Options.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/OptionsSection.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/OptionsField.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/PostType.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Stylesheet.php" );
	AnnotationRegistry::registerFile( __DIR__ . "/annotations/Script.php" );

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