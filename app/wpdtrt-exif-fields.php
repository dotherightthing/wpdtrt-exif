<?php
/**
 * Add EXIF fields (Time and GPS) to attachment media modal
 *
 * This file contains PHP.
 *
 * @since       0.1.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-exif/
 *
 * @package     WPDTRT_EXIF
 * @subpackage  WPDTRT_EXIF/app
 */

/**
 * Add GPS field to media uploader, for GPS dependent functions (map, weather)
 * Writing the EXIF back to the image is a hassle, so we can query the GPS in the future, instead.
 *
 * Note: except for 'title', unwanted fields cannot be removed from the attachment modal
 * @see https://codex.wordpress.org/Function_Reference/remove_post_type_support
 * @see https://core.trac.wordpress.org/ticket/23932
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, the $post object describing the attachment being edited
 * @return $form_fields, modified form fields
 * @see https://codex.wordpress.org/Function_Reference/get_attached_file
 * @see attachment.php
 * @see wp-admin/includes/media.php
 * @see wp-includes/media-template.php
 */
function wpdtrt_exif_attachment_field( $form_fields, $post ) {

  // NOTE:
  // $post->ID // object reference (@param type)
  // $post['ID'] // array reference

  // this will also return the attachment geotag if it is available
  $attachment_metadata = wpdtrt_exif_get_attachment_metadata( $post->ID );

  // wp_get_attachment_link has been overwritten to pass settings to JS, so it only ever points to the 'large' version
  //wpdtrt_log( 'TEST 1: ' . wp_get_attachment_image($post->ID, 'full') );
  //wpdtrt_log( 'TEST 2: ' . wp_get_attachment_link($post->ID, 'full') );

  // Time is read only

  if ( !empty( $attachment_metadata['image_meta']['created_timestamp'] ) ) {

    $timestamp_format = 'd:m:Y h:i:s';

    $form_fields['wpdtrt-exif-time'] = array(
      'label' => 'Time',
      'input' => 'html',
      'html' => '<input type="text" readonly="readonly" value="' . date( $timestamp_format, $attachment_metadata['image_meta']['created_timestamp'] ) . '" />',
    );
  }

  // Geotag is read and write

  $attachment_metadata_gps = wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, 'number' );

  $attachment_metadata_gps_source = '';

  // if the values can be pulled from the image
  if ( isset( $attachment_metadata_gps['latitude'], $attachment_metadata_gps['longitude'] ) ) {
    // then display these values to content admins
    $value = ( $attachment_metadata_gps['latitude'] . ',' . $attachment_metadata_gps['longitude'] );
    $attachment_metadata_gps_source = 'WordPress'; // i.e. Image metadata has been stored to WordPress as attachment metadata
  }
  // else try to pull these values from the user field
  else {
    $value = get_post_meta( $post->ID, 'wpdtrt_exif_attachment_geotag', true );
    $attachment_metadata_gps_source = 'Custom Field';
  }

  $gmap = '';

  if ( $value !== '' ) {
    $gmap .= 'https://maps.googleapis.com/maps/api/staticmap?';
    $gmap .= 'maptype=satellite';
    $gmap .= '&center=' . $value;
    $gmap .= '&zoom=8';
    $gmap .= '&size=150x150';
    $gmap .= '&markers=color:0xff0000|' . $value;
    $gmap .= '&key=AIzaSyAyMI7z2mnFYdONaVV78weOmB0U2LThZMo';
  }

  $form_fields['wpdtrt-exif-geotag'] = array(
    'label' => 'Geotag',
    'input' => 'text',
    'value' => $value,
    'helps' => '<img src="' . $gmap . '" alt="' . $value . '" title="' . $value . '" width="150" height="150"><br>Geotag source: ' . $attachment_metadata_gps_source,
  );

  return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_exif_attachment_field', 10, 2 );

/**
 * Save value of EXIF field in media uploader, for GPS dependent functions (map, weather)
 *
 * @param $post array, Post attributes.
 * @param $attachment array, attachment fields (form submitted via Ajax)
 * @return $post array, modified post data
 *
 * @todo wp_update_attachment_metadata rather than update_post_meta, making update_post_meta redundant
 * @see https://github.com/dotherightthing/wpdtrt-exif/issues/2
 */

function wpdtrt_exif_attachment_field_save( $post, $attachment ) {

  // NOTE:
  // $post->ID // object reference
  // $post['ID'] // array reference (@param type)

  if ( isset( $attachment['wpdtrt-exif-geotag'] ) ) {

    /*
    // Copy user input from custom field to attachment metadata,
    // converting $attachment['wpdtrt-exif-geotag'] array(lat,lng) to the exif format that WP uses

    $attachment_metadata = wp_get_attachment_metadata( $attachment_id, false );

    $attachment_metadata_updated = $attachment_metadata;

    $user_dd = explode( ',', $attachment['wpdtrt-exif-geotag'] );
    $user_lat_dms_fr = wpdtrt_exif_convert_dd_to_dms( $user_dd[0] );
    $user_long_dms_fr = wpdtrt_exif_convert_dd_to_dms( $user_dd[1] );

    $attachment_metadata_updated['image_meta']['latitude'] =      $user_lat_dms_fr[; // array( 39/1, 56/1, 357/100)
    $attachment_metadata_updated['image_meta']['latitude_ref'] =  $TODO; // N
    $attachment_metadata_updated['image_meta']['longitude'] =     $user_long_dms_fr; // array( 116/1, 23/1, 4891/100 )
    $attachment_metadata_updated['image_meta']['longitude_ref'] = $TODO; // E

    wp_update_attachment_metadata( $post['ID'], $attachment_metadata_updated );
    */

    update_post_meta( $post['ID'], 'wpdtrt_exif_attachment_geotag', $attachment['wpdtrt-exif-geotag'] );
  }

  return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_exif_attachment_field_save', 10, 2 );


?>