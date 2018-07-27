
=== DTRT EXIF ===
Contributors: dotherightthingnz
Donate link: http://dotherightthing.co.nz
Tags: exif, geotag, attachment
Requires at least: 4.9.5
Tested up to: 4.9.5
Requires PHP: 5.6.30
Stable tag: 0.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds EXIF (time and geotag) fields to the attachment media modal, for use by other plugins.

== Description ==

Adds EXIF (time and geotag) fields to the attachment media modal, for use by other plugins.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wpdtrt-exif` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->DTRT EXIF screen to configure the plugin

== Frequently Asked Questions ==

= How do I use this? =

This plugin adds the following custom fields to attachments:

1. `Time` (display only)
2. `Geotag` (editable)

`Geotag` is saved to the following custom field, which can be queried by other plugins:

* `wpdtrt_exif_attachment_geotag`

== Screenshots ==

1. The caption for ./images/screenshot-1.(png|jpg|jpeg|gif)
2. The caption for ./images/screenshot-2.(png|jpg|jpeg|gif)

== Changelog ==

= 0.2.2 =
* Update wpdtrt-plugin-boilerplate to 1.5.3
* Sync with generator-wpdtrt-plugin-boilerplate 0.8.2

= 0.2.1 =
* Update wpdtrt-plugin-boilerplate to 1.5.0

= 0.2.0 =
* Update wpdtrt-plugin-boilerplate to 1.5.0
* Sync with generator-wpdtrt-plugin-boilerplate 0.8.0

= 0.1.12 =
* Update wpdtrt-plugin-boilerplate to 1.4.39
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.27

= 0.1.11 =
* Use public packages

= 0.1.10 =
* Update wpdtrt-plugin-boilerplate to 1.4.38
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.25

= 0.1.9 =
* Update wpdtrt-plugin-boilerplate to 1.4.25
* Sync with generator-wpdtrt-plugin-boilerplate 0.7.20	

= 0.1.8 =
* Update wpdtrt-plugin-boilerplate to 1.4.24
* Prefer stable versions, but allow dev versions

= 0.1.7 =
* Move includes and attachment filter hooks into root file
* Fix DMS to DD conversion, adding one dependency via Private Packagist as it has no releases
* Remove TGMPA from Composer file
* Fix tests, comment out broken virtual file tests
* Fixes for PHPCS

= 0.1.6 =
* Update wpdtrt-plugin to wpdtrt-plugin-boilerplate
* Update wpdtrt-plugin-boilerplate to 1.4.22
* Add missing functions including those from the theme
* Update URL in Google API key hint
* Add tests (failing)
* Move test images into tests folder

= 0.1.5 =
* Update wpdtrt-plugin-boilerplate to 1.4.15

= 0.1.4 =
* Update wpdtrt-plugin-boilerplate to 1.4.14

= 0.1.3 =
* Demote dotherightthing/wpdtrt-exif to require-dev (test dependency)
* Fix path to autoloader when loaded as a test dependency

= 0.1.2 =
* Include release number in wpdtrt-plugin-boilerplate namespaces
* Update wpdtrt-plugin-boilerplate to 1.4.6

= 0.1.1 =
* Update wpdtrt-plugin-boilerplate to 1.3.6

= 0.1.0 =
* Initial version
* Update wpdtrt-plugin-boilerplate to 1.3.1

== Upgrade Notice ==

= 0.1.0 =
* Initial release
