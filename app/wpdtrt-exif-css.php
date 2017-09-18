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

?>
