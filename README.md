Modern Framework for Wordpress
==================================

This "plugin" provides a foundation of object oriented design patterns, bootstrap classes, and api abstractions for wordpress that enable very rapid development of new wordpress plugins. It also provides several utilities to auto generate new plugin resources and automatically manage new plugin builds/releases.

## Documentation

- [Annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations)
- [Framework Classes](https://github.com/Miller-Media/modern-wordpress/wiki)
- [WP CLI](https://github.com/Miller-Media/modern-wordpress/wiki/WP-CLI)

## Main features

* Simply document your functions using @annotations and let the framework automatically hook them into core.
* Develop rapidly by extending base classes that bootstrap your plugin, settings pages, widgets, post types, and more.
* Easily keep all your html in individual re-usable templates that can be overridden by other plugins and themes.
* Leverage a built in task runner to easily send routine tasks off to a managed queue to be ran by cron.
* Create tables for your plugin and let the framework automatically track and update them on new releases.
* Build new release packages with a single command and all your plugin files will be versioned automatically.

## How to get started:

1) **Install the packaged plugin and dependencies**

If you have WP CLI installed:
```
$ wp plugin install piklist --activate
$ wp plugin install https://github.com/Miller-Media/modern-wordpress/raw/master/builds/modern-framework-latest-stable.zip --activate
```

2) Enable developer mode 

> To enable developer mode: Create or edit the **dev_config.php** file in the *wp-content/plugins/modern-framework/* directory and add:
```
define( 'MODERN_WORDPRESS_DEV', TRUE );
```

### Create A New Plugin
Bootstrap the creation of your own new plugin by cloning and customizing the [boilerplate plugin](https://github.com/Miller-Media/wp-plugin-boilerplate) using [WP CLI](https://wp-cli.org/):
```
$ wp mwp update-boilerplate
$ wp mwp create-plugin "Awesome New Plugin" --vendor="My Company" --author="My Name"
```
**Note**: By using the WP CLI to create your plugin, the boilerplate is automatically customized with your plugin details!

### Make It Do Something
To begin programming the functionality of your new plugin, just start adding methods to the *`./your-plugin-dir/classes/Plugin.php`* file, which can be hooked into wordpress [using @annotations](https://github.com/Miller-Media/modern-wordpress/wiki/@Annotations). At some point, you will likely want to separate your code out into separate files to keep things logically organized.

You can easily create new javascript modules, css stylesheets, html templates, and php classes all from the WP CLI.
```
$ wp mwp add-script myplugin-slug script-name
$ wp mwp add-css myplugin-slug stylesheet-name
$ wp mwp add-template myplugin-slug views/template-name
$ wp mwp add-class myplugin-slug New\Class
```

### Distribute It
When you are ready to build a release package of your plugin for distribution, that's easy too:

```
$ wp mwp build-plugin myplugin-slug --version-update=minor
```
A packaged .zip file that contains your new version will be created in the `/builds` subdirectory of your plugin. That zip file can be used to install the plugin on any other wordpress site.

Thats it. Have fun!

