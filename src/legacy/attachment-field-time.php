<?php
/**
 * Add Read Only Time field to attachment media modal
 * 	Note: except for 'title', unwanted fields cannot be removed from the attachment modal
 * 	This file contains PHP.
 *
 * @since       0.0.1
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 *
 * @package     WPDTRT_Exif
 */

/**
 * Add Time field to media uploader, for Time dependent functions (map, weather)
 * 	Note: except for 'title', unwanted fields cannot be removed from the attachment modal
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 *
 * @see https://codex.wordpress.org/Function_Reference/remove_post_type_support
 * @see https://core.trac.wordpress.org/ticket/23932
 * @see https://codex.wordpress.org/Function_Reference/get_attached_file
 * @see attachment.php
 * @see wp-admin/includes/media.php
 * @see wp-includes/media-template.php
 */
function wpdtrt_exif_attachment_field_time( $form_fields, $post ) {

	// NOTE:
	// $post->ID // object reference (@param type)
	// $post['ID'] // array reference

	// this will also return the attachment geotag if it is available
	$attachment_metadata = $this->get_attachment_metadata( $post->ID );

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
			'helps' => '',
		);

  		return $form_fields;
  	}
}

add_filter( 'attachment_fields_to_edit', 'wpdtrt_exif_attachment_field_time', 10, 2 );

/**
 * Save value of Time field in media uploader, for Time dependent functions (map, weather)
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */
function wpdtrt_exif_attachment_field_time_save( $post, $attachment ) {
	if ( isset( $attachment['wpdtrt-exif-time'] ) ) {
		update_post_meta( $post['ID'], 'wpdtrt_exif_attachment_time', $attachment['wpdtrt-exif-time'] );
	}

	return $post;
}

add_filter( 'attachment_fields_to_save', 'wpdtrt_exif_attachment_field_time_save', 10, 2 );

?>