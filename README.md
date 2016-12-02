Modern Wordpress Plugin Framework
==================================

This "plugin" provides a modern foundation of object oriented design patterns and api abstractions that enable rapid development of new wordpress plugins.

Table of Contents
=================

- [Main features](#main-features)
- [Objectives](#framework-objectives)
- [Annotations](#)
- [Framework API Doc](#)

## How to get started:

1. Install the plugin -or- Clone this repository
2. Enable developer mode - Create or edit your dev_config.php file in the modern-wordpress/ directory and add:
```
define( 'MODERN_WORDPRESS_DEV', TRUE );
```

**Note**: If you want to begin by cloning this repository instead of installing a packaged version of this plugin, you will need to install the composer dependencies manually after downloading.
Do `composer install` on the command line from the plugin dir.

### Creating A Plugin
Begin building your own new plugin from scratch by customizing the [boilerplate plugin](https://github.com/Miller-Media/wp-plugin-boilerplate), or you can create a new modern wordpress plugin using [WP CLI](https://wp-cli.org/):
```
$ wp mwp update-boilerplate https://github.com/Miller-Media/wp-plugin-boilerplate/archive/master.zip
$ wp mwp create-plugin "Awesome New Plugin" --vendor="My Company" --author="My Name"
```
**Note**: By using the WP CLI to create your plugin, the boilerplate is automatically cloned, customized, and used as the skeleton for your new plugin.

## Main features

* Write code that is automatically connected to wordpress according to your docblock @annotations
* Develop rapidly by extending base classes that bootstrap your plugin, settings pages, widgets, post types, and more.
* Easily encapsulate your html into re-usable templates that can be overridden by other plugins and themes.

## Objectives

The objective of this framework is to ease the development of new wordpress plugins and to encourage good design practices through the use of modern object oriented design patterns and paradigms. Using this framework, you can expect to:

* Create accurate documentation (docblocks) while you develop to register callbacks, assets, and features into wordpress core. 
* Keep all code portable and encapsulated in namespaced classes rather than global functions which will improve maintainability.
* Keep all html templates seperate from functional code so that they can be easily changed or overridden by plugins or themes.
* Keep all plugin assets in an organized heirarchy and use autoloading of classes instead of manually including them.

