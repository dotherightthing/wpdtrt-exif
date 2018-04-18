/**
 * Scripts for the public front-end
 *
 * PHP variables are provided in wpdtrt_exif_config.
 *
 * @version 	0.0.1
 * @since       0.7.5
 */

jQuery(document).ready(function($){

	var config = wpdtrt_exif_config;

	$.post( wpdtrt_exif_config.ajax_url, {
		action: 'wpdtrt_exif_data_refresh'
	}, function( response ) {
		//console.log( 'Ajax complete' );
	});
});
