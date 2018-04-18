<?php
/**
 * Plugin sub class.
 *
 * @package     wpdtrt_exif
 * @version 	0.0.1
 * @since       0.7.5
 */

/**
 * Plugin sub class.
 *
 * Extends the base class to inherit boilerplate functionality.
 * Adds application-specific methods.
 *
 * @version 	0.0.1
 * @since       0.7.5
 */
class WPDTRT_Exif_Plugin extends DoTheRightThing\WPPlugin\Plugin {

    /**
     * Hook the plugin in to WordPress
     * This constructor automatically initialises the object's properties
     * when it is instantiated,
     * using new WPDTRT_Weather_Plugin
     *
     * @param     array $settings Plugin options
     *
	 * @version 	0.0.1
     * @since       0.7.5
     */
    function __construct( $settings ) {

    	// add any initialisation specific to wpdtrt-exif here

		// Instantiate the parent object
		parent::__construct( $settings );
    }

    //// START WORDPRESS INTEGRATION \\\\

    /**
     * Initialise plugin options ONCE.
     *
     * @param array $default_options
     *
     * @version     0.0.1
     * @since       0.7.5
     */
    protected function wp_setup() {

    	parent::wp_setup();

		// add actions and filters here
        add_filter( 'wp_read_image_metadata', 'wpdtrt_exif_read_image_geodata', '', 3 );
    }

    //// END WORDPRESS INTEGRATION \\\\

    //// START SETTERS AND GETTERS \\\\

    /**
     * Get metadata from attachment, including geotag
     *
     * @param $attachment_id
     * @return array $attachment_metadata
     * @uses http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
     */
    function wpdtrt_exif_get_attachment_metadata( $attachment_id ) {

        include_once( ABSPATH . 'wp-admin/includes/image.php' ); // access wp_read_image_metadata

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
     * Get geotag from attachment metadata
     * @param $attachment_metadata
     * @param $format
     * @return array ($latitude, $longitude)
     * @todo https://github.com/dotherightthing/wpdtrt-exif/issues/3
     */
    function wpdtrt_exif_get_attachment_metadata_gps( $attachment_metadata, $format ) {
        $lat_out = null;
        $lng_out = null;
        $latitude = $attachment_metadata['image_meta']['latitude'];
        $longitude = $attachment_metadata['image_meta']['longitude'];
        $lat = wpdtrt_exif_convert_dms_to_dd( $latitude );
        $lng = wpdtrt_exif_convert_dms_to_dd( $longitude );
        //wpdtrt_exif_convert_test( $latitude );
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
            // geo_pretty_fracs2dec() missing
            //else if ( $format === 'text' ) {
            //  $lat_out = ( geo_pretty_fracs2dec($latitude). $lat_ref );
            //  $lng_out = ( geo_pretty_fracs2dec($longitude) . $lng_ref );
            //}
        }
        else {
            $user_gps = wpdtrt_exif_get_user_gps();
            if ( $format === 'number' ) {
            $lat_out = $user_gps['latitude'];
            $lng_out = $user_gps['longitude'];
            }
        }
        return array(
            'latitude' => $lat_out,
            'longitude' => $lng_out
        );
    }

    /**
     * Get geotag from user edited custom field
     *  This provides a fallback if there is no GPS metadata stored with the image.
     *
     * @return array ($latitude, $longitude)
     *
     * @todo This should then be stored with the image, but it needs to be converted
     * @todo https://github.com/dotherightthing/wpdtrt-exif/issues/2
     * @todo rename wpdtrt_exif_attachment_geotag to use my 'cf' naming convention
     */
    function wpdtrt_exif_get_user_gps() {
        global $post;
        // TODO: $post->ID is throwing error in edit screen
        $user_gps = get_post_meta( $post->ID, 'wpdtrt_exif_attachment_geotag', true );

        if ( isset($user_gps) && ( strpos($user_gps, ',') !== false ) ) {
            $user_gps = explode(',', $user_gps);
            $latitude = $user_gps[0];
            $longitude = $user_gps[1];
        }
        else {
            $latitude = null;
            $longitude = null;
        }

        return array(
            'latitude' => $latitude,
            'longitude' => $longitude
        );
    }

    //// END SETTERS AND GETTERS \\\\

    //// START CONVERTORS \\\\

    /**
     * Convert from Degrees Minutes Seconds fractions, to Decimal Degrees for Google Maps
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
        $latitude_dd_1 =        wpdtrt_exif_convert_dms_to_dd( $latitude_dms_fr_1 );
        $latitude_dms_fr_2 =    wpdtrt_exif_convert_dd_to_dms( $latitude_dd_1 );
        //$latitude_dd_1 =      wpdtrt_exif_convert_dms_to_dd( $latitude_dms_fr_2 );
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

    //// END CONVERTORS \\\\

    //// START RENDERERS \\\\
    //// END RENDERERS \\\\

    //// START FILTERS \\\\

    /**
     * Get metadata from image
     *
     * Supplement the core function wp_read_image_metadata
     * to also return the GPS location data which WP usually ignores
     *
     * Added false values to prevent this function running over and over
     * if the image was taken with a non-geotagging camera
     *
     * @example
     *  include_once( ABSPATH . 'wp-admin/includes/image.php' ); // access wp_read_image_metadata
     *  add_filter('wp_read_image_metadata', 'wpdtrt_exif_read_image_geodata','',3);
     *
     * @see http://kristarella.blog/2009/04/add-image-exif-metadata-to-wordpress/
     * @uses wp-admin/includes/image.php
     *
     * @todo Pull geotag from wpdtrt_exif_attachment_geotag if it is not available in the image.
     *  This requires resolving the conversion issue https://github.com/dotherightthing/wpdtrt-exif/issues/2
     */
    function wpdtrt_exif_read_image_geodata( $meta, $file, $sourceImageType ) {
        // the filtered function also runs exif_read_data
        // but the value is not accessible to the function.
        // note: @ suppresses any error messages that might be generated by the prefixed expression

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

    //// END FILTERS \\\\

    //// START HELPERS \\\\
    //// END HELPERS \\\\
}

?>