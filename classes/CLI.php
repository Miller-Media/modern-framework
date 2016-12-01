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
	
	/**
	 * Update the wordpress plugin boilerplate
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The download url of the current boilerplate to update to 
	 *
	 * ## EXAMPLES
	 *
	 *     # Update the boilerplate
	 *     $ wp mwp update-boilerplate https://github.com/Miller-Media/wp-plugin-boilerplate/archive/master.zip
	 *     Success: Boilerplate successfully updated.
	 *
	 * @subcommand update-boilerplate
	 * @when after_wp_load
	 */
	function updateBoilerplate( $args, $assoc ) 
	{
		include_once( ABSPATH . 'wp-admin/includes/file.php' ); // Internal Upgrader WP Class
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' ); // Internal Upgrader WP Class
		
		if ( WP_Filesystem() == FALSE )
		{
			\WP_CLI::error( 'Error initializing wp_filesystem.' );
			return;
		}
		
		$download_url = $args[0];
		$upgrader = new \WP_Upgrader;
		$framework = \Modern\Wordpress\Framework::instance();

		ob_start();
		
		\WP_CLI::line( 'Downloading package...' );
		/*
		 * Download the package (Note, This just returns the filename
		 * of the file if the package is a local file)
		 */
		if ( is_wp_error( $download = $upgrader->download_package( $download_url ) ) ) 
		{
			\WP_CLI::error( $download );
		}

		$delete_package = ( $download != $download_url ); // Do not delete a "local" file

		\WP_CLI::line( 'Extracting package...' );

		// Unzips the file into a temporary directory.
		if ( is_wp_error( $working_dir = $upgrader->unpack_package( $download, $delete_package ) ) ) 
		{
			\WP_CLI::error( $working_dir );
		}
		
		\WP_CLI::line( 'Updating boilerplate plugin...' );

		// With the given options, this installs it to the destination directory.
		$result = $upgrader->install_package( array
		(
			'source' => $working_dir,
			'destination' => $framework->getPath() . '/boilerplate',
			'clear_destination' => true,
			'abort_if_destination_exists' => false,
			'clear_working' => true,
			'hook_extra' => array(),
		) );
		
		$r = ob_get_clean();
		
		\WP_CLI::success( 'Boilerplate successfully updated.' );
	}
}