<?php
/**
 * Plugin Name: Modern Framework for Wordpress
 * Version: 1.0.2
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
	
	/* Optional config file (for development overrides) */
	if ( file_exists( __DIR__ . '/dev_config.php' ) ) {
		include_once __DIR__ . '/dev_config.php'; 
	}
	
	/* Register plugin dependencies */
	include_once 'includes/plugin-dependency-config.php';
	
	if ( ! is_plugin_active( 'piklist/piklist.php' ) )
	{
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), function()
		{
			echo '<td colspan="3" class="plugin-update colspanchange">
					<div class="update-message notice inline notice-error notice-alt">
						<p><strong style="color:red">MISSING DEPENDENCY.</strong> Please activate <a href="' . admin_url( 'plugins.php?page=tgmpa-install-plugins' ) . '"><strong>Piklist</strong></a> to enable the operation of this plugin.</p>
					</div>
				  </td>';			
		});
		return;
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
			$framework->attach( $framework );
			do_action( 'modern_wordpress_init' );
		}		
	}
	
	ModernWordpressFramework::init();
}