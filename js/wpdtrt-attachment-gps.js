/**
 * Scripts for the public front-end
 *
 * This file contains JavaScript.
 *    PHP variables are provided in wpdtrt_attachment_gps_config.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-attachment-gps
 * @since       0.1.0
 *
 * @package     WPDTRT_Attachment_GPS
 * @subpackage  WPDTRT_Attachment_GPS/js
 */

jQuery(document).ready(function($){

	$('.wpdtrt-attachment-gps-badge').hover(function() {
		$(this).find('.wpdtrt-attachment-gps-badge-info').stop(true, true).fadeIn(200);
	}, function() {
		$(this).find('.wpdtrt-attachment-gps-badge-info').stop(true, true).fadeOut(200);
	});

  $.post( wpdtrt_attachment_gps_config.ajax_url, {
    action: 'wpdtrt_attachment_gps_data_refresh'
  }, function( response ) {
    //console.log( 'Ajax complete' );
  });

});
