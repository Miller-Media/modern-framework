# Modern Wordpress Plugin Framework
This "plugin" provides a modern foundation of object oriented design patterns and api abstractions for other plugins to build on.

# Table of Contents

- [Main features](#main-features)
- [Framework Objectives](#framework-objectives)
- [Annotations](#annotations)
	- **Supported Annotations: Class Methods**
	- [@Wordpress\Action](#wordpressaction)
	- [@Wordpress\Filter](#wordpressfilter)
	- [@Wordpress\Shortcode](#wordpressshortcode)
	- [@Wordpress\AjaxHandler](#wordpressajaxhandler)
	- **Supported Annotations: Class Properties**
	- [@Wordpress\PostType](#wordpressposttype)
	- [@Wordpress\Script](#wordpressscript)
	- [@Wordpress\Stylesheet](#wordpressstylesheet)
	- **Supported Annotations: Classes**
	- [@Wordpress\Options](#wordpressoptions)
	- [@Wordpress\OptionsSection](#wordpressoptionssection)
	- [@Wordpress\OptionsField](#wordpressoptionsfield)
- [Base Classes](#base-classes)
	- [Modern\Wordpress\Plugin](#modernwordpressplugin)
	- [Modern\Wordpress\Plugin\Settings](#modernwordpresspluginsettings)
	- [Modern\Wordpress\Plugin\Widget](#modernwordpresspluginwidget)
- [Utitlity Classes](#utility-classes)
	- [Modern\Wordpress\Framework](#modernwordpressframework)
- [Pattern Classes](#pattern-classes)
	- [Modern\Wordpress\Pattern\Singleton](#modernwordpresspatternsingleton)

**How to get started:**

1. Install the plugin -or- Clone this repository
2. Enable developer mode (create a dev_config.php file in the plugin directory and add `define( MODERN_WORDPRESS_DEBUG, TRUE );` )
3. Begin building your own new plugin from scratch, or customize the [boilerplate plugin](https://github.com/Miller-Media/wp-plugin-boilerplate).

**Note**: If you want to begin by cloning this repository instead of installing a packaged version of this plugin, you will need to install the composer dependencies manually after downloading.
Do `composer install` on the command line from the plugin dir.

## Main features

* Write code that is automatically connected to wordpress according to your docblock @annotations
* Develop rapidly by extending base classes that bootstrap your plugin, settings pages, widgets, post types, and more.
* Easily encapsulate your html into re-usable templates that can be overridden by other plugins and themes.

## Framework Objectives

The objective of this framework is to ease the development of new wordpress plugins and to encourage good design practices through the use of modern object oriented design patterns and paradigms. Using this framework, you can expect to:

* Create accurate documentation (docblocks) while you develop to register callbacks, assets, and features into wordpress core. 
* Keep all code portable and encapsulated in namespaced classes rather than global functions which will improve maintainability.
* Keep all html templates seperate from functional code so that they can be easily changed or overridden by plugins or themes.
* Keep all plugin assets in an organized heirarchy and use autoloading of classes instead of manually including them.

## Annotations

The first thing to understand is that the modern wordpress framework leverages an annotation reading library from the [Doctrine Project](http://www.doctrine-project.org/) to read docblocks on classes, methods, and properties that you create. This allows the framework to automatially register your methods to core wordpress based on the documentation you write.

**Example #1: Adding a function to the core wordpress '*init*' action**

```php
namespace MyNamespace\MyPackage;

class MyPlugin extends \Modern\Wordpress\Plugin
{
	/**
	 * Wordpress Init
	 *
	 * @Wordpress\Action( for="init" )
	 *
	 * @return void
	 */
	public function wordpressInitialized()
	{
		// run this code when wordpress does the 'init' action
	}
}
```  

In the example above, the modern wordpress framework will read the annotations on your class method, see the one called @Wordpress\Action, and attach the method to the wordpress action for "init". There are a handful of other annotations (such as @Wordpress\Filter) that you can also use to register your plugin functions to core wordpress, which we will detail later. 

For now, the main takeaway is that you will be maintaining accurate inline documentation for your wordpress callbacks, which will save you from having to register the functions in separate methods that will undoubtedly lead to "what was this method for again?" syndrome, and you can easily refactor function names without the need to track down and change any associated registration calls.

The only other thing that would need to be done to complete this example is to instantiate your plugin and attach it to core wordpress. Normally, you just do this in your plugin's primary php file when it is loaded.

```php
// file: your-plugin/plugin.php

$myPlugin = \MyNamespace\MyPackage\MyPlugin::instance();
$framework = \Modern\Wordpress\Framework::instance();

$framework->attach( $myPlugin );
```

### Supported Annotations: Class Methods
--------------------------------------------------
The following annotations can be used to document class methods.

#### @Wordpress\Action

`@Wordpress\Action( for="action_name", priority=10, args=1 )`
Using this annotation will add your function as a callback for a core wordpress action. It is analogous to using [`add_action()`](https://developer.wordpress.org/reference/functions/add_action/) in wordpress.

**Params**:
> `for="action_name"`: (required) - The core wordpress action to attach the method to<br>
> `priority=10`: (optional) / {*default: 10*} - The priority of your callback<br>
> `args=1`: (optional) / {*default: 1*} - The number of arguments your callback expects

**Example**:
```php
/**
 * Examine an updated post
 *
 * @Wordpress\Action( for="post_updated", args=3 )
 * @param   int     $post_ID        The id of the post
 * @param   object  $post_after     The post object after the update
 * @param   object  $post_before    The post object before the update
 * @return  void
 */
public function examinePost( $post_ID, $post_after, $post_before )
{
    echo 'Post ID:';
    var_dump($post_ID);

    echo 'Post Object AFTER update:';
    var_dump($post_after);

    echo 'Post Object BEFORE update:';
    var_dump($post_before);
}
```
----------------------------------------

#### @Wordpress\Filter

`@Wordpress\Filter( for="filter_name", priority=10, args=1 )`
Using this annotation will add your function as a callback for a core wordpress filter. It is analogous to using [`add_filter()`](https://developer.wordpress.org/reference/functions/add_filter/) in wordpress.

**Params**:
> `for="filter_name"`: (required) - The core wordpress filter to attach the method to<br>
> `priority=10`: (optional) / {*default: 10*} - The priority of your callback<br>
> `args=1`: (optional) / {*default: 1*} - The number of arguments your callback expects

**Example**:
```php
/**
 * Add classes to the <body> html element
 *
 * @Wordpress\Filter( for="body_class" )
 * @param   array	$classes        An array of classes to add to body
 * @return  array
 */
public function addBodyClass( $classes )
{
    $classes[] = 'my-custom-class';
	return $classes;
}
```
----------------------------------------

#### @Wordpress\Shortcode

`@Wordpress\Shortcode( name="shortcode_tag" )`
Using this annotation will register your function as a callback for a wordpress shortcode of the name you specify. It is analogous to using [`add_shortcode()`](https://developer.wordpress.org/reference/functions/add_shortcode/) in wordpress.

**Params**:
> `name="shortcode_tag"`: (required) - The shortcode tag to be replaced in post content

**Example**:
```php
/**
 * Output bold and italic text
 *
 * @Wordpress\Shortcode( name="emphatic" )
 * @param   array	$atts        The attributes added inside the shortcode
 * @param   string  $content     The content enclosed within the shortcode
 * @return  string
 */
public function makeEmphatic( $atts, $content )
{
	// load the html template located at 'templates/shortcode/emphatic.php', while passing it the $content variable
    echo $this->getTemplateContent( 'shortcode/emphatic', array( 'content' => $content ) );
}
```
----------------------------------------

#### @Wordpress\AjaxHandler

`@Wordpress\AjaxHandler( action="ajax_action_name", for={"users","guests"} )`
Using this annotation will register your function as a callback for an ajax call. It can be configured to respond to both logged in and guest users.

**Params**:
> `action="ajax_action_name"`: (required) - The action parameter that should be used in the ajax call<br>
> `for={"users","guests"}`: (optional) / {*default: "users", "guests"*} - The type of users the callback should respond to.

**Example**:
```php
/**
 * Return the current users member id
 *
 * @Wordpress\AjaxHandler( action="get_my_userid" )
 * @return  string
 */
public function respondWithUserID()
{
	// Send user id, or 0 if user is not logged in
    wp_send_json( get_current_user_id() );
}
```
----------------------------------------

#### @Wordpress\Plugin

`@Wordpress\Plugin( on="activation|deactivation", file="plugin.php" )`
Using this annotation will register your function as a callback to when the plugin is activacted or deactivated on the site.

**Params**:
> `on="activation|deactivation"`: (required) - The event for which the function will be executed. Must be one of ('activation,'deactivation')<br>
> `file="plugin.php"`: (required) - The filename of your base plugin file inside your plugin directory. 

**Example**:
```php
/**
 * Create a default page on first activation
 * 
 * @Wordpress\Plugin( on="activation", file="plugin.php" )
 *
 * @return	void
 */
public function pluginActivated()
{
	if ( ! $this->getSetting( 'plugin_page' ) )
	{
		$page_id = wp_insert_post( array
			(
				'post_title'    => 'My Plugin Page',
				'post_content'  => '',
				'post_status'   => 'publish',
				'post_author'   => get_current_user_id(),
				'post_type'     => 'page',
			) 
		);
		
		$this->setSetting( 'plugin_page', $page_id )->saveSettings();
	}
}
```
----------------------------------------

### Supported Annotations: Class Properties
--------------------------------------------------
The following annotations can be used to document class properties.

#### @Wordpress\PostType

`@Wordpress\PostType( name="customtype" )`
Using this annotation will register a new post type to wordpress using the values provided in your property. Your post type will be registered with the core wordpress function [`register_post_type()`](https://codex.wordpress.org/Function_Reference/register_post_type)

**Params**:
> `name="customtype"`: (required) - The name of your post type

**Example**:
```php
/**
 * Custom Post Type
 *
 * @Wordpress\PostType( name="custompost" )
 *
 * @var array
 */
public $myPostType = array
(
	'labels'      	=> array( 'name' => 'Custom Posts', 'singular_name' => 'Custom Post' ),
	'public'      	=> true,
	'has_archive' 	=> false,
	'supports' 		=> array( 'title', 'editor', 'comments', 'post-templates', 'thumbnail' ),
);
```
----------------------------------------

#### @Wordpress\Script

`@Wordpress\Script( deps={"jquery"}, ver=false, footer=false, always=false )`
Using this annotation will register a script from your plugin to be used on wordpress pages. The script will be registered with the core wordpress function [`wp_enqueue_script()`](https://developer.wordpress.org/reference/functions/wp_enqueue_script/). The value of the annotated property should be the relative path from your plugin basedir to the script resource.

**Params**:
> `deps={"jquery"}`: (optional) / {*default: {}*} - Array of the names of any dependencies that this script has<br>
> `ver=false`: (optional) / {*default: false*} - String specifying script version number, or false to generate automatically<br>
> `footer=false`: (optional) / {*default: false*} - Whether to enqueue the script in the footer of the page instead of in the head<br>
> `always=false`: (optional) / {*default: false*} - If set to true, this script will be included on every page on the site automatically. Otherwise, you must issue the command to use the script at some other point in your code.

**Example**:
```php
/**
 * @Wordpress\Script( deps={"jquery"}, ver=false, footer=false, always=false )
 * @var string
 */
public $mainScript = "assets/js/main-module.js";

/**
 * @Wordpress\Action( for="wp_enqueue_scripts" )
 * 
 * @return	void
 */
public function enqueueScripts()
{
	if ( is_page( $this->getSetting( 'plugin_page' ) ) || is_singular( 'custompost' ) )
	{
		$this->useScript( $this->mainScript );
	}	
}
```
----------------------------------------

#### @Wordpress\Stylesheet

`@Wordpress\Stylesheet( deps={}, ver=false, footer=false, always=false )`
Using this annotation will register a stylesheet from your plugin to be used on wordpress pages. The stylesheet will be registered with the core wordpress function [`wp_enqueue_style()`](https://developer.wordpress.org/reference/functions/wp_enqueue_style/). The value of the annotated property should be the relative path from your plugin basedir to the stylesheet resource.

**Params**:
> `deps={}`: (optional) / {*default: {}*} - Array of the names of any dependencies that this stylesheet has<br>
> `ver=false`: (optional) / {*default: false*} - String specifying stylesheet version number, or false to generate automatically<br>
> `media={"all"}`: (optional) / {*default: {"all"}*} - Which media types this stylesheet should apply to<br>
> `always=false`: (optional) / {*default: false*} - If set to true, this stylesheet will be included on every page on the site automatically. Otherwise, you must issue the command to use the stylesheet at some other point in your code.

**Example**:
```php
/**
 * @Wordpress\Stylesheet
 * @var string
 */
public $mainStyles = "assets/css/styles.css";

/**
 * @Wordpress\Action( for="wp_enqueue_scripts" )
 * 
 * @return	void
 */
public function enqueueStyles()
{
	if ( is_page( $this->getSetting( 'plugin_page' ) ) || is_singular( 'custompost' ) )
	{
		$this->useStyle( $this->mainStyles );
	}	
}
```
----------------------------------------

### Supported Annotations: Classes
--------------------------------------------------
The following annotations can be used to document on the class level.

#### @Wordpress\Options

`@Wordpress\Options( menu="My Plugin", title="My Plugin Options", capability="manage_options" )`
This annotation is used to designate an options page to manage settings for a plugin. The class it is used on should be an extension of the Modern\Wordpress\Plugin\Settings class, which will be the settings object used to access and set the settings found on the designated options page. The annotation itself will provision the page for the settings, but additional annotations are needed to specify the options groups and options fields used on the page.

**Params**:
> `menu="My Plugin"`: (optional) / {*default: %Plugin Name%*} - The name of the menu link for the settings page. Defaults to the plugin name if not provided.<br>
> `title="My Plugin Options"`: (optional) / {*default: %Plugin Name% + Options*} - The title of the settings page. Defaults to the plugin name + ' Options' if not provided.<br>
> `capability="manage_options"`: (optional) / {*default: "manage_options"*} - The administrative capability required to access the settings page.

**Example**:
> See example at end of section.
----------------------------------------

#### @Wordpress\OptionsSection

`@Wordpress\OptionsSection( title="General Settings", description="Manage general settings for this plugin." )`
This annotation is used on a class that extends Modern\Wordpress\Plugin\Settings, and must be placed after the @Wordpress\Options annotation. It triggers the grouping of options fields on the settings page. Any options fields that are specified after a @Wordpress\OptionsSection annotation will be grouped into the same section.

**Params**:
> `title="General Settings"`: (required) - The title of the settings section.<br>
> `description="Description string"`: (optional) / {*default: null*} - A description for the settings section.

**Example**:
> See example at end of section.
----------------------------------------

#### @Wordpress\OptionsField

`@Wordpress\OptionsField( name="field_name", title="Field Title", type="select", options={ "value1":"Option 1", "value2": "Option 2" } )`
This annotation is used to specify individual settings fields which can be managed on the settings page, and which will store values which can be retrieved by the settings class in the plugin.

**Params**:
> `name="field_name"`: (required) - Specifies the name of the option. This is the name that the setting will be accessible by from the settings class.<br>
> `title="Field Title"`: (required) - The title of the form field<br>
> `type="select"`: (required) / - The type of form element to use for the option field<br>
> `options={}`: (optional) / {*default: {}*} - For fields that utilize options (such as select, radio, checkboxes, etc), this is a static array of the options that are available, or a string which contains a method name in the class which can be called to return an array of values to use for the field options.

**Example**:
```php
/**
 * Plugin Settings
 *
 * @Wordpress\Options
 * @Wordpress\Options\Section( title="General Settings" )
 * @Wordpress\Options\Field( name="plugin_title", type="text", title="Plugin Title" )
 * @Wordpress\Options\Field( name="plugin_page", type="select", title="Forums Page", options="forumsPageOptions" )
 */
class Settings extends \Modern\Wordpress\Plugin\Settings
{
	
	/**
	 * Instance Cache - Required for singleton
	 * @var	self
	 */
	protected static $_instance;
	
	/**
	 * Get Plugin Page Select Options
	 *
	 * @param		string			$currentValue			The current settings value
	 * @return		array
	 */
	public function pluginPageOptions( $currentValue=NULL )
	{
		$options = array();
		
		/* Create list of wordpress pages */
		foreach( get_pages() as $page )
		{
			$post_title = htmlentities( $page->post_title );
			$post_name = htmlentities( $page->post_name );
			$options[ $page->ID ] = "{$post_name} ({$post_title})";
		}
		
		return $options;
	}	
	
}
```
----------------------------------------

## Base Classes
The following classes can be extended to bootstrap functionality.

### Modern\Wordpress\Plugin

### Modern\Wordpress\Plugin\Settings

### Modern\Wordpress\Plugin\Widget

## Utility Classes

### Modern\Wordpress\Framework

## Pattern Classes

### Modern\Wordpress\Pattern\Singleton

