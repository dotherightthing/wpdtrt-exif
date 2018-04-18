
=== DTRT EXIF ===
Contributors: dotherightthingnz
Donate link: http://dotherightthing.co.nz
Tags: exif, geotag, attachment
Requires at least: 4.9.5
Tested up to: 4.9.5
Requires PHP: 5.6.30
Stable tag: 0.0.1
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

= 0.0.1 =
* Initial version

== Upgrade Notice ==

= 0.0.1 =
* Initial release
