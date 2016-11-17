<?php
/*
Plugin Name: Modern Wordpress Plugin Framework
Description: Provides a standard framework for modern wordpress plugins to run on.
Author: Miller Media
Author URI: http://www.miller-media.com/
*/

require_once 'vendor/autoload.php';

/* Optional config file (for development overrides) */
if ( file_exists( __DIR__ . '/dev_config.php' ) ) {
	include_once __DIR__ . '/dev_config.php'; 
}

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Action.php" );
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Filter.php" );
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Shortcode.php" );
AnnotationRegistry::registerFile( __DIR__ . "/annotations/PostType.php" );
