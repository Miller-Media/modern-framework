<?php

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Implements example command.
 */
class CLI extends \WP_CLI_Command {

	/**
	 * Creates a new boilerplate modern wordpress plugin.
	 *
	 * ## OPTIONS
	 *
	 * <name>
	 * : The name of the plugin.
	 *
	 * [--vendor=<vendor>]
	 * : The name of the plugin provider.
	 *
	 * [--namespace=<namespace>]
	 * : The Vendor\Package namespace for the plugin.
	 *
	 * [--dir=<dir>]
	 * : The directory name that the plugin will be created in.
	 *
	 * [--description=<description>]
	 * : The plugin description.
	 *
	 * [--author=<author>]
	 * : The name of the plugin author.
	 *
	 * [--author-url=<author_url>]
	 * : The plugin author web url.
	 *
	 * [--plugin-url=<plugin_url>]
	 * : The plugin project url.
	 *
	 * ## EXAMPLES
	 *
	 *     # Create a new plugin
	 *     $ wp mwp create "My New Plugin" --vendor="My Company" --dir="example-plugin-dir" --namespace="MyCompany\MyPlugin" --description="A new plugin to customize."
	 *     Success: Plugin successfully created in 'example-plugin-dir'.
	 *
	 * @subcommand create-plugin
	 * @when after_wp_load
	 */
	function createPlugin( $args, $assoc ) 
	{
		$framework = \Modern\Wordpress\Framework::instance();
		
		$assoc[ 'name' ] = $args[0];
		
		if ( ! isset( $assoc[ 'vendor' ] ) )
		{
			$assoc[ 'vendor' ] = "Modern Wordpress";
		}
		
		if ( ! isset( $assoc[ 'namespace' ] ) )
		{
			/**
			 * Create a namespace from the vendor + plugin name 
			 */
			
			/* Reduce to only alphanumerics and spaces */ 
			$vendorName = preg_replace( '/[^A-Za-z0-9 ]/', '', $assoc[ 'vendor' ] );
			$packageName = preg_replace( '/[^A-Za-z0-9 ]/', '', $assoc[ 'name' ] );
			
			/* Combine possible multiple spaces into a single space */
			$vendorName = preg_replace( '/ {2,}/', ' ', $vendorName );
			$packageName = preg_replace( '/ {2,}/', ' ', $packageName );
			
			/* Trim spaces off ends */
			$vendorName = trim( $vendorName );
			$packageName = trim( $packageName );
			
			/* Divide into words */
			$vendorPieces = explode( ' ', $vendorName );
			$packagePieces = explode( ' ', $packageName );
			
			/* Create vendor space from first 1 or 2 words */
			if ( count( $vendorPieces ) > 1 )
			{
				$piece1 = array_shift( $vendorPieces );
				$piece2 = array_shift( $vendorPieces );
				$vendorSpace = ucwords( $piece1 ) . ucwords( $piece2 );
			}
			else
			{
				$vendorSpace = ucwords( $vendorPieces[0] );
			}
			
			/* Create package space from first 1 or 2 words */
			if ( count( $packagePieces ) > 1 )
			{
				$piece1 = array_shift( $packagePieces );
				$piece2 = array_shift( $packagePieces );
				$packageSpace = ucwords( $piece1 ) . ucwords( $piece2 );
			}
			else
			{
				$packageSpace = ucwords( $packagePieces[0] );
			}
			
			$assoc[ 'namespace' ] = "$vendorSpace\\$packageSpace";
		}
		
		if ( ! isset( $assoc[ 'dir' ] ) )
		{
			$assoc[ 'dir' ] = strtolower( preg_replace( '|\\\|', '-', $assoc[ 'namespace' ] ) );
		}
		
		try
		{
			$framework->createPlugin( $assoc );
		}
		catch( \Exception $e )
		{
			\WP_CLI::error( $e->getMessage() );
		}
		
		\WP_CLI::success( "Plugin successfully created in '{$assoc[ 'dir' ]}'." );
	}
}