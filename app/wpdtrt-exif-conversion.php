<?php
/**
 * Convert GPS data
 *
 * This file contains PHP.
 *
 * @since       0.1.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 *
 * @package     WPDTRT_EXIF
 * @subpackage  WPDTRT_EXIF/app
 */

/**
 * Convert from Degrees Minutes Seconds fractions, to Decimal Degrees
 * for Google Maps
 *
 * Note: A parallel version of this script
 * used wpdtrt_exif_dms_to_number() instead of wp_exif_frac2dec()
 * used wpdtrt_exif_gps_dms_to_decimal() instead of wpdtrt_exif_convert_dms_to_dd()
 *
 * @uses wp_exif_frac2dec
 * @see http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
 * @see https://tmackinnon.com/converting-decimal-degrees-to-degrees-minutes-seconds.php
 */
function wpdtrt_exif_convert_dms_to_dd($dms_fractions) {

  $degrees = wp_exif_frac2dec( $dms_fractions[0] ); // 52/1 -> 52 -> 52
  $minutes = wp_exif_frac2dec( $dms_fractions[1] ); // 17/1 -> 17 /60 -> 0.283333333333333
  $seconds = wp_exif_frac2dec( $dms_fractions[2] ); // 2282/100 -> 22.82 /60 -> 0.380333333333333

  // 52.6636666667
  $decimal_degrees = $degrees + $minutes/60 + $seconds/60;

  return $decimal_degrees;
}

?>