Modern Wordpress Plugin Framework
==================================

This "plugin" provides a modern foundation of object oriented design patterns and api abstractions that enable rapid development of new wordpress plugins.

## Documentation

- [Annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations)
- [Framework API Doc](https://github.com/Miller-Media/modern-wordpress/wiki)

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

1.a) **Install the packaged plugin (not currently available)**

If you have WP CLI installed:
```
$ wp plugin install modern-wordpress --activate
```

-- **or** -- from your wordpress site:

```
WP Admin > Plugins > Add New > Search for "Modern Wordpress" > Install
```

1.b) **Download/clone this repository**

**Note**: When you begin by cloning this repository rather than installing the packaged version of the plugin, you will also need to install the composer dependencies manually after downloading. These instructions assume you already have the following programs on your system:
* [git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
* [composer](https://getcomposer.org/doc/00-intro.md)
* [WP CLI](https://wp-cli.org/docs/installing/)

```
$ cd wp-content/plugins
$ git clone https://github.com/Miller-Media/modern-wordpress
$ cd modern-wordpress
$ composer install
$ wp plugin activate modern-wordpress
```

2) Enable developer mode 

> To enable developer mode: Create or edit the **dev_config.php** file in the *modern-wordpress/* directory and add:
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
To begin programming the functionality of your new plugin, start adding methods to the *`./your-plugin-dir/classes/Plugin.php`* class file, taking care to [hook them into wordpress using @annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations).

Thats it. Have fun!

