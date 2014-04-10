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


    /**
     * Create starter pages
     * if they don't already exist
     *
     * @todo: move this to its own function call, check if this page already exists, remove it once theme is switched
     */
    wp_insert_post(
      array(
        'post_name' => 'signin'
      )
    );

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
 * Add a custom schedule 'every 10mins'
 */
add_filter( 'cron_schedules', 'cron_add_every_ten_minutes' );
function cron_add_every_ten_minutes( $schedules ) {
  // Adds once weekly to the existing schedules.
  $schedules['every_ten_minutes'] = array(
    'interval' => 600,
    'display' => __( 'Every ten minutes' )
  );
  return $schedules;
}


function build_report_data($report){
  // if the reporter id is not set,
  // set it to the post_author
  if( ($reporter_id = get_post_meta($report->ID, '_reporter_id', true)) == ''){
    $reporter_id = $report->post_author;
  }

  //reporter
  $report->reporter = array(
    'id'    => $reporter_id,
    'name'  => get_the_author_meta( 'display_name', $reporter_id )
  );

  // report date
  $report->date_reported = date('jS F, Y', strtotime(get_post_meta($report->ID, '_date_reported', true)) );

  // media count
  $report->media = new StdClass;

  $report->media->audio = fetch_attachments('audio', $report->ID);
  $report->media->video = fetch_attachments('video', $report->ID);
  $report->media->photos = fetch_attachments('image', $report->ID);

  //verification status
  $report->verified = (get_post_meta($report->ID, '_cmb_verified', true ) == 'on');


  //geo data
  $report->lng = get_post_meta( $report->ID, '_lng', true);
  $report->lat = get_post_meta( $report->ID, '_lat', true);

  // get terms for this post

  // sectors
  $report->categories = array_map('_better_post_terms', wp_get_post_terms( $report->ID, 'category'));
  $report->sectors = array_map('_better_post_terms', wp_get_post_terms( $report->ID, 'sector'));
  $report->entities = array_map('_better_post_terms', wp_get_post_terms( $report->ID, 'entity'));

  return $report;
}

function _better_post_terms($obj){
  return array(
    'id'   => $obj->term_id,
    'name' => $obj->name,
  );
}

function fetch_attachments($type = '', $post_parent = '')
{
  $args = array(
    'posts_per_page' => -1,
    'post_type'   => 'attachment',
    'post_status' => 'any',
    'post_parent' => $post_parent,
    'meta_query' => array(
      array(
        'key'   => '_media_type',
        'value' => $type
      )
    )
  );
  $attachments = get_posts($args);

  foreach($attachments as $key=>$attachment) {
    $attachment->uploaded = get_post_meta($attachment->ID, '_uploaded', true ) == 'true';

    if( $type == 'audio' && get_post_meta($attachment->ID, '_uploaded', true ) == 'true'){
      $trackdata = json_decode(get_post_meta($attachment->ID, '_soundcloud_track_data', true ));
      $trackdata->embed_url = "https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/".$trackdata->id."%3Fsecret_token%3D".$trackdata->secret_token."&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_artwork=true";
      $attachment->soundcloud = $trackdata;
    }
    if( $type == 'video' && get_post_meta($attachment->ID, '_uploaded', true ) == 'true'){
      $video_id = get_post_meta($attachment->ID, '_vimeo_video_id', true);
      $attachment->vimeo = array(
        'video_id' => get_post_meta($attachment->ID, '_vimeo_video_id', true),
        'embed_url' => "//player.vimeo.com/video/".$video_id
      );
    }
    $attachments[$key] = $attachment;
  }
  return $attachments;
}

/**
 * Enqueue frontend scripts
 */
function timby_scripts() {
  //remove jquery from the header, stick it at the footer
  wp_deregister_script('jquery');
  wp_register_script('jquery', '/wp-includes/js/jquery/jquery.js', false, '1.10.2', true);
  wp_enqueue_script('jquery');


  wp_enqueue_script( 'angular', get_template_directory_uri() .'/bower_components/angular/angular.min.js', false, false, true );
  wp_enqueue_script( 'angular-route', get_template_directory_uri() .'/bower_components/angular-route/angular-route.min.js', false, false, true );
  wp_enqueue_script( 'angular-animate', get_template_directory_uri() .'/bower_components/angular-animate/angular-animate.min.js', false, false, true );
  wp_enqueue_script( 'angular-sanitize', get_template_directory_uri() .'/bower_components/angular-sanitize/angular-sanitize.min.js', false, false, true );
  wp_enqueue_script( 'angular-checklist-model', get_template_directory_uri() .'/bower_components/checklist-model/checklist-model.js', false, false, true );


  // chosen
  wp_enqueue_script( 'chosen', get_template_directory_uri() .'/bower_components/chosen/chosen.jquery.js', false, false, true );
  wp_enqueue_script( 'angular-chosen', get_template_directory_uri() .'/bower_components/angular-chosen-localytics/chosen.js', false, false, true );
  //wp_enqueue_style( 'chosen-css', get_template_directory_uri() .'/bower_components/chosen/chosen.min.css', false, false, false );
  wp_enqueue_style( 'chosen-angular-spinner-css', get_template_directory_uri() .'/bower_components/angular-chosen-localytics/chosen-spinner.css', false, false, false );

  //toaster 
  wp_enqueue_script( 'angular-toaster', get_template_directory_uri() .'/js/libs/angular.toaster.js', false, false, true );
  
  // textangular
  wp_enqueue_script( 'textangular', get_template_directory_uri() .'/bower_components/textAngular/textAngular.min.js', false, false, true );

  wp_enqueue_script( 'google-maps', '//maps.googleapis.com/maps/api/js?sensor=false', false, false, true );

  // angular-google-maps, depends on underscore
  wp_enqueue_script( 'angular-google-maps', get_template_directory_uri() .'/bower_components/angular-google-maps/dist/angular-google-maps.min.js', array('underscore'), false, true );

  //angular file upload
  wp_enqueue_script( 'angular-file-upload-shim', get_template_directory_uri() .'/bower_components/ng-file-upload/angular-file-upload-shim.min.js', false, false, true );
  wp_enqueue_script( 'angular-file-upload', get_template_directory_uri() .'/bower_components/ng-file-upload/angular-file-upload.min.js', false, false, true );

  //angular ui bootstrap
  wp_enqueue_script( 'angular-bootstrap', get_template_directory_uri() .'/js/libs/angularui-bootstrap/ui-bootstrap-custom-tpls-0.10.0.js', false, false, true );

  // app, controllers, directives and services
  wp_enqueue_script( 'app', get_template_directory_uri() .'/js/app.js',false, false, true );
  wp_enqueue_script( 'controllers', get_template_directory_uri() .'/js/controllers.js',false, false, true );
  wp_enqueue_script( 'directives', get_template_directory_uri() .'/js/directives.js',false, false, true );
  wp_enqueue_script( 'services', get_template_directory_uri() .'/js/services.js',false, false, true );
  wp_enqueue_script( 'filters', get_template_directory_uri() .'/js/filters.js',false, false, true );

  // local css


  //localize ajaxurl and nonce to the app script
  wp_localize_script(
    'app',
    'wp_data',
    array(
      'ajaxurl'      => admin_url( 'admin-ajax.php' ),
      'nonce'        => wp_create_nonce('timbyweb_front_nonce'),
      'template_url' => get_template_directory_uri()
    )
  );
}
add_action( 'wp_enqueue_scripts', 'timby_scripts' );



/**
 * When a report is saved from the backend, save custom fields
 * outside of the cmb library
 *
 * @param int $post_id The ID of the post being saved.
 */
function save_custom_report_data( $post_id ) {
  // check permissions
  if (
    // check if autosave
    defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE
    // check user editing permissions
    || ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) )
    || ! current_user_can( 'edit_post', $post_id )
    // current post type is report
    || $_POST['post_type'] != 'report'
  )
    return $post_id;

  // save the long and latitude
  if( array_key_exists('_lat', $_POST) && array_key_exists('_lng', $_POST)  )
  {
    $lat = sanitize_text_field($_POST['_lat']);
    $lng = sanitize_text_field($_POST['_lng']);
  } else {
    $lng = '0';
    $lat = '0';
  }
  // Update the meta field in the database.
  update_post_meta( $post_id, '_lat', $lat );
  update_post_meta( $post_id, '_lng', $lng );

  // save the reported_date if not already set
  if( get_post_meta( $post_id, '_date_reported', true) == '' ){
    update_post_meta( $post_id, '_date_reported', date('c', time() ) );
  }

  // save the reporter as the moderator
  if( get_post_meta( $post_id, '_reporter_id', true) === ''){
    update_post_meta( $post_id, '_reporter_id', $_POST['post_author'] );
  }
}
add_action( 'save_post', 'save_custom_report_data' );

/**
 * Creates custom database tables
 * @return null
 */
function timby_create_custom_tables(){
  global $wpdb; 

  $tablename = $wpdb->prefix . 'stories';
  $wpdb->query(
    "CREATE TABLE IF NOT EXISTS `$tablename` ( 
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
      `title` varchar(255) NOT NULL, 
      `sub_title` varchar(255) NOT NULL, 
      `content` TEXT NOT NULL,
      `author_id` int(11) unsigned NOT NULL, 
      `created` DATETIME NOT NULL, 
      PRIMARY KEY (`id`) 
    );"
  );

}
add_action('admin_init', 'timby_create_custom_tables');
/**
 * A cron job running every 10 mins checking
 * if there are any new posts, new posts are essentially
 * posts with no _cartodb_id custom field
 */
// require get_template_directory().'/lib/cartodb/cartodb.class.php';
// wp_schedule_event(time(), 'every_ten_minutes', 'sync_reports_to_carto');
// function sync_reports_to_carto(){
//   // initialize cartodb
//   $cartodb =  new CartoDBClient(
//     array(
//       'key'       => 'jTIOqWUcpsQyfvQP46s09pcGcDXEn877qhgaN44C',
//       'secret'    => 'VUX82GTIzm10o9NoptjJ5ksl73eO7miUbFi3M2t9',
//       'email'     => 'kamweti@circle.co.ke',
//       'password'  => 'P%)>zV:M&{f2K74',
//       'subdomain' => 'kaam'
//     )
//   );

//   // query all posts without a cartodb id field
//   $args = array(
//     'post_type'   => 'report',
//     'post_status' => 'publish',
//     'meta_query' => array(
//       array(
//         'key'     => '_cartodb_id',
//         'compare' => 'NOT EXISTS'
//       )
//     )
//   );
//   $reports = get_posts($args);

//   if( count($reports) > 0){
//     //syncs these new reports with carto
//     foreach($reports as $post){
//       $lnglat = array(
//         'lng' => get_post_meta($post->ID, '_longitude', true ) ?: '0', //ternary operator here assigning a default value http://cn2.php.net/ternary#language.operators.comparison.ternary
//         'lat' => get_post_meta($post->ID, '_latitude', true ) ?: '0',
//       );

//       // try inserting data into table
//       $cartoreport = $cartodb->insertRow(
//         'reports',
//         array(
//           'post_id' => "'".$post->ID."'",
//           'the_geom' => "ST_SetSRID(ST_Point(".$lnglat['lng'].", ".$lnglat['lat']."),4326)"
//         )
//       );

//       //save the cartodb id as a custom meta field
//       if( isset($cartoreport['return']['rows'][0]->id) ) {
//         update_post_meta($post->ID, '_cartodb_id', $cartoreport->rows->id );
//       }
//     }
//   }

// }

