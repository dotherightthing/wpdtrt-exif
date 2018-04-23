<?php
/**
 * Plugin Name:  DTRT EXIF
 * Plugin URI:   https://github.com/dotherightthing/wpdtrt-exif
 * Description:  Adds EXIF (time and geotag) fields to the attachment media modal, for use by other plugins.
 * Version:      0.0.1
 * Author:       Dan Smith
 * Author URI:   https://profiles.wordpress.org/dotherightthingnz
 * License:      GPLv2 or later
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  wpdtrt-exif
 * Domain Path:  /languages
 */

require_once plugin_dir_path( __FILE__ ) . "vendor/autoload.php";

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
 * @link https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Constants
 * @link https://codex.wordpress.org/Determining_Plugin_and_Content_Directories#Plugins
 */

/**
  * Determine the correct path to the autoloader
  * @see https://github.com/dotherightthing/wpdtrt-plugin/issues/51
  */
if( ! defined( 'WPDTRT_PLUGIN_CHILD' ) ) {
  define( 'WPDTRT_PLUGIN_CHILD', true );
}

if( ! defined( 'WPDTRT_EXIF_VERSION' ) ) {
/**
 * Plugin version.
 *
 * WP provides get_plugin_data(), but it only works within WP Admin,
 * so we define a constant instead.
 *
 * @example $plugin_data = get_plugin_data( __FILE__ ); $plugin_version = $plugin_data['Version'];
 * @link https://wordpress.stackexchange.com/questions/18268/i-want-to-get-a-plugin-version-number-dynamically
 *
 * @version   0.0.1
 * @since     0.7.5
 */
  define( 'WPDTRT_EXIF_VERSION', '0.0.1' );
}

if( ! defined( 'WPDTRT_EXIF_PATH' ) ) {
/**
 * Plugin directory filesystem path.
 *
 * @param string $file
 * @return The filesystem directory path (with trailing slash)
 *
 * @link https://developer.wordpress.org/reference/functions/plugin_dir_path/
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 *
 * @version   0.0.1
 * @since     0.7.5
 */
  define( 'WPDTRT_EXIF_PATH', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'WPDTRT_EXIF_URL' ) ) {
/**
 * Plugin directory URL path.
 *
 * @param string $file
 * @return The URL (with trailing slash)
 *
 * @link https://codex.wordpress.org/Function_Reference/plugin_dir_url
 * @link https://developer.wordpress.org/plugins/the-basics/best-practices/#prefix-everything
 *
 * @version   0.0.1
 * @since     0.7.5
 */
  define( 'WPDTRT_EXIF_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * Include plugin logic
 *
 * @version   0.0.1
 * @since     0.7.5
 */

  // base class
  // redundant, but includes the composer-generated autoload file if not already included
  require_once(WPDTRT_EXIF_PATH . 'vendor/dotherightthing/wpdtrt-plugin/index.php');

  // classes without composer.json files are loaded via Bower
  //require_once(WPDTRT_EXIF_PATH . 'vendor/name/file.php');

  // sub classes
  require_once(WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-plugin.php');
  require_once(WPDTRT_EXIF_PATH . 'src/class-wpdtrt-exif-widgets.php');

  // wordpress
  $test_path = ABSPATH;

  // remove trailing slash from ABSPATH
  // works for local unit testing, but fails in local WordPress and in Travis
  //if ( substr($test_path, -1) == '/' ) {
  //  $test_path = substr($test_path, 0, -1);
  //}

  require_once( $test_path . 'wp-admin/includes/image.php' ); // access wp_read_image_metadata

  // legacy helpers
  require_once(WPDTRT_EXIF_PATH . 'src/legacy/attachment-field-time.php');
  require_once(WPDTRT_EXIF_PATH . 'src/legacy/attachment-field-gps.php');

  // log & trace helpers
  $debug = new DoTheRightThing\WPDebug\Debug;

  /**
   * Plugin initialisaton
   *
   * We call init before widget_init so that the plugin object properties are available to it.
   * If widget_init is not working when called via init with priority 1, try changing the priority of init to 0.
   * init: Typically used by plugins to initialize. The current user is already authenticated by this time.
   * └─ widgets_init: Used to register sidebars. Fired at 'init' priority 1 (and so before 'init' actions with priority ≥ 1!)
   *
   * @see https://wp-mix.com/wordpress-widget_init-not-working/
   * @see https://codex.wordpress.org/Plugin_API/Action_Reference
   * @todo Add a constructor function to WPDTRT_Exif_Plugin, to explain the options array
   */
  function wpdtrt_exif_init() {
    // pass object reference between classes via global
    // because the object does not exist until the WordPress init action has fired
    global $wpdtrt_exif_plugin;

    /**
     * Admin settings
     * For array syntax, please view the field documentation:
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-checkbox.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-number.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-password.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-select.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-text.php
     */
    $plugin_options = array(
      'google_static_maps_api_key' => array(
        'type' => 'text',
        'label' => __('Google Static Maps API Key', 'wpdtrt-exif'),
        'size' => 50,
        'tip' => __('https://developers.google.com/maps/documentation/static-maps/ > GET A KEY', 'wpdtrt-exif')
      )
    );

    /**
     * All options available to Widgets and Shortcodes
     * For array syntax, please view the field documentation:
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-checkbox.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-number.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-password.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-select.php
     * @see https://github.com/dotherightthing/wpdtrt-plugin/blob/master/views/form-element-text.php
     */
    $instance_options = array();

    $wpdtrt_exif_plugin = new WPDTRT_Exif_Plugin(
      array(
        'url' => WPDTRT_EXIF_URL,
        'prefix' => 'wpdtrt_exif',
        'slug' => 'wpdtrt-exif',
        'menu_title' => __('EXIF', 'wpdtrt-exif'),
        'developer_prefix' => '',
        'path' => WPDTRT_EXIF_PATH,
        'messages' => array(
          'loading' => __('Loading latest data...', 'wpdtrt-exif'),
          'success' => __('settings successfully updated', 'wpdtrt-exif'),
          'insufficient_permissions' => __('Sorry, you do not have sufficient permissions to access this page.', 'wpdtrt-exif'),
          'options_form_title' => __('General Settings', 'wpdtrt-exif'),
          'options_form_description' => __('Please enter your preferences.', 'wpdtrt-exif'),
          'no_options_form_description' => __('There aren\'t currently any options.', 'wpdtrt-exif'),
          'options_form_submit' => __('Save Changes', 'wpdtrt-exif'),
          'noscript_warning' => __('Please enable JavaScript', 'wpdtrt-exif'),
          'demo_sample_title' => __('Demo sample', 'wpdtrt-exif'),
          'demo_data_title' => __('Demo data', 'wpdtrt-exif'),
          'demo_shortcode_title' => __('Demo shortcode', 'wpdtrt-exif'),
          'demo_data_description' => __('This demo was generated from the following data', 'wpdtrt-exif'),
          'demo_date_last_updated' => __('Data last updated', 'wpdtrt-exif'),
        ),
        'plugin_options' => $plugin_options,
        'instance_options' => $instance_options,
        'version' => WPDTRT_EXIF_VERSION,
        /*
        'plugin_dependencies' => array(
          array(
            'name'          => 'Plugin Name',
            'slug'          => 'plugin-name',
            'source'        => 'https://github.com/user/library/archive/master.zip',
            'required'      => true,
            'is_callable'   => 'function_name'
          )
        ),
        */
        'demo_shortcode_params' => null
      )
    );
  }

  add_action( 'init', 'wpdtrt_exif_init', 0 );

  /**
   * Register functions to be run when the plugin is activated.
   *
   * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
   *
   * @version   0.0.1
   * @since     0.7.5
   */
  function wpdtrt_exif_activate() {
    //wpdtrt_exif_rewrite_rules();
    flush_rewrite_rules();
  }

  register_activation_hook(__FILE__, 'wpdtrt_exif_activate');

  /**
   * Register functions to be run when the plugin is deactivated.
   *
   * (WordPress 2.0+)
   *
   * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
   *
   * @version   0.0.1
   * @since     0.7.5
   */
  function wpdtrt_exif_deactivate() {
    flush_rewrite_rules();
  }

  register_deactivation_hook(__FILE__, 'wpdtrt_exif_deactivate');

?>
