<?php
/**
 * Plugin sub class.
 *
 * @package WPDTRT_Exif
 * @since   0.7.16 DTRT WordPress Plugin Boilerplate Generator
 */

/**
 * Extend the base class to inherit boilerplate functionality.
 * Adds application-specific methods.
 *
 * @since   1.0.0
 */
class WPDTRT_Exif_Plugin extends DoTheRightThing\WPDTRT_Plugin_Boilerplate\r_1_4_39\Plugin {

	/**
	 * Supplement plugin initialisation.
	 *
	 * @param     array $options Plugin options.
	 * @since     1.0.0
	 * @version   1.1.0
	 */
	function __construct( $options ) {

		// edit here.

		parent::__construct( $options );
	}

	/**
	 * ====== WordPress Integration ======
	 */

	/**
	 * Supplement plugin's WordPress setup.
	 * Note: Default priority is 10. A higher priority runs later.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference Action order
	 */
	protected function wp_setup() {

		// edit here.

		parent::wp_setup();

		// add actions and filters here
		add_filter( 'wp_read_image_metadata', array( $this, 'filter_read_image_geodata' ), '', 3 );
	}

	/**
	 * ====== Getters and Setters ======
	 */

	/**
	 * Get metadata from attachment, including geotag
	 *
	 * @param $attachment_id
	 * @return array $attachment_metadata
	 * @uses http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
	 */
	public function get_attachment_metadata( $attachment_id ) {

		// reinstate attachment metadata accidentally deleted during development:
		// $attach_data = wp_generate_attachment_metadata( $id, get_attached_file( $id ) );
		// wp_update_attachment_metadata( $id, $attach_data );
		// get the core metadata stored with the image post
		$attachment_metadata = wp_get_attachment_metadata( $attachment_id, false );

		// if the core metadata doesn't include a GPS location
		// then this wasn't stored when the image was uploaded into WP
		// (i.e. it was uploaded before this function was written)
		// so reprocess the image
		if ( ! array_key_exists( 'latitude', $attachment_metadata ) || ! array_key_exists( 'longitude', $attachment_metadata ) ) {

			$attachment_metadata = $this->update_attachment_metadata( $attachment_id, $attachment_metadata );
		}

		return $attachment_metadata;
	}

	/**
	 * Get metadata from attachment, including geotag
	 *
	 * @param int $attachment_id Attachment ID
	 * @return array $image_metadata
	 */
	public function get_image_metadata( $attachment_id ) {

		$file = get_attached_file( $attachment_id ); // full path

		// read metadata, including the GPS metadata requested by our filter filter_read_image_geodata
		// this includes running exif_read_data()
		$image_metadata = wp_read_image_metadata( $file );

		return $image_metadata;
	}

	/**
	 * Add image metadata to the attachment metadata stored in WordPress
	 *
	 * @param int $attachment_id Attachment ID
	 * @param mixed $attachment_metadata Attachment meta field
	 * @return array $merged_attachment_metadata
	 */
	public function update_attachment_metadata( $attachment_id, $attachment_metadata ) {

		// TODO: check for false values
		// and replace with the value of the custom field if it has been supplied
		// the metadata update is destructive
		// so merge the existing metadata with the metadata which we've just read from the image
		$attachment_metadata_updated               = $attachment_metadata;
		$attachment_metadata_updated['image_meta'] = $this->get_image_metadata( $attachment_id );

		// write the updated metadata to WP's database
		// note that the actual image EXIF metadata is not changed
		// i.e. this is not wp_write_image_metadata
		wp_update_attachment_metadata( $attachment_id, $attachment_metadata_updated );

		// read the updated metadata
		// TODO: is this redundant?
		return wp_get_attachment_metadata( $attachment_id, false );
	}

	/**
	 * Get geotag from attachment metadata
	 *  (partially ex get_geo_exif in twentysixteenchild-dontbelievethehype/includes/attachment-geolocation.php)
	 *
	 * @param mixed $attachment_metadata Attachment meta field
	 * @param string $format Format
	 * @param object $post Post
	 * @return array ($latitude, $longitude)
	 * @todo https://github.com/dotherightthing/wpdtrt-exif/issues/3
	 */
	public function get_attachment_metadata_gps( $attachment_metadata, $format, $post ) {

		$lat_out = null;
		$lng_out = null;

		if ( ! isset( $attachment_metadata['image_meta']['latitude'], $attachment_metadata['image_meta']['longitude'] ) ) {
			return array();
		}

		$latitude      = $attachment_metadata['image_meta']['latitude'];
		$longitude     = $attachment_metadata['image_meta']['longitude'];
		$latitude_ref  = $attachment_metadata['image_meta']['latitude_ref'];
		$longitude_ref = $attachment_metadata['image_meta']['longitude_ref'];
		$lat           = $this->helper_convert_dms_to_dd( $latitude, $latitude_ref );
		$lng           = $this->helper_convert_dms_to_dd( $longitude, $longitude_ref );
		$neg_lat       = '';
		$neg_lng       = '';

		if ( $lat < 0 ) {
			$neg_lat = '-';
		}

		if ( $lng < 0 ) {
			$neg_lng = '-';
		}

		if ( 0 !== $latitude && 0 !== $longitude ) {
			if ( 'number' === $format ) {
				// full decimal latitude and longitude for Google Maps
				$lat_out = ( $neg_lat . number_format( $lat, 6 ) );
				$lng_out = ( $neg_lng . number_format( $lng, 6 ) );

			} elseif ( 'text' === $format ) {

				// text based latitude and longitude for Alternative text
				$lat_out = $this->helper_convert_dms_to_dd_pretty( $latitude, $latitude_ref );
				$lng_out = $this->helper_convert_dms_to_dd_pretty( $longitude, $longitude_ref );
			}
		} else {
			$user_gps = $this->get_user_gps( $post );

			if ( 'number' === $format ) {
				$lat_out = $user_gps['latitude'];
				$lng_out = $user_gps['longitude'];
			}
			// TODO: do we need an ALT TEXT version here?
		}

		return array(
			'latitude'  => $lat_out,
			'longitude' => $lng_out,
		);
	}

	/**
	 * Get geotag from user edited custom field
	 *  This provides a fallback if there is no GPS metadata stored with the image.
	 *
	 * @param object $post Post
	 * @return array ($latitude, $longitude)
	 * @todo This should then be stored with the image, but it needs to be converted
	 * @todo https://github.com/dotherightthing/wpdtrt-exif/issues/2
	 * @todo rename wpdtrt_exif_attachment_gps to use my 'cf' naming convention
	 */
	public function get_user_gps( $post ) {

		$user_gps = get_post_meta( $post->ID, 'wpdtrt_exif_attachment_gps', true );

		if ( isset( $user_gps ) && ( strpos( $user_gps, ',' ) !== false ) ) {
			$user_gps  = explode( ',', $user_gps );
			$latitude  = $user_gps[0];
			$longitude = $user_gps[1];
		} else {
			$latitude  = null;
			$longitude = null;
		}

		return array(
			'latitude'  => $latitude,
			'longitude' => $longitude,
		);
	}

	/**
	 * ===== Renderers =====
	 */

	/**
	 * ===== Filters =====
	 */

	/**
	 * Get metadata from image
	 *  (ex add_geo_exif in twentysixteenchild-dontbelievethehype/includes/attachment-geolocation.php)
	 *
	 * Supplement the core function wp_read_image_metadata
	 * to also return the GPS location data which WP usually ignores
	 *
	 * Added false values to prevent this function running over and over
	 * if the image was taken with a non-geotagging camera
	 *
	 * @param array $meta Image meta data.
     * @param string $file Path to image file.
     * @param int $source_image_type Type of image.
	 * @see http://kristarella.blog/2009/04/add-image-exif-metadata-to-wordpress/
	 * @uses wp-admin/includes/image.php
	 * @todo Pull geotag from wpdtrt_exif_attachment_gps if it is not available in the image.
	 *  This requires resolving the conversion issue https://github.com/dotherightthing/wpdtrt-exif/issues/2
	 */
	function filter_read_image_geodata( $meta, $file, $source_image_type ) {
		// the filtered function also runs exif_read_data
		// but the value is not accessible to the function.
		// note: @ suppresses any error messages that might be generated by the prefixed expression

		$exif = @exif_read_data( $file );

		if ( ! empty( $exif['GPSLatitude'] ) ) {
			$meta['latitude'] = $exif['GPSLatitude'];
		} else {
			$meta['latitude'] = false;
		}

		if ( ! empty( $exif['GPSLatitudeRef'] ) ) {
			$meta['latitude_ref'] = trim( $exif['GPSLatitudeRef'] );
		} else {
			$meta['latitude_ref'] = false;
		}

		if ( ! empty( $exif['GPSLongitude'] ) ) {
			$meta['longitude'] = $exif['GPSLongitude'];
		} else {
			$meta['longitude'] = false;
		}

		if ( ! empty( $exif['GPSLongitudeRef'] ) ) {
			$meta['longitude_ref'] = trim( $exif['GPSLongitudeRef'] );
		} else {
			$meta['longitude_ref'] = false;
		}

		return $meta;
	}

	/**
	 * ===== Helpers =====
	 */

	/**
	 * Convert fraction to decimal, format to be human readable
	 *  (ex twentysixteenchild-dontbelievethehype/includes/attachment-geolocation.php)
	 *
	 * @param array $dms_fractions array( Degrees/1, Minutes/1, Seconds/100 )
	 * @param string $axis_ref N|S|E|W
	 * @return string $decimal_degrees_pretty
	 */
	public function helper_convert_dms_to_dd_pretty( $dms_fractions, $axis_ref ) {

		$d = $this->helper_convert_dms_fraction_to_number( $dms_fractions[0] );
		$m = $this->helper_convert_dms_fraction_to_number( $dms_fractions[1] );
		$s = $this->helper_convert_dms_fraction_to_number( $dms_fractions[2] );

		$decimal_degrees_pretty = ( $d . '&deg; ' . $m . '&prime; ' . $s . '&Prime; ' . $axis_ref );

		return $decimal_degrees_pretty;
	}

	/**
	 * Convert Degrees Minutes Seconds fractions, to Decimal Degrees for Google Maps
	 * This is called once for latitude, and once for longitude.
	 *
	 * @param array $dms_fractions array( Degrees/1, Minutes/1, Seconds/100 )
	 * @param string $axis_ref N|S|E|W
	 * @return string $decimal_degrees
	 * @see http://kristarella.blog/2008/12/geo-exif-data-in-wordpress/
	 * @see https://tmackinnon.com/converting-decimal-degrees-to-degrees-minutes-seconds.php
     * @see http://www.leancrew.com/all-this/2014/07/extracting-coordinates-from-apple-maps/ Apple Maps to Mail = 52.836163,106.508788
     * @see https://www.fcc.gov/media/radio/dms-decimal FCC (JS Page) = 52.836164, 106.508789
     * @see https://github.com/prairiewest/PHPconvertDMSToDecimal PHPconvertDMSToDecimal (Github PHP) = 52.83616388888889, 106.50878888888889
     * @see https://www.web-max.ca/PHP/misc_6.php WebMax (PHP Page) = 52.83616388888889, 106.50878888888889
     * @see https://www.latlong.net/degrees-minutes-seconds-to-decimal-degrees LatLng (PHP? Page, expects a rounded longitudinal second value) = 52.83616389,106.50888889
	 */
	public function helper_convert_dms_to_dd( $dms_fractions, $axis_ref ) {

		$d = $this->helper_convert_dms_fraction_to_number( $dms_fractions[0] );
		$m = $this->helper_convert_dms_fraction_to_number( $dms_fractions[1] );
		$s = $this->helper_convert_dms_fraction_to_number( $dms_fractions[2] );

		$decimal_degrees = convertDMSToDecimal( $axis_ref . ' ' . $d . ' ' . $m . ' ' . $s );

		return $decimal_degrees;
	}

	/**
	 * Convert Degrees Minutes Seconds fraction string to decimal number
	 *  (ex. wpdtrt-exif-archive.php)
	 *  (ex geo_frac2dec in twentysixteenchild-dontbelievethehype/includes/attachment-geolocation.php)
	 *  Note: replaced by helper_convert_fraction_to_decimal()
	 *
	 * @param string $str Fraction string
	 * @return number $number Decimal number
	 */
	function helper_convert_dms_fraction_to_number( $str ) {

		$decimal = 0;

		@list( $n, $d ) = explode( '/', $str );

		if ( ! empty( $d ) ) {

			// convert fraction a/b into array(a,b)
			$decimal = $n / $d;
		}

		return $decimal;
	}

	/**
	 * Converts Decimal Degrees to Degrees Minutes Seconds
	 *
	 * @param $dd Decimal Degrees
	 * @return array($degrees, $minutes, $seconds)
	 * @see https://stackoverflow.com/a/7927527/6850747
	 * @uses https://www.web-max.ca/PHP/misc_6.php
	 * @todo Not generating the WP format yet
	 */
	public function helper_convert_dd_to_dms( $dd ) {
		// To avoid issues with floating
		// point math we extract the integer part and the float
		// part by using a string function.
		$vars   = explode( '.', $dd );
		$deg    = $vars[0];
		$tempma = '0.' . $vars[1];
		$tempma = $tempma * 3600;
		$min    = floor( $tempma / 60 );
		$sec    = $tempma - ( $min * 60 );

		return array(
			$deg,
			$min,
			$sec,
		);
	}
}
