<?php
/**
 * Plugin Name:  DTRT EXIF
 * Plugin URI:   https://github.com/dotherightthing/wpdtrt-exif
 * Description:  Adds EXIF (time and geotag) fields to the attachment media modal, for use by other plugins.
 * Version:      0.1.6
 * Author:       Dan Smith
 * Author URI:   https://profiles.wordpress.org/dotherightthingnz
 * License:      GPLv2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wpdtrt-exif
 * Domain Path:  /languages
 */

/**
 * Constants
 * WordPress makes use of the following constants when determining the path to the content and plugin directories.
 * These should not be used directly by plugins or themes, but are listed here for completeness.
 * WP_CONTENT_DIR  // no trailing slash, full paths only
 * WP_CONTENT_URL  // full url
 * WP_PLUGIN_DIR  // full path, no trailing slash
 * WP_PLUGIN_URL  // full url, no trailing slash
 *
 * WordPress provides several functions for easily determining where a given file or directory lives.
 * Always use these functions in your plugins instead of hard-coding references to the wp-content directory
 * or using the WordPress internal constants.
 * plugins_url()
 * plugin_dir_url()
 * plugin_dir_path()
 * plugin_basename()
 *
 * @see https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Constants
 * @see https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Plugins
 */

if ( ! defined( 'WPDTRT_EXIF_VERSION' ) ) {
  /**
   * Plugin version.
   *
   * WP provides get_plugin_data(), but it only works within WP Admin,
   * so we define a constant instead.
   *
   * @see $plugin_data = get_plugin_data( __FILE__ ); $plugin_version = $plugin_data['Version'];
   * @see https://wordpress.stackexchange.com/questions/18268/i-want-to-get-a-plugin-version-number-dynamically
   */
  define( 'WPDTRT_EXIF_VERSION', '0.1.6' );
}

if ( ! defined( 'WPDTRT_EXIF_PATH' ) ) {
  /**
   * Plugin directory filesystem path.
   *
   * @param string $file
   * @return The filesystem directory path (with trailing slash)
   * @see https://developer.wordpress.org/reference/functions/plugin_dir_path/
   * @see https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
   */
  define( 'WPDTRT_EXIF_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'WPDTRT_EXIF_URL' ) ) {
  /**
   * Plugin directory URL path.
   *
   * @param string $file
   * @return The URL (with trailing slash)
   * @see https://codex.wordpress.org/Function_Reference/plugin_dir_url
   * @see https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
   */
  define( 'WPDTRT_EXIF_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * ===== Dependencies =====
 */

/**
 * Determine the correct path, from wpdtrt-plugin-boilerplate-boilerplate to the PSR-4 autoloader
 *
 * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/issues/51
 */
if ( ! defined( 'WPDTRT_PLUGIN_CHILD' ) ) {
  define( 'WPDTRT_PLUGIN_CHILD', true );
}

/**
 * Determine the correct path, from wpdtrt-foobar to the PSR-4 autoloader
 *
 * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/issues/104
 * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/wiki/Options:-Adding-WordPress-plugin-dependencies
 */
if ( defined( 'WPDTRT_EXIF_TEST_DEPENDENCY' ) ) {
  $project_root_path = realpath( __DIR__ . '/../../..' ) . '/';
} else {
  $project_root_path = '';
}

require_once $project_root_path . 'vendor/autoload.php';

// sub classes, not loaded via PSR-4.
// comment out the ones you don't need, edit the ones you do.
require_once WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-plugin.php';
//require_once WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-rewrite.php';
//require_once WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-shortcode.php';
//require_once WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-taxonomy.php';
//require_once WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-widget.php';

// access wp_read_image_metadata
// remove trailing slash from ABSPATH
// works for local unit testing, but fails in local WordPress and in Travis
//if ( substr($test_path, -1) == '/' ) {
//  $test_path = substr($test_path, 0, -1);
//}
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// legacy helpers
require_once(WPDTRT_EXIF_PATH . 'src/legacy/attachment-field-heading.php');
require_once(WPDTRT_EXIF_PATH . 'src/legacy/attachment-field-time.php');
require_once(WPDTRT_EXIF_PATH . 'src/legacy/attachment-field-gps.php');

// log & trace helpers.
global $debug;
$debug = new DoTheRightThing\WPDebug\Debug;

/**
 * ===== WordPress Integration =====
 *
 * Comment out the actions you don't need.
 *
 * Notes:
 *  Default priority is 10. A higher priority runs later.
 *  register_activation_hook() is run before any of the provided hooks.
 *
 * @see https://developer.wordpress.org/plugins/hooks/actions/#priority
 * @see https://codex.wordpress.org/Function_Reference/register_activation_hook.
 */
register_activation_hook( dirname( __FILE__ ), 'wpdtrt_exif_helper_activate' );

add_action( 'init', 'wpdtrt_exif_plugin_init', 0 );
//add_action( 'init', 'wpdtrt_exif_shortcode_init', 100 );
//add_action( 'init', 'wpdtrt_exif_taxonomy_init', 100 );
//add_action( 'widgets_init', 'wpdtrt_exif_widget_init', 10 );

register_deactivation_hook( dirname( __FILE__ ), 'wpdtrt_exif_helper_deactivate' );

/**
 * ===== Plugin config =====
 */

/**
 * Register functions to be run when the plugin is activated.
 *
 * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
 * @todo https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/issues/128
 * @see See also Plugin::helper_flush_rewrite_rules()
 */
function wpdtrt_exif_helper_activate() {
  flush_rewrite_rules();
}

/**
 * Register functions to be run when the plugin is deactivated.
 * (WordPress 2.0+)
 *
 * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
 * @todo https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/issues/128
 * @see See also Plugin::helper_flush_rewrite_rules()
 */
function wpdtrt_exif_helper_deactivate() {
  flush_rewrite_rules();
}

/**
 * Plugin initialisaton
 *
 * We call init before widget_init so that the plugin object properties are available to it.
 * If widget_init is not working when called via init with priority 1, try changing the priority of init to 0.
 * init: Typically used by plugins to initialize. The current user is already authenticated by this time.
 * widgets_init: Used to register sidebars. Fired at 'init' priority 1 (and so before 'init' actions with priority ≥ 1!)
 *
 * @see https://wp-mix.com/wordpress-widget_init-not-working/
 * @see https://codex.wordpress.org/Plugin_API/Action_Reference
 * @todo Add a constructor function to WPDTRT_Blocks_Plugin, to explain the options array
 */
function wpdtrt_exif_plugin_init() {
  // pass object reference between classes via global
  // because the object does not exist until the WordPress init action has fired
  global $wpdtrt_exif_plugin;

  /**
   * Global options
   *
   * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/wiki/Options:-Adding-global-options Options: Adding global options
   */
  $plugin_options = array(
    'google_static_maps_api_key' => array(
      'type'  => 'password',
      'label' => __('Google Static Maps API Key', 'wpdtrt-exif'),
      'size'  => 50,
      'tip'   => __('https://developers.google.com/maps/documentation/maps-static/get-api-key', 'wpdtrt-exif')
    )
  );

  /**
   * Shortcode or Widget options
   *
   * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/wiki/Options:-Adding-shortcode-or-widget-options Options: Adding shortcode or widget options
   */
  $instance_options = array();

  /**
   * Plugin dependencies
   *
   * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/wiki/Options:-Adding-WordPress-plugin-dependencies Options: Adding WordPress plugin dependencies
   */
  $plugin_dependencies = array();

  /**
   *  UI Messages
   */
  $ui_messages = array(
    'demo_data_description'       => __( 'This demo was generated from the following data', 'wpdtrt-exif' ),
    'demo_data_displayed_length'  => __( 'results displayed', 'wpdtrt-exif' ),
    'demo_data_length'            => __( 'results', 'wpdtrt-exif' ),
    'demo_data_title'             => __( 'Demo data', 'wpdtrt-exif' ),
    'demo_date_last_updated'      => __( 'Data last updated', 'wpdtrt-exif' ),
    'demo_sample_title'           => __( 'Demo sample', 'wpdtrt-exif' ),
    'demo_shortcode_title'        => __( 'Demo shortcode', 'wpdtrt-exif' ),
    'insufficient_permissions'    => __( 'Sorry, you do not have sufficient permissions to access this page.', 'wpdtrt-exif' ),
    'no_options_form_description' => __( 'There aren\'t currently any options.', 'wpdtrt-exif' ),
    'noscript_warning'            => __( 'Please enable JavaScript', 'wpdtrt-exif' ),
    'options_form_description'    => __( 'Please enter your preferences.', 'wpdtrt-exif' ),
    'options_form_submit'         => __( 'Save Changes', 'wpdtrt-exif' ),
    'options_form_title'          => __( 'General Settings', 'wpdtrt-exif' ),
    'loading'                     => __( 'Loading latest data...', 'wpdtrt-exif' ),
    'success'                     => __( 'settings successfully updated', 'wpdtrt-exif' ),
  );

  /**
   * Demo shortcode
   *
   * @see https://github.com/dotherightthing/wpdtrt-plugin-boilerplate-boilerplate/wiki/Settings-page:-Adding-a-demo-shortcode Settings page: Adding a demo shortcode
   */
  $demo_shortcode_params = array();

  /**
   * Plugin configuration
   */
  $wpdtrt_exif_plugin = new WPDTRT_Contentsections_Plugin(
    array(
      'path'                  => WPDTRT_EXIF_PATH,
      'url'                   => WPDTRT_EXIF_URL,
      'version'               => WPDTRT_EXIF_VERSION,
      'prefix'                => 'wpdtrt_exif',
      'slug'                  => 'wpdtrt-exif',
      'menu_title'            => __( 'EXIF', 'wpdtrt-exif' ),
      'settings_title'        => __( 'Settings', 'wpdtrt-exif' ),
      'developer_prefix'      => 'DTRT',
      'messages'              => $ui_messages,
      'plugin_options'        => $plugin_options,
      'instance_options'      => $instance_options,
      'plugin_dependencies'   => $plugin_dependencies,
      'demo_shortcode_params' => $demo_shortcode_params,
    )
  );
}

/**
 * ===== Rewrite config =====
 */

/**
 * Register Rewrite
 */
function wpdtrt_exif_rewrite_init() {

  global $wpdtrt_exif_plugin;

  $wpdtrt_exif_rewrite = new WPDTRT_Contentsections_Rewrite(
    array()
  );
}

/**
 * ===== Shortcode config =====
 */

/**
 * Register Shortcode
 */
function wpdtrt_exif_shortcode_init() {

  global $wpdtrt_exif_plugin;

  $wpdtrt_exif_shortcode = new WPDTRT_Contentsections_Shortcode(
    array()
  );
}

/**
 * ===== Taxonomy config =====
 */

/**
 * Register Taxonomy
 *
 * @return object Taxonomy/
 */
function wpdtrt_exif_taxonomy_init() {

  global $wpdtrt_exif_plugin;

  $wpdtrt_exif_taxonomy = new WPDTRT_Contentsections_Taxonomy(
    array()
  );

  // return a reference for unit testing.
  return $wpdtrt_exif_taxonomy;
}

/**
 * ===== Widget config =====
 */

/**
 * Register a WordPress widget, passing in an instance of our custom widget class
 * The plugin does not require registration, but widgets and shortcodes do.
 * Note: widget_init fires before init, unless init has a priority of 0
 *
 * @uses        ../../../../wp-includes/widgets.php
 * @see         https://codex.wordpress.org/Function_Reference/register_widget#Example
 * @see         https://wp-mix.com/wordpress-widget_init-not-working/
 * @see         https://codex.wordpress.org/Plugin_API/Action_Reference
 * @uses        https://github.com/dotherightthing/wpdtrt/tree/master/library/sidebars.php
 * @todo        Add form field parameters to the options array
 * @todo        Investigate the 'classname' option
 */
function wpdtrt_exif_widget_init() {

  global $wpdtrt_exif_plugin;

  $wpdtrt_exif_widget = new WPDTRT_Contentsections_Widget(
    array()
  );

  register_widget( $wpdtrt_exif_widget );
}
