<?php
/**
 * CSS imports
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-attachment-gps
 * @since       0.1.0
 *
 * @package     WPDTRT_Attachment_GPS
 * @subpackage  WPDTRT_Attachment_GPS/app
 */

if ( !function_exists( 'wpdtrt_attachment_gps_css_backend' ) ) {

  /**
   * Attach CSS for Settings > DTRT Attachment GPS
   *
   * @since       0.1.0
   */
  function wpdtrt_attachment_gps_css_backend() {

     $media = 'all';

    wp_enqueue_style( 'wpdtrt_attachment_gps_css_backend',
      WPDTRT_ATTACHMENT_GPS_URL . 'css/wpdtrt-attachment-gps-admin.css',
      array(),
      WPDTRT_ATTACHMENT_GPS_VERSION,
      $media
    );
  }

  add_action( 'admin_head', 'wpdtrt_attachment_gps_css_backend' );

}

if ( !function_exists( 'wpdtrt_attachment_gps_css_frontend' ) ) {

  /**
   * Attach CSS for front-end widgets and shortcodes
   *
   * @since       0.1.0
   */
  function wpdtrt_attachment_gps_css_frontend() {

    $media = 'all';

    /*
    wp_register_style( 'a_dependency',
      WPDTRT_ATTACHMENT_GPS_URL . 'vendor/bower_components/a_dependency/a_dependency.css',
      array(),
      DEPENDENCY_VERSION,
      $media
    );
    */

    wp_enqueue_style( 'wpdtrt_attachment_gps',
      WPDTRT_ATTACHMENT_GPS_URL . 'css/wpdtrt-attachment-gps.css',
      array(
        // load these registered dependencies first:
        'a_dependency'
      ),
      WPDTRT_ATTACHMENT_GPS_VERSION,
      $media
    );

  }

  add_action( 'wp_enqueue_scripts', 'wpdtrt_attachment_gps_css_frontend' );

}

?>
