<?php
/**
 * JS imports
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-exif
 * @see         https://codex.wordpress.org/AJAX_in_Plugins
 * @since       0.1.0
 *
 * @package     WPDTRT_EXIF
 * @subpackage  WPDTRT_EXIF/app
 */

if ( !function_exists( 'wpdtrt_exif_js' ) ) {

  /**
   * Attach JS for front-end widgets and shortcodes
   *    Generate a configuration object which the JavaScript can access.
   *    When an Ajax command is submitted, pass it to our function via the Admin Ajax page.
   *
   * @since       0.1.0
   * @see         https://codex.wordpress.org/AJAX_in_Plugins
   * @see         https://codex.wordpress.org/Function_Reference/wp_localize_script
   */
  function wpdtrt_exif_js() {

    $attach_to_footer = true;

    /**
     * Registering scripts is technically not necessary, but highly recommended nonetheless.
     *
     * Scripts that have been pre-registered using wp_register_script()
     * do not need to be manually enqueued using wp_enqueue_script()
     * if they are listed as a dependency of another script that is enqueued.
     * WordPress will automatically include the registered script
     * before it includes the enqueued script that lists the registered script’s handle as a dependency.
     *
     * Note: If a dependency is shared between plugins/theme,
     *  the hook must match, otherwise the dependency will be loaded twice,
     *  potentially overriding variables and generating errors.
     *
     * @see https://developer.wordpress.org/reference/functions/wp_register_script/#more-information
     */

    /*
    wp_register_script( 'a_dependency',
      WPDTRT_EXIF_URL . 'vendor/bower_components/a_dependency/a_dependency.js',
      array(
        'jquery'
      ),
      DEPENDENCY_VERSION,
      $attach_to_footer
    );
    */

    wp_enqueue_script( 'wpdtrt_exif',
      WPDTRT_EXIF_URL . 'js/wpdtrt-exif.js',
      array(
        // load these registered dependencies first:
        'jquery'
        // a_dependency
      ),
      WPDTRT_EXIF_VERSION,
      $attach_to_footer
    );

    wp_localize_script( 'wpdtrt_exif',
      'wpdtrt_exif_config',
      array(
        'ajax_url' => admin_url( 'admin-ajax.php' ) // wpdtrt_exif_config.ajax_url
      )
    );

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_exif_js' );

}

?>
