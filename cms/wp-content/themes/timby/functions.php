<?php 

/**
 * Custom Post types
 */
require get_template_directory() . '/inc/custom-post-types.php';

/**
 * Custom User Roles
 */
require get_template_directory() . '/inc/custom-user-roles.php';

/**
 * Custom Taxonomies
 */
require get_template_directory() . '/inc/custom-taxonomies.php';


/**
 * Custom API logs page
 */
require get_template_directory() . '/inc/custom-api-logs-page/page.php';


if ( ! function_exists( 'timbyweb_setup' ) ) :
  /**
   * Sets up theme defaults and registers support for various WordPress features.
   *
   * Note that this function is hooked into the after_setup_theme hook, which runs
   * before the init hook. The init hook is too late for some features, such as indicating
   * support post thumbnails.
   */
  function timbyweb_setup() {

    /**
     * Make theme available for translation
     * Translations can be filed in the /languages/ directory
     * If you're building a theme based on timbyweb, use a find and replace
     * to change 'timbyweb' to the name of your theme in all the template files
     */
    load_theme_textdomain( 'timbyweb', get_template_directory() . '/languages' );

    /**
     * Add default posts and comments RSS feed links to head
     * Add post thumbnails
     */
    add_theme_support( 'automatic-feed-links', 'post-thumbnails' );

  }
endif; // timbyweb_setup
add_action( 'after_setup_theme', 'timbyweb_setup' );

/**
 * Initialize the metabox class.
 */
function cmb_initialize_cmb_meta_boxes() {
  if ( ! class_exists( 'cmb_Meta_Box' ) )
    require_once 'lib/metabox/init.php';
}
add_action( 'init', 'cmb_initialize_cmb_meta_boxes', 9999 );

/**
 * Custom Meta boxes
 */
require get_template_directory() . '/inc/custom-meta-boxes.php';


/**
 * When a report is published, 
 * Insert the report data into cartodb
 */
require get_template_directory().'/lib/cartodb/cartodb.class.php';
function report_pending_to_publish( $new_status, $old_status, $post ) {
  if ( $old_status == 'pending' && $new_status == 'publish' ) {
    if( $post->post_type == 'report')
    {
      $lnglat = array(
        'lng' => get_post_meta($post->ID, '_longitude', true ),
        'lat' => get_post_meta($post->ID, '_latitude', true ),
      );
      
      $cartodb =  new CartoDBClient(
        array(
          'key'       => 'jTIOqWUcpsQyfvQP46s09pcGcDXEn877qhgaN44C',
          'secret'    => 'VUX82GTIzm10o9NoptjJ5ksl73eO7miUbFi3M2t9',
          'email'     => 'kamweti@circle.co.ke',
          'password'  => 'P%)>zV:M&{f2K74',
          'subdomain' => 'kaam'
        )
      );


      // try inserting data into table
      $cartodb->insertRow(
        'reports', 
        array(
          'post_id' => "'".$post->ID."'",
          'the_geom' => "ST_SetSRID(ST_Point(".$lnglat['lng'].", ".$lnglat['lat']."),4326)"
        )
      );

    }
  }
}
add_action( 'transition_post_status', 'report_pending_to_publish', 10, 3 );