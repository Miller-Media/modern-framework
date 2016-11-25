# Modern Wordpress Plugin Framework
This "plugin" provides a modern foundation of object oriented design patterns and api abstractions for other plugins to build on.

**How to get started:**

1. Install the plugin
2. Enable developer mode
3. Begin building your own new plugin from scratch, or customize a boilerplate plugin.

## Main features

* Write your code and modern wordpress will connect it to core automatically based on your docblock @annotations
* Use base classes from the framework to bootstrap settings pages, widgets, and post types in your plugin
* Create html templates that are separate from your plugin code and let the framework load them with complete override support.

### @TODO DOC

* Basic Framework Usage
  - Annotations
* Base classes documentaton
  - Modern\Wordpress\Framework  
  - Modern\Wordpress\Plugin
  - Modern\Wordpress\Plugin\Settings
  - Modern\Wordpress\Widget
  - Modern\Wordpress\Pattern\Singleton

## Framework Objectives

The purpose of this framework is to ease the development of new wordpress plugins and to encourage the practice of good design through the use of modern object oriented design patterns and paradigms. Some of the top level goals include:

* Use proper documentation (docblocks) to register callbacks, assets, and features into wordpress core. 
* Keep all code portable and encapsulated in namespaced classes rather than global functions to improve maintainability.
* Keep all html templates seperate from functional code so that they can be easily changed or overridden by plugins or themes.
* Keep all plugin assets in an organized heirarchy and use autoloading of classes instead of manually including them.

## Annotations
The modern wordpress framework leverages the annotation reader library from the [Doctrine Project](http://www.doctrine-project.org/) to read your docblocks on classes, methods, and properties that you create, and will register your methods to core wordpress automatically based on your documentation.

**Example: Adding a function for the core wordpress '*init*' action**

```php
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

From the example above, the modern wordpress framework will read the annotations on your class method, see the one called @Wordpress\Action, and attach your method to the wordpress action for "init". There are a handful of other annotations (such as @Wordpress\Filter) that you can also use to tie your features to core wordpress which we will detail later. 

For now, the benefit of doing this is that you will have accurate documentation for your wordpress callback that is located right next to your function, it saves you from having to register the function elsewhere which can lead to "what is this method for again" syndrome, and you can easily refactor your function name without the need to track down and change its associated registration call.

The only other thing that needs to be done in this example is to instantiate your plugin and attach it to core wordpress. Normally, you just do this in your plugin's primary php file:

```php
// your-plugin/plugin.php

$myPlugin = \MyNamespace\MyPackage\MyPlugin::instance();
$framework = \Modern\Wordpress\Framework::instance();

$framework->attach( $myPlugin );
```

## Base Classes

### Modern\Wordpress\F