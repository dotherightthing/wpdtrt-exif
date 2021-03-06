<?php
/**
 * Add GPS field to attachment media modal
 * Writing the EXIF back to the image is a hassle, so we can query the GPS in the future, instead.
 * Note: except for 'title', unwanted fields cannot be removed from the attachment modal
 * This file contains PHP.
 *
 * @since       0.0.1
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Exif
 */

/**
 * Add GPS field to media uploader, for GPS dependent functions (map, weather)
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields Modified form fields
 * @see https://codex.wordpress.org/Function_Reference/remove_post_type_support
 * @see https://core.trac.wordpress.org/ticket/23932
 * @see https://codex.wordpress.org/Function_Reference/get_attached_file
 * @see attachment.php
 * @see wp-admin/includes/media.php
 * @see wp-includes/media-template.php
 */
function wpdtrt_exif_attachment_field_gps( $form_fields, $post ) {

	global $wpdtrt_exif_plugin;

	$plugin_options             = $wpdtrt_exif_plugin->get_plugin_options();
	$google_static_maps_api_key = '';

	if ( array_key_exists( 'value', $plugin_options['google_static_maps_api_key'] ) ) {
		$google_static_maps_api_key = $plugin_options['google_static_maps_api_key']['value'];
	}

	$attachment_metadata = $wpdtrt_exif_plugin->get_attachment_metadata( $post->ID );

	$attachment_metadata_gps = $wpdtrt_exif_plugin->get_attachment_metadata_gps( $attachment_metadata, 'number', $post );

	if ( isset( $attachment_metadata_gps['latitude'], $attachment_metadata_gps['longitude'] ) ) {
		// if the values can be pulled from the image
		// then display these values to content admins.
		$value                          = ( $attachment_metadata_gps['latitude'] . ',' . $attachment_metadata_gps['longitude'] );
		$attachment_metadata_gps_source = 'WordPress'; // i.e. Image metadata has been stored to WordPress as attachment metadata.
	} else {
		// else try to pull these values from the user field.
		$value                          = get_post_meta( $post->ID, 'wpdtrt_exif_attachment_gps', true );
		$attachment_metadata_gps_source = 'This field';
	}

	$gmap  = '';
	$helps = '';

	if ( '' !== $value ) {
		$gmap .= 'https://maps.googleapis.com/maps/api/staticmap?';
		$gmap .= 'maptype=satellite';
		$gmap .= '&center=' . $value;
		$gmap .= '&zoom=8';
		$gmap .= '&size=150x150';
		$gmap .= '&markers=color:0xff0000|' . $value;
		$gmap .= '&key=' . $google_static_maps_api_key;

		$helps .= '<img src="' . $gmap . '" alt="' . $value . '" title="' . $value . '" width="150" height="150">';
		$helps .= '<br>';
	}

	$form_fields['wpdtrt-exif-gps'] = array(
		'label' => 'Geotag',
		'input' => 'text',
		'value' => $value,
		'helps' => $helps . 'Source: ' . $attachment_metadata_gps_source,
	);

	return $form_fields;
}

/**
 * Save value of Time field in media uploader, for GPS dependent functions (map, weather)
 *
 * @param array $post The post data for database.
 * @param array $attachment Attachment fields from $_POST form.
 * @return array $post Modified post data
 */
function wpdtrt_exif_attachment_field_gps_save( $post, $attachment ) {
	if ( isset( $attachment['wpdtrt-exif-gps'] ) ) {

		// phpcs:disable
		/*
		// Copy user input from custom field to attachment metadata,
		// converting $attachment['wpdtrt-exif-geotag'] array(lat,lng) to the exif format that WP uses
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id, false );
		$attachment_metadata_updated = $attachment_metadata;
		$user_dd = explode( ',', $attachment['wpdtrt-exif-geotag'] );
		$user_lat_dms_fr = $this->helper_convert_dd_to_dms( $user_dd[0] );
		$user_long_dms_fr = $this->helper_convert_dd_to_dms( $user_dd[1] );
		$attachment_metadata_updated['image_meta']['latitude'] =      $user_lat_dms_fr[; // array( 39/1, 56/1, 357/100)
		$attachment_metadata_updated['image_meta']['latitude_ref'] =  $TODO; // N
		$attachment_metadata_updated['image_meta']['longitude'] =     $user_long_dms_fr; // array( 116/1, 23/1, 4891/100 )
		$attachment_metadata_updated['image_meta']['longitude_ref'] = $TODO; // E
		wp_update_attachment_metadata( $post['ID'], $attachment_metadata_updated );
		*/
		// phpcs:enable

		update_post_meta( $post['ID'], 'wpdtrt_exif_attachment_gps', $attachment['wpdtrt-exif-gps'] );
	}

	return $post;
}
