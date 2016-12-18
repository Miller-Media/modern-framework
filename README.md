Modern Framework for Wordpress
==================================

This "plugin" provides a modern foundation of object oriented design patterns and api abstractions that enable rapid development of new wordpress plugins.

## Documentation

- [Annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations)
- [Framework Classes](https://github.com/Miller-Media/modern-wordpress/wiki)
- [WP CLI](https://github.com/Miller-Media/modern-wordpress/wiki/WP-CLI)

## Main features

* Write code that is automatically connected to wordpress according to your docblock @annotations
* Develop rapidly by extending base classes that bootstrap your plugin, settings pages, widgets, post types, and more.
* Easily encapsulate your html into re-usable templates that can be overridden by other plugins and themes.

## Objectives

The objective of this framework is to ease the development of new wordpress plugins and to encourage good design practices through the use of modern object oriented design patterns and paradigms. Using this framework, you can expect to:

* Create accurate documentation (docblocks) while you develop. 
* Keep all code portable and encapsulated in namespaced classes.
* Eliminate the need to manually 'include' any php files.
* Keep all html templates seperate from functional code.
* Keep all plugin assets in an organized heirarchy.
* Have fun!

## How to get started:

1) **Install the packaged plugin and dependencies**

If you have WP CLI installed:
```
$ wp plugin install piklist --activate
$ wp plugin install https://github.com/Miller-Media/modern-wordpress/raw/master/builds/modern-framework-latest-stable.zip --activate
```

2) Enable developer mode 

> To enable developer mode: Create or edit the **dev_config.php** file in the *wp-content/plugins/modern-wordpress/* directory and add:
```
define( 'MODERN_WORDPRESS_DEV', TRUE );
```

### Create A New Plugin
Begin building your own new plugin from scratch by customizing the [boilerplate plugin](https://github.com/Miller-Media/wp-plugin-boilerplate), or you can create a new modern wordpress plugin using [WP CLI](https://wp-cli.org/):
```
$ wp mwp update-boilerplate https://github.com/Miller-Media/wp-plugin-boilerplate/archive/master.zip
$ wp mwp create-plugin "Awesome New Plugin" --vendor="My Company" --author="My Name"
```
**Note**: By using the WP CLI to create your plugin, the boilerplate is automatically cloned, customized, and used as the skeleton for your new plugin!

### Make It Do Something
To begin programming the functionality of your new plugin, start adding methods to the *`./your-plugin-dir/classes/Plugin.php`* file, taking care to [hook them into wordpress using @annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations).

Thats it. Have fun!

