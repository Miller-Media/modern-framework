<?php
/**
 * WP CLI Command Class (WP_CLI_Command)
 * 
 * @package 	Modern Wordpress Framework
 * @author	Kevin Carwile
 * @since	Nov 20, 2016
 */

namespace Modern\Wordpress;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Implements commands that can be executed from the WP CLI.
 */
class CLI extends \WP_CLI_Command {

	/**
	 * Creates a new boilerplate modern wordpress plugin.
	 *
	 * @param	$args		array		Positional command line arguments
	 * @param	$assoc		array		Named command line arguments
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
	 * [--slug=<slug>]
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
	 *     $ wp mwp create "My New Plugin" --vendor="My Company" --slug="example-plugin-dir" --namespace="MyCompany\MyPlugin" --description="A new plugin to customize."
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
		
		if ( ! isset( $assoc[ 'slug' ] ) )
		{
			$assoc[ 'slug' ] = strtolower( preg_replace( '|\\\|', '-', $assoc[ 'namespace' ] ) );
		}
		
		try
		{
			\WP_CLI::line( 'Creating plugin...' );
			$framework->createPlugin( $assoc );
		}
		catch( \Exception $e )
		{
			if ( $e->getCode() == 1 )
			{
				// No boilerplate present
				\WP_CLI::error( $e->getMessage() . "\nSuggestion: Try using: $ wp mwp update-boilerplate https://github.com/Miller-Media/wp-plugin-boilerplate/archive/master.zip" );
			}
			else if ( $e->getCode() == 2 )
			{
				// Plugin directory already used
				\WP_CLI::error( $e->getMessage() . "\nSuggestion: Try using: $ wp mwp create-plugin \"{$assoc['name']}\" --slug='my-custom-dir' to force a different install directory" );
			}
			
			\WP_CLI::error( $e->getMessage() );
		}
		
		\WP_CLI::success( "Plugin successfully created in '{$assoc[ 'slug' ]}'." );
	}
	
	/**
	 * Update the wordpress plugin boilerplate
	 *
	 * @param	$args		array		Positional command line arguments
	 * @param	$assoc		array		Named command line arguments
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
	
	/**
	 * Add a new javascript module
	 * 
	 * @param	$args		array		Positional command line arguments
	 * @param	$assoc		array		Named command line arguments
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the modern wordpress plugin
	 * 
	 * <name>
	 * : The name of the javascript file
	 *
	 * ## EXAMPLES
	 *
	 *     # Add a new javascript module
	 *     $ wp mwp add-js my-plugin testmodule.js
	 *     Success: Javascript module added successfully.
	 *
	 * @subcommand add-js
	 * @when after_wp_load
	 */
	function createJavascriptModule( $args, $assoc )
	{
		$framework = \Modern\Wordpress\Framework::instance();
		
		try
		{
			\WP_CLI::line( 'Creating new javascript module...' );
			$framework->createJavascript( $args[0], $args[1] );
		}
		catch( \ErrorException $e )
		{
			\WP_CLI::error( $e->getMessage() );
		}
		
		\WP_CLI::success( 'Javascript module added successfully.' );
	}
	
	/**
	 * Add a new stylesheet file
	 * 
	 * @param	$args		array		Positional command line arguments
	 * @param	$assoc		array		Named command line arguments
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the modern wordpress plugin
	 * 
	 * <name>
	 * : The name of the stylesheet file
	 *
	 * ## EXAMPLES
	 *
	 *     # Add a new css stylesheet
	 *     $ wp mwp add-css my-plugin newstyle.css
	 *     Success: Stylesheet added successfully.
	 *
	 * @subcommand add-css
	 * @when after_wp_load
	 */
	function createStylesheetFile( $args, $assoc )
	{
		$framework = \Modern\Wordpress\Framework::instance();
		
		try
		{
			\WP_CLI::line( 'Creating new css stylesheet...' );
			$framework->createStylesheet( $args[0], $args[1] );
		}
		catch( \ErrorException $e )
		{
			\WP_CLI::error( $e->getMessage() );
		}
		
		\WP_CLI::success( 'Stylesheet added successfully.' );
	}
	
	/**
	 * Add a new php class file
	 * 
	 * @param	$args		array		Positional command line arguments
	 * @param	$assoc		array		Named command line arguments
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the modern wordpress plugin
	 * 
	 * <name>
	 * : The name of the new class (can be namespaced)
	 *
	 * ## EXAMPLES
	 *
	 *     # Add a new php class file
	 *     $ wp mwp add-class my-plugin Plugin\NewSettings
	 *     Success: Class added successfully.
	 *
	 * @subcommand add-class
	 * @when after_wp_load
	 */
	function createClassFile( $args, $assoc )
	{
		$framework = \Modern\Wordpress\Framework::instance();
		
		try
		{
			\WP_CLI::line( 'Creating new plugin class file...' );
			$framework->createClass( $args[0], $args[1] );
		}
		catch( \ErrorException $e )
		{
			\WP_CLI::error( $e->getMessage() );
		}
		
		\WP_CLI::success( 'Class added sucessfully.' );
	}	
}