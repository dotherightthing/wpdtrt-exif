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
  * Get attachment metadata including geotag
  *
  * @param $attachment_id
  * @returns array $attachment_metadata
  * @uses http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
  */

// reinstate attachment metadata accidentally deleted during development:
// ini_set('max_execution_time', 300); //300 seconds = 5 minutes

function wpdtrt_exif_get_attachment_metadata( $attachment_id ) {

  // reinstate attachment metadata accidentally deleted during development:
  // $attach_data = wp_generate_attachment_metadata( $id, get_attached_file( $id ) );
  // wp_update_attachment_metadata( $id, $attach_data );

  // get the core metadata stored with the image post
  $attachment_metadata = wp_get_attachment_metadata( $attachment_id, false );

  // if the core metadata doesn't include a GPS location
  // then this wasn't stored when the image was uploaded into WP
  // (i.e. it was uploaded before this function was written)
  // so reprocess the image
  if ( !array_key_exists('latitude', $attachment_metadata) || !array_key_exists('longitude', $attachment_metadata) ) {

    $file = get_attached_file( $attachment_id ); // full path

    // read metadata, including the GPS metadata requested by our filter wpdtrt_exif_read_image_geodata
    // this includes running exif_read_data()
    $image_metadata = wp_read_image_metadata( $file );

    // TODO: check for false values
    // and replace with the value of the custom field if it has been supplied

    // the metadata update is destructive
    // so merge the existing metadata with the metadata which we've just read from the image
    $attachment_metadata_updated = $attachment_metadata;
    $attachment_metadata_updated['image_meta'] = $image_metadata;

    // write the updated metadata to WP's database
    // note that the actual image EXIF metadata is not changed
    // i.e. this is not wp_write_image_metadata
    wp_update_attachment_metadata( $attachment_id, $attachment_metadata_updated );

    // read the updated metadata
    // TODO: is this redundant?
    $attachment_metadata = wp_get_attachment_metadata( $attachment_id, false );
  }

  return $attachment_metadata;
}

/**
 * Extract the GPS coordinates from the attachment metadata
 * @param $attachment_metadata
 * @param $format
 * @return array ($lat, $lng)
 */
function wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, $format ) {

  $lat_out = null;
  $lng_out = null;

  $latitude = $attachment_metadata['image_meta']['latitude'];
  $longitude = $attachment_metadata['image_meta']['longitude'];

  $lat = wpdtrt_exif_convert_dms_to_dd( $latitude );
  $lng = wpdtrt_exif_convert_dms_to_dd( $longitude );

  $lat_ref = $attachment_metadata['image_meta']['latitude_ref'];
  $lng_ref = $attachment_metadata['image_meta']['longitude_ref'];

  if ($lat_ref == 'S') {
    $neg_lat = '-';
  }
  else {
    $neg_lat = '';
  }

  if ($lng_ref == 'W') {
    $neg_lng = '-';
  }
  else {
    $neg_lng = '';
  }

  if ($latitude != 0 && $longitude != 0) {
    // full decimal latitude and longitude for Google Maps
    if ( $format === 'number' ) {
      $lat_out = ( $neg_lat . number_format($lat,6) );
      $lng_out = ( $neg_lng . number_format($lng, 6) );
    }
    // text based latitude and longitude for Alternative text
    else if ( $format === 'text' ) {
      $lat_out = ( geo_pretty_fracs2dec($latitude). $lat_ref );
      $lng_out = ( geo_pretty_fracs2dec($longitude) . $lng_ref );
    }
  }

  return array(
    'latitude' => $lat_out,
    'longitude' => $lng_out
  );

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