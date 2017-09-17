/**
 * Scripts for the public front-end
 *
 * This file contains JavaScript.
 *    PHP variables are provided in wpdtrt_exif_config.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-exif
 * @since       0.1.0
 *
 * @package     WPDTRT_EXIF
 * @subpackage  WPDTRT_EXIF/js
 */

jQuery(document).ready(function($){

	$('.wpdtrt-exif-badge').hover(function() {
		$(this).find('.wpdtrt-exif-badge-info').stop(true, true).fadeIn(200);
	}, function() {
		$(this).find('.wpdtrt-exif-badge-info').stop(true, true).fadeOut(200);
	});

  $.post( wpdtrt_exif_config.ajax_url, {
    action: 'wpdtrt_exif_data_refresh'
  }, function( response ) {
    //console.log( 'Ajax complete' );
  });

});
