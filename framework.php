<?php
/*
Plugin Name: Modern Wordpress Plugin Framework
Description: Provides a standard framework for modern wordpress plugins to run on.
Author: Miller Media
Author URI: http://www.miller-media.com/
*/

require_once 'vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Action.php" );
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Filter.php" );
AnnotationRegistry::registerFile( __DIR__ . "/annotations/Shortcode.php" );
