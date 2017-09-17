<?php
/**
 * CSS imports
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-exif
 * @since       0.1.0
 *
 * @package     WPDTRT_EXIF
 * @subpackage  WPDTRT_EXIF/app
 */

if ( !function_exists( 'wpdtrt_exif_css_backend' ) ) {

  /**
   * Attach CSS for Settings > DTRT EXIF
   *
   * @since       0.1.0
   */
  function wpdtrt_exif_css_backend() {

     $media = 'all';

    wp_enqueue_style( 'wpdtrt_exif_css_backend',
      WPDTRT_EXIF_URL . 'css/wpdtrt-exif-admin.css',
      array(),
      WPDTRT_EXIF_VERSION,
      $media
    );
  }

  add_action( 'admin_head', 'wpdtrt_exif_css_backend' );

}

if ( !function_exists( 'wpdtrt_exif_css_frontend' ) ) {

  /**
   * Attach CSS for front-end widgets and shortcodes
   *
   * @since       0.1.0
   */
  function wpdtrt_exif_css_frontend() {

    $media = 'all';

    /*
    wp_register_style( 'a_dependency',
      WPDTRT_EXIF_URL . 'vendor/bower_components/a_dependency/a_dependency.css',
      array(),
      DEPENDENCY_VERSION,
      $media
    );
    */

    wp_enqueue_style( 'wpdtrt_exif',
      WPDTRT_EXIF_URL . 'css/wpdtrt-exif.css',
      array(
        // load these registered dependencies first:
        'a_dependency'
      ),
      WPDTRT_EXIF_VERSION,
      $media
    );

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_exif_css_frontend' );

}

?>
