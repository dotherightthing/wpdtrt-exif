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

/**
 * Convert Degrees Minutes Seconds String To Number
 * @param string $string The String
 * @return number $number The number
 */
function wpdtrt_exif_dms_to_number( $string ) {

  $number = 0;

  $pos = strpos($string, '/');

  if ($pos !== false) {
    $temp = explode('/', $string);
    $number = $temp[0] / $temp[1];
  }

  return $number;
}

/**
 * Convert Degrees Minutes Seconds to Decimal Degrees
 * @param string $reference_direction (n/s/e/w)
 * @param string $degrees Degrees
 * @param string $minutes Minutes
 * @param string $seconds Seconds
 * @return string $decimal The decimal value
 */
function wpdtrt_exif_gps_dms_to_decimal( $reference_direction, $degrees, $minutes, $seconds ) {

  // http://stackoverflow.com/a/32611358
  // http://stackoverflow.com/a/19420991
  // https://www.mail-archive.com/pkg-perl-maintainers@lists.launchpad.net/msg02335.html

  $degrees = wpdtrt_exif_dms_to_number( $degrees );
  $minutes = wpdtrt_exif_dms_to_number( $minutes );
  $seconds = wpdtrt_exif_dms_to_number( $seconds );

  $decimal = ( $degrees + ( $minutes / 60 ) + ( $seconds / 3600 ) );

  //If the latitude is South, or the longitude is West, make it negative.
  if ( ( $reference_direction === 'S' ) || ( $reference_direction === 'W' ) ) {
    $decimal *= -1;
  }

  return $decimal;
}

?>