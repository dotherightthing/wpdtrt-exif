<?php
/**
 * Unit tests, using PHPUnit, wp-cli, WP_UnitTestCase
 *
 * The plugin is 'active' within a WP test environment
 * so the plugin class has already been instantiated
 * with the options set in wpdtrt-gallery.php
 *
 * Only function names prepended with test_ are run.
 * $debug logs are output with the test output in Terminal
 * A failed assertion may obscure other failed assertions in the same test.
 *
 * @package     WPDTRT_Exif
 * @version     0.0.1
 * @since       0.7.5 DTRT WordPress Plugin Boilerplate Generator
 * @see http://kb.dotherightthing.dan/php/wordpress/php-unit-testing-revisited/ - Links
 * @see http://richardsweeney.com/testing-integrations/
 * @see https://gist.github.com/benlk/d1ac0240ec7c44abd393 - Collection of notes on WP_UnitTestCase
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes/factory.php
 * @see https://core.trac.wordpress.org/browser/trunk/tests/phpunit/includes//factory/
 * @see https://stackoverflow.com/questions/35442512/how-to-use-wp-unittestcase-go-to-to-simulate-current-pageclass-wp-unittest-factory-for-term.php
 * @see https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories
 */

/**
 * WP_UnitTestCase unit tests for wpdtrt_exif
 */
class WPDTRT_ExifTest extends WP_UnitTestCase {

	/**
	 * Compare two HTML fragments.
	 *
	 * @param string $expected Expected value.
	 * @param string $actual Actual value.
	 * @param string $error_message Message to show when strings don't match.
	 * @uses https://stackoverflow.com/a/26727310/6850747
	 */
	protected function assertEqualHtml( $expected, $actual, $error_message ) {
		$from = [ '/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s', '/> </s' ];
		$to   = [ '>', '<', '\\1', '><' ];
		$this->assertEquals(
			preg_replace( $from, $to, $expected ),
			preg_replace( $from, $to, $actual ),
			$error_message
		);
	}

	/**
	 * Delete image sizes generated by WP
	 *
	 * @see https://gistpages.com/posts/php_delete_files_with_unlink
	 */
	public function delete_sized_images() {
		array_map( 'unlink', glob( 'tests/data/test1-*.jpg' ) );
		array_map( 'unlink', glob( 'tests/data/test2-*.jpg' ) );
	}

	/**
	 * SetUp
	 * Automatically called by PHPUnit before each test method is run
	 */
	public function setUp() {
		// Make the factory objects available.
		parent::setUp();

		// Generate WordPress data fixtures.
		$this->post_id_1 = $this->create_post( array(
			'post_title'   => 'DTRT EXIF test 1',
			'post_content' => 'This is a simple test',
		));

		$this->post_id_2 = $this->create_post( array(
			'post_title'   => 'DTRT EXIF test 2',
			'post_content' => 'This is a simple test',
		));

		// Attachment (for testing custom sizes and meta).
		$this->attachment_id_1 = $this->create_attachment( array(
			'filename'       => 'tests/data/test1.jpg',
			'parent_post_id' => $this->post_id_1,
		));

		$this->attachment_id_2 = $this->create_attachment( array(
			'filename'       => 'tests/data/test2.jpg',
			'parent_post_id' => $this->post_id_2,
		));

		// tests/data/test2.jpg.
		$this->attachment_2_meta = array(
			'aperture'          => '2.4',
			'credit'            => '',
			'camera'            => 'iPhone 5',
			'caption'           => '',
			'created_timestamp' => '1442664479',
			'copyright'         => '',
			'focal_length'      => '4.12',
			'iso'               => '50',
			'shutter_speed'     => '0.0001469939732471',
			'title'             => '',
			'orientation'       => '1',
			'keywords'          => array(),
			'latitude'          => array(
				'52/1',
				'50/1',
				'1019/100', // 2dp - actual 10.188 (3dp) but this alone is not the cause.
			),
			'latitude_ref'      => 'N',
			'longitude'         => array(
				'106/1',
				'30/1',
				'3164/100', // 2dp - actual 31.638 (3dp) but this alone is not the cause.
			),
			'longitude_ref'     => 'E',
		);

		/*
		// Attachment (for testing custom sizes and meta)
		$this->attachment_id_3 = $this->create_attachment( array(
		'filename' => 'tests/data/23465055912_ce8ff02e9f_o_Saihan_Tal.jpg',
		'parent_post_id' => $this->post_id_1
		) );

		// Attachment (for testing custom sizes and meta)
		$this->attachment_id_4 = $this->create_attachment( array(
		'filename' => 'tests/data/MDM_20151206_155919_20151206_1559_Outer_Mongolia.jpg',
		'parent_post_id' => $this->post_id_1
		) );
		*/
	}

	/**
	 * TearDown
	 * Automatically called by PHPUnit after each test method is run
	 *
	 * @see https://codesymphony.co/writing-wordpress-plugin-unit-tests/#object-factories
	 */
	public function tearDown() {

		parent::tearDown();

		wp_delete_post( $this->post_id_1, true );
		wp_delete_post( $this->attachment_id_1, true );
		wp_delete_post( $this->attachment_id_2, true );

		$this->delete_sized_images();
	}

	/**
	 * Create post
	 *
	 * @param array $options Post options.
	 * @return number $post_id
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
	 * @see https://wordpress.stackexchange.com/questions/37163/proper-formatting-of-post-date-for-wp-insert-post
	 * @see https://codex.wordpress.org/Function_Reference/wp_update_post
	 */
	public function create_post( $options ) {

		$post_title   = null;
		$post_date    = null;
		$post_content = null;

		extract( $options, EXTR_IF_EXISTS );

		$post_id = $this->factory->post->create([
			'post_title'   => $post_title,
			'post_date'    => $post_date,
			'post_content' => $post_content,
			'post_type'    => 'post',
			'post_status'  => 'publish',
		]);

		return $post_id;
	}

	/**
	 * Create attachment, upload media file, generate sizes
	 *
	 * @param array $options Options.
	 * @see http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php
	 * @see https://core.trac.wordpress.org/ticket/42990 - Awaiting Review
	 * @todo Factory method not available - see create_attachment(), below
	 */
	public function create_attachment_simple( $options ) {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		$attachment_id = $this->factory->attachment->create_upload_object([
			'file'   => $filename,
			'parent' => $parent_post_id,
		]);
	}

	/**
	 * Create attachment and attach it to a post
	 *  Note: this doesn't actually appear to copy the image to the uploads folder
	 *  this is problematic for checking that the file_exists
	 *  as used by wp_read_image_metadata()
	 *
	 * @param array $options Options.
	 * @return number $attachment_id
	 * @see https://developer.wordpress.org/reference/functions/wp_insert_attachment/
	 * @see http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/factory/class-wp-unittest-factory-for-attachment.php
	 */
	public function create_attachment( $options ) {

		$filename       = null;
		$parent_post_id = null;

		extract( $options, EXTR_IF_EXISTS );

		// Check the type of file. We'll use this as the 'post_mime_type'.
		$filetype = wp_check_filetype( basename( $filename ), null );

		// Get the path to the upload directory.
		$wp_upload_dir = wp_upload_dir();

		// Create the attachment from an array of post data.
		$attachment_id = $this->factory->attachment->create([
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_parent'    => $parent_post_id, // test factory only.
			'file'           => $filename, // test factory only.
		]);

		// generate image sizes
		// @see https://wordpress.stackexchange.com/a/134252.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * ===== Tests =====
	 */

	/**
	 * Test that the correct 'upload' location is used by create_attachment()
	 * to debug wp_read_image_metadata(), via $plugin->get_image_metadata()
	 *
	 * @todo Test only works on local dev
	 */
	public function __test_upload_dir() {

		$wp_upload_dir = wp_upload_dir();

		$this->assertSame(
			array(
				'path'    => '/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress//wp-content/uploads/2018/04',
				'url'     => 'http://example.org/wp-content/uploads/2018/04',
				'subdir'  => '/2018/04',
				'basedir' => '/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress//wp-content/uploads',
				'baseurl' => 'http://example.org/wp-content/uploads',
				'error'   => false,
			),
			$wp_upload_dir
		);

		$this->assertSame(
			'http://example.org/wp-content/uploads/2018/04',
			$wp_upload_dir['url']
		);
	}

	/**
	 * Test that querying empty attachment fields gives the expected results
	 */
	public function __test_empty_attachment_fields() {

		global $wpdtrt_exif_plugin;

		$this->go_to(
			get_post_permalink( $this->post_id_1 )
		);

		/*
		// fails
		$this->assertEquals(
		array(
		'latitude' => null,
		'longitude' => null
		),
		$wpdtrt_exif_plugin->get_user_gps(),
		'Expected no user GPS data, if GPS not manually entered'
		);
		*/
	}

	/**
	 * Test that querying populated attachment fields gives the expected results
	 */
	public function test_attachment_fields() {

		global $wpdtrt_exif_plugin;
		$test1jpg_location = '40.0798614,116.6009234';
		update_post_meta( $this->attachment_id_1, 'wpdtrt_exif_attachment_gps', $test1jpg_location );

		$this->go_to(
			get_post_permalink( $this->post_id_1 )
		);

		$this->assertNotEquals(
			array(
				'latitude'  => null,
				'longitude' => null,
			),
			$wpdtrt_exif_plugin->get_user_gps( get_post( $this->attachment_id_1 ) ),
			'Expected user GPS data, if GPS manually entered'
		);
	}

	/**
	 * Test that meta data can be pulled from the attachment image using the WordPress API.
	 * Note: Test images exceed the width/height big_image threshhold of 2560px and so are renamed to -scaled
	 * See: https://make.wordpress.org/core/2019/10/09/introducing-handling-of-big-images-in-wordpress-5-3/
	 */
	public function test_core_meta_retrieval() {

		// images are uploaded to
		// /var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress//wp-content/uploads/
		// + real_relative_path
		//
		// ok.
		$this->assertContains(
			'wp-content/uploads/tests/data/test-scaled.jpg',
			get_attached_file( $this->attachment_id_1 ),
			'Attachment 1 should exist'
		);

		// ok.
		$this->assertContains(
			'wp-content/uploads/tests/data/test2-scaled.jpg',
			get_attached_file( $this->attachment_id_2 ),
			'Attachment 2 should exist'
		);

		/*
		// ok - disabled as this path can change

		$this->assertEquals(
		'/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress//wp-content/uploads/tests/data/test2-scaled.jpg',
		get_attached_file( $this->attachment_id_2 ),
		'Attachment should have this file path'
		);
		*/

		// ok.
		$this->assertTrue(
			function_exists( 'wp_read_image_metadata' ),
			'Function should exist'
		);

		// ok.
		$this->assertEquals(
			$this->attachment_2_meta,
			wp_read_image_metadata( 'tests/data/test2-scaled.jpg' ),
			'Image metadata should exist'
		);

		// ok.
		$this->assertTrue(
			file_exists( 'tests/data/test2-scaled.jpg' ),
			'Real file should exist'
		);

		/*
			// fails - #10

			$this->assertTrue(
			file_exists( get_attached_file( $this->attachment_id_2 ) ),
			'Virtual file should exist'
		);
		*/

		/*
			// passes if image manually copied over

			$this->assertFileExists(
			'/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress/wp-content/uploads/2018/04/test1-scaled.jpg',
			'file does not exist 1'
		);
		*/

		/*
		$this->assertFileExists(
			'/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress/wp-content/uploads/2018/04/tests/data/test1-scaled.jpg',
			'file does not exist 2'
		);
		*/

		/*
		$this->assertFileExists(
			'/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress/wp-content/uploads/tests/data/test1-scaled.jpg',
			'file does not exist 3'
		);
		*/

		/*
		// passes with bogus path, image NOT manually copied over nor actually in file system at this location

		$this->assertSame(
			'/var/folders/0y/31dr5mx52c98lmldc_zpw3w00000gn/T/wordpress//wp-content/uploads/tests/data/test1-scaled.jpg',
			get_attached_file( $this->attachment_id_1 ),
			'file does not exist 4'
		);
		*/
	}

	/**
	 * Test that meta data can be pulled from the attachment image
	 *  using the plugin's methods
	 */
	public function test_plugin_meta_retrieval() {

		global $wpdtrt_exif_plugin;

		$attachment_metadata = $wpdtrt_exif_plugin->get_attachment_metadata( $this->attachment_id_2 );
		$image_metadata      = $wpdtrt_exif_plugin->get_image_metadata( $this->attachment_id_2 );

		$this->assertArrayHasKey(
			'image_meta',
			$attachment_metadata,
			'Attachment meta should include image meta'
		);

		/*
			// fails

			$this->assertNotNull(
			$attachment_metadata['image_meta']['latitude'],
			'Attachment meta should include the GPS latitude'
		);
		*/

		/*
		// fails - #10

		$this->assertEquals(
			$this->attachment_2_meta,
			$image_metadata,
			'Image metadata should exist'
		);
		*/

		/*
		$this->assertSame(
		array(),
			$wpdtrt_exif_plugin->update_attachment_metadata( $this->attachment_id_1 ),
			'image metadata is ?'
		);
		*/
	}

	/**
	 * Test DMS to decimal conversion
	 */
	public function test_meta_conversion() {

		global $wpdtrt_exif_plugin;

		$this->assertEquals(
			52.83616388888889, // tests/data/test2-scaled.jpg.
			$wpdtrt_exif_plugin->helper_convert_dms_to_dd(
				$this->attachment_2_meta['latitude'],
				$this->attachment_2_meta['latitude_ref']
			),
			'1 Latitude should be converted from degrees-decimal to degrees-minutes-seconds'
		);

		$this->assertEquals(
			106.5087888888888, // tests/data/test2-scaled.jpg.
			$wpdtrt_exif_plugin->helper_convert_dms_to_dd(
				$this->attachment_2_meta['longitude'],
				$this->attachment_2_meta['longitude_ref']
			),
			'2 Longitude should be converted from degrees-decimal to degrees-minutes-seconds'
		);
	}
}
