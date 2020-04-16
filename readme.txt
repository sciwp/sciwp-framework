# SCI WP Framework

Contributors: neeonez
Tags: framework, mvc, orm, autoloader, models, views, controllers, services, plugins, dependency injection, DI, scripts, styles, templates, router, routes.
Requires at least: 5.2
Tested up to: 5.3
Requires PHP: 7.2
Stable tag: trunk
License: GNU Lesser General Public License
License URI: https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html

MVC Features for WordPress.

== Description ==

This plugin is useful for users or companies who want to create custom plugins for their personal or internal usage.

Features:

1. ORM with common CRUD operations and Query builder
2. MVC Architecture (Controllers, Models, Views)
3. Service container supporting parametrized dependency injection
4. Namespaces and PSR-4 compliant autoloader
5. Configure custom URL routes hierarchically
6. Install once and use with all plugins you want
7. Collections, Services and dynamic method bindings
8. Activation and deactivation actions and checks
9. Asset manager for JS scripts and CSS files
10. Service providers
11. Template manager


Important: This plugin is not a starter template or something you can bundle. In case you want to bundle the included framework with your own plugin and distribute it,
you should use the embedded framework version instead, which is avaiable at GitHub. You can also find a starter boilerplate at GitHub.
Althouth sometimes it might be easy to do it, you should never bundle another plugin directly, as it can cause big compatibility problems for other users using the plugin.

== Installation ==


You can install this plugin by these two methods:

1. You can install it form the WordPress Plugin Manager
2. Or you can download the zip file, unzip it and upload the "mvc-wp" directory to the WordPress `/wp-content/plugins/` directory.


== Tutorial ==


You can find the documentation here: http://sciwp.com/

== Changelog ==

= 1.0 =
* Initial release