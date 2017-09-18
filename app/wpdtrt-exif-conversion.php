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
 * @param $dms_fractions Degrees Minutes Seconds fractions
 * @return string $decimal_degrees
 *
 * @uses wp_exif_frac2dec
 * @see http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
 * @see https://tmackinnon.com/converting-decimal-degrees-to-degrees-minutes-seconds.php
 */
function wpdtrt_exif_convert_dms_to_dd($dms_fractions) {

	// dms_fractions = array( 52/1, 17/1, 2282/100 );

	$degrees = wp_exif_frac2dec( $dms_fractions[0] ); // 52/1 -> 52 -> 52
	$minutes = wp_exif_frac2dec( $dms_fractions[1] ); // 17/1 -> 17 /60 -> 0.283333333333333
	$seconds = wp_exif_frac2dec( $dms_fractions[2] ); // 2282/100 -> 22.82 /60 -> 0.380333333333333

	// 52.6636666667
	$decimal_degrees = $degrees + $minutes/60 + $seconds/60;

	return $decimal_degrees;
}

/**
 * Converts from Decimal Degrees to Degrees Minutes Seconds
 *
 * @param $dd Decimal Degrees
 * @return array($degrees, $minutes, $seconds)
 *
 * @see https://stackoverflow.com/a/7927527/6850747
 * @uses https://www.web-max.ca/PHP/misc_6.php
 * @todo Not generating the WP format yet
 */
function wpdtrt_exif_convert_dd_to_dms($dd) {

	// To avoid issues with floating
	// point math we extract the integer part and the float
	// part by using a string function.

    $vars = explode( ".", $dd );

    $deg = $vars[0];
    $tempma = "0." . $vars[1];

    $tempma = $tempma * 3600;
    $min = floor( $tempma / 60 );
    $sec = $tempma - ( $min * 60 );

    return array(
    	$deg,
    	$min,
    	$sec
    );
}

/**
 * Test the two-way conversion from DMS to DD
 *
 * @param $latitude_dms_fr_1 Latitude in Degrees Minutes Seconds fractions
 *
 * @see https://github.com/dotherightthing/wpdtrt-exif/issues/2
 */
function wpdtrt_exif_convert_test( $latitude_dms_fr_1 ) {

	$latitude_dd_1 = 		wpdtrt_exif_convert_dms_to_dd( $latitude_dms_fr_1 );
	$latitude_dms_fr_2 = 	wpdtrt_exif_convert_dd_to_dms( $latitude_dd_1 );
	//$latitude_dd_1 =   	wpdtrt_exif_convert_dms_to_dd( $latitude_dms_fr_2 );

	wpdtrt_log( '==== wpdtrt_exif_convert_test ====' );

	/*
	From DMS Fractions:

	Array
	(
	    [0] => 39/1
	    [1] => 56/1
	    [2] => 375/100
	)
	*/
	wpdtrt_log( $latitude_dms_fr_1 );

	/*
	To Decimal Degrees:

	39.9958333333
	*/
	wpdtrt_log( $latitude_dd_1 );

	/*
	To DMS Fractions

	Array
	(
	    [0] => 39
	    [1] => 59
	    [2] => 44.99999988
	)
	*/
	wpdtrt_log( $latitude_dms_fr_2 );

	/*
	To Decimal Degrees:

	*/
	//wpdtrt_log( $latitude_dd_2 );

}

?>