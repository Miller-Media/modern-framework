<?php
/*
Plugin Name: Modern Wordpress Plugin Framework
Version: 0.1.0
Provides: lib-modern-wordpresss
Description: Provides a standard framework that other plugins may depend on.
Author: Miller Media
Author URI: http://www.miller-media.com/
*/
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use Doctrine\Common\Annotations\AnnotationRegistry;

/* Load Only Once */
if ( ! class_exists( 'ModernWordpressFramework' ) )
{
	require_once 'vendor/autoload.php';

	/* Optional config file (for development overrides) */
	if ( file_exists( __DIR__ . '/dev_config.php' ) ) {
		include_once __DIR__ . '/dev_config.php'; 
	}

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
			do_action( 'modern_wordpress_init' );
		}
	}
	
	ModernWordpressFramework::init();
}