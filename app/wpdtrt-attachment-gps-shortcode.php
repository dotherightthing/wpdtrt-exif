<?php
/**
 * Generate a shortcode, to embed the widget inside a content area.
 *
 * This file contains PHP.
 *
 * @link        https://github.com/dotherightthing/wpdtrt-attachment-gps
 * @link        https://generatewp.com/shortcodes/
 * @since       0.1.0
 *
 * @example     [wpdtrt_attachment_gps number="4" enlargement="yes"]
 * @example     do_shortcode( '[wpdtrt_attachment_gps number="4" enlargement="yes"]' );
 *
 * @package     WPDTRT_Attachment_GPS
 * @subpackage  WPDTRT_Attachment_GPS/app
 */

if ( !function_exists( 'wpdtrt_attachment_gps_shortcode' ) ) {

  /**
   * @param       array $atts
   *    Optional shortcode attributes specified by the user
   * @param       string $content
   *    Content within the enclosing shortcode tags
   *
   * @since       0.1.0
   * @uses        ../../../../wp-includes/shortcodes.php
   * @see         https://codex.wordpress.org/Function_Reference/add_shortcode
   * @see         https://codex.wordpress.org/Shortcode_API#Enclosing_vs_self-closing_shortcodes
   * @see         http://php.net/manual/en/function.ob-start.php
   * @see         http://php.net/manual/en/function.ob-get-clean.php
   */
  function wpdtrt_attachment_gps_shortcode( $atts, $content = null ) {

    // post object to get info about the post in which the shortcode appears
    global $post;

    // predeclare variables
    $before_widget = null;
    $before_title = null;
    $title = null;
    $after_title = null;
    $after_widget = null;
    $number = null;
    $enlargement = null;
    $shortcode = 'wpdtrt_attachment_gps_shortcode';

    /**
     * Combine user attributes with known attributes and fill in defaults when needed.
     * @see https://developer.wordpress.org/reference/functions/shortcode_atts/
     */
    $atts = shortcode_atts(
      array(
        'number' => '4',
        'enlargement' => 'yes'
      ),
      $atts,
      $shortcode
    );

    // only overwrite predeclared variables
    extract( $atts, EXTR_IF_EXISTS );

    if ( $enlargement === 'yes') {
      $enlargement = '1';
    }

    if ( $enlargement === 'no') {
      $enlargement = '0';
    }

    $wpdtrt_attachment_gps_options = get_option('wpdtrt_attachment_gps');
    $wpdtrt_attachment_gps_data = $wpdtrt_attachment_gps_options['wpdtrt_attachment_gps_data'];

    /**
     * ob_start — Turn on output buffering
     * This stores the HTML template in the buffer
     * so that it can be output into the content
     * rather than at the top of the page.
     */
    ob_start();

    require(WPDTRT_ATTACHMENT_GPS_PATH . 'templates/wpdtrt-attachment-gps-front-end.php');

    /**
     * ob_get_clean — Get current buffer contents and delete current output buffer
     */
    $content = ob_get_clean();

    return $content;
  }

  /**
   * @param string $tag
   *    Shortcode tag to be searched in post content.
   * @param callable $func
   *    Hook to run when shortcode is found.
   */
  add_shortcode( 'wpdtrt_attachment_gps', 'wpdtrt_attachment_gps_shortcode' );

}

?>
