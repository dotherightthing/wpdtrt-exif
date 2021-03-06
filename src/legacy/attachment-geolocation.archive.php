<?php // phpcs:disable
include_once( ABSPATH . 'wp-admin/includes/image.php' ); // for access to wp_read_image_metadata

/**
  * Add Geolocation EXIF to the attachment metadata stored in the WP database
  * Added false values to prevent this function running over and over
  * if the image was taken with a non-geotagging camera
  * Source: http://kristarella.blog/2009/04/add-image-exif-metadata-to-wordpress/
  */

add_filter('wp_read_image_metadata', 'add_geo_exif','',3);

function add_geo_exif( $meta, $file, $sourceImageType ) {

  $exif = @exif_read_data( $file );

  if (!empty($exif['GPSLatitude'])) {
    $meta['latitude'] = $exif['GPSLatitude'] ;
  }
  else {
    $meta['latitude'] = false;
  }

  if (!empty($exif['GPSLatitudeRef'])) {
    $meta['latitude_ref'] = trim( $exif['GPSLatitudeRef'] );
  }
  else {
    $meta['latitude_ref'] = false;
  }

  if (!empty($exif['GPSLongitude'])) {
    $meta['longitude'] = $exif['GPSLongitude'] ;
  }
  else {
    $meta['longitude'] = false;
  }

  if (!empty($exif['GPSLongitudeRef'])) {
    $meta['longitude_ref'] = trim( $exif['GPSLongitudeRef'] );
  }
  else {
    $meta['longitude_ref'] = false;
  }

  return $meta;
}

/**
  * Generate the full decimal latitude and longitude for Google
  * Naming convention follows /wp-admin/includes/image.php
  * Source: http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
  */

function geo_single_fracs2dec($fracs) {
  return wp_exif_frac2dec($fracs[0]) +
      wp_exif_frac2dec($fracs[1]) / 60 +
      wp_exif_frac2dec($fracs[2]) / 3600;
}

/**
  * Get Latitude and Longitude from stored attachment metadata
  * Original source: http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
  */

// reinstate attachment metadata accidentally deleted during development:
// ini_set('max_execution_time', 300); //300 seconds = 5 minutes

function get_geo_exif($id, $format) {

  $lat_out = '';
  $lng_out = '';

  // reinstate attachment metadata accidentally deleted during development:
  // $attach_data = wp_generate_attachment_metadata( $id, get_attached_file( $id ) );
  // wp_update_attachment_metadata( $id, $attach_data );

  //PC::debug('1. attachment_metadata');
  $attachment_metadata = wp_get_attachment_metadata( $id, false );
  //PC::debug($attachment_metadata);

  if ( !array_key_exists('latitude', $attachment_metadata) || !array_key_exists('longitude', $attachment_metadata) ) {
    $file = get_attached_file( $id ); // full path

    //PC::debug('2. image_metadata');
    $image_metadata = wp_read_image_metadata( $file ); // extract geolocation data
    //PC::debug($image_metadata);

    //PC::debug('3. merge to update');
    $attachment_metadata_updated = $attachment_metadata;
    $attachment_metadata_updated['image_meta'] = $image_metadata;
    //PC::debug($attachment_metadata_updated);

    //PC::debug('3. wp_update_attachment_metadata');
    wp_update_attachment_metadata($id, $attachment_metadata_updated);

    //PC::debug('4. attachment_metadata');
    $attachment_metadata = wp_get_attachment_metadata( $id, false ); // try again
    //PC::debug($attachment_metadata);
  }

  $latitude = $attachment_metadata['image_meta']['latitude'];
  $longitude = $attachment_metadata['image_meta']['longitude'];
  $lat_ref = $attachment_metadata['image_meta']['latitude_ref'];
  $lng_ref = $attachment_metadata['image_meta']['longitude_ref'];

  $lat = geo_single_fracs2dec($latitude);
  $lng = geo_single_fracs2dec($longitude);

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
    // full decimal latitude and longitude for Google
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

  return array($lat_out, $lng_out);
}
// phpcs:enable
