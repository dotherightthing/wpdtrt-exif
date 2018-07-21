<?php
/**
 * Add Heading above attachment fields
 *
 * This file contains PHP.
 *
 * @since       1.1.0
 * @see http://www.billerickson.net/wordpress-add-custom-fields-media-gallery/
 * @package     WPDTRT_Exif
 */

/**
 * Add read-only Heading 'field' to media uploader
 *
 * @param array  $form_fields Fields to include in attachment form.
 * @param object $post Attachment record in database.
 * @return $form_fields Modified form fields
 */
function wpdtrt_exif_attachment_field_heading( $form_fields, $post ) {
	$form_fields['wpdtrt-exif-heading'] = array(
		'label' => '<h2>WPDTRT EXIF</h2>',
		'input' => 'html',
		'html'  => '<span></span>',
	);

	return $form_fields;
}
