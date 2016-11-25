# Modern Wordpress Plugin Framework
This "plugin" provides a modern foundation of object oriented design patterns and api abstractions for other plugins to build on.

# Table of Contents

- [Main features](#main-features)
- [Framework Objectives](#framework-objectives)
- [Annotations](#annotations)
	- Supported Annotations: Class Methods
	- [@Wordpress\Action](#wordpressaction)
	- [@Wordpress\Filter](#wordpressfilter)
	- [@Wordpress\Shortcode](#wordpressshortcode)
	- [@Wordpress\AjaxHandler](#wordpressajaxhandler)
	- Supported Annotations: Class Properties
	- [@Wordpress\PostType](#wordpressposttype)
	- [@Wordpress\Script](#wordpressscript)
	- [@Wordpress\Style](#wordpressstyle)
	- Supported Annotations: Classes
	- [@Wordpress\Options](#wordpressoptions)
	- [@Wordpress\OptionsSection](#wordpressoptionssection)
	- [@Wordpress\OptionsField](#wordpressoptionsfield)
- [Base Classes](#base-classes)
	
	
**How to get started:**

1. Install the plugin
2. Enable developer mode
3. Begin building your own new plugin from scratch, or customize a [boilerplate plugin](https://github.com/Miller-Media/wp-plugin-boilerplate).

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

For now, you can see that you will always have accurate inline documentation for your wordpress callbacks, it will save you from having to register the functions in separate methods which will undoubtedly lead to "what was this method for again?" syndrome, and you can easily refactor function names without the need to track down and change any associated registration calls.

The only other thing that needs to be done to complete this example is to instantiate your plugin and attach it to core wordpress. Normally, you just do this in your plugin's primary php file when it is loaded.

```php
// file: your-plugin/plugin.php

$myPlugin = \MyNamespace\MyPackage\MyPlugin::instance();
$framework = \Modern\Wordpress\Framework::instance();

$framework->attach( $myPlugin );
```

### Supported Annotations: Class Methods
--------------------------------------------------
The following annotations can be used to document class methods.

##### @Wordpress\Action

`@Wordpress\Action( for="action_name", priority=10, args=1 )`
Using this annotation will add your function as a callback for a core wordpress action. It is analogous to using [`add_action()`](https://developer.wordpress.org/reference/functions/add_action/) in wordpress.

**Params**:
> `for="action_name"`: (required) - The core wordpress action to attach the method to
> `priority=10`: (optional) / {*default: 10*} - The priority of your callback
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

##### @Wordpress\Filter

`@Wordpress\Filter( for="filter_name", priority=10, args=1 )`
Using this annotation will add your function as a callback for a core wordpress filter. It is analogous to using [`add_filter()`](https://developer.wordpress.org/reference/functions/add_filter/) in wordpress.

**Params**:
> `for="action_name"`: (required) - The core wordpress action to attach the method to
> `priority=10`: (optional) / {*default: 10*} - The priority of your callback
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

##### @Wordpress\Shortcode

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

##### @Wordpress\AjaxHandler

`@Wordpress\AjaxHandler( action="ajax_action_name", for={"users","guests"} )`
Using this annotation will register your function as a callback for an ajax call. It can be configured to respond to both logged in and guest users.

**Params**:
> `action="ajax_action_name"`: (required) - The action parameter that should be used in the ajax call
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

### Supported Annotations: Class Properties
--------------------------------------------------
The following annotations can be used to document class properties.

##### @Wordpress\PostType

----------------------------------------

##### @Wordpress\Script

----------------------------------------

##### @Wordpress\Style

----------------------------------------

### Supported Annotations: Classes
--------------------------------------------------
The following annotations can be used to document entire classes.

##### @Wordpress\Options

----------------------------------------

##### @Wordpress\OptionsSection

----------------------------------------

##### @Wordpress\OptionsField

----------------------------------------

## Base Classes
The following classes can be extended to inherit complete sets of functionality.

