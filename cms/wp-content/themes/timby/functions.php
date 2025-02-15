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

/**
 * Timby src files
 */
require get_template_directory() . '/src/Timby/Configuration.php';


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


    timby_create_custom_tables();
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

  // report status, defaults to private
  $report->status = 'private';
  if( get_post_meta($report->ID, '_report_status', true) == 'public' ){
    $report->status = 'public';
  }

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
  
  $report->download_link = get_template_directory_uri() . '/download.php?id='. $report->ID;
  // build the embed code, and 
  if( $report->status == 'public'){
    $report->embed_code = '<iframe src="'.get_template_directory_uri() .'/embed.php?id='.$report->ID.'" width="400px; height: 400px;"></iframe>';
  }

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

    if( $type == 'image' ){
      // get multiple images iszes
      if( $small = wp_get_attachment_image_src($attachment->ID, 'thumbnail', true) )
        $attachment->small = $small[0];

      if( $medium = wp_get_attachment_image_src($attachment->ID, 'medium', true) )
        $attachment->medium = $medium[0];

      if( $large = wp_get_attachment_image_src($attachment->ID, 'large', true) )
        $attachment->large = $large[0];
    }

    if( $type == 'audio' && get_post_meta($attachment->ID, '_uploaded', true ) == 'true'){
      // build soundcloud track data
      $trackdata = json_decode(get_post_meta($attachment->ID, '_soundcloud_track_data', true ));
      $trackdata->embed_url = "https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/".$trackdata->id."%3Fsecret_token%3D".$trackdata->secret_token."&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_artwork=true";
      $attachment->soundcloud = $trackdata;
    }
    
    if( $type == 'video' && get_post_meta($attachment->ID, '_uploaded', true ) == 'true'){
      // get data about this video
      $vimeo_data = get_post_meta($attachment->ID, '_vimeo', true);

      $vimeo_data['embed_url'] = "//player.vimeo.com/video/".$vimeo_data['id'];
      $attachment->vimeo = $vimeo_data;
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

  //public facing script
  wp_enqueue_script( 'pub-script', get_template_directory_uri() .'/js/scripts.js', array('jquery','google-maps'), false, true );

  wp_enqueue_script( 'angular', get_template_directory_uri() .'/bower_components/angular/angular.min.js', false, false, true );
  wp_enqueue_script( 'angular-route', get_template_directory_uri() .'/bower_components/angular-route/angular-route.min.js', false, false, true );
  wp_enqueue_script( 'angular-animate', get_template_directory_uri() .'/bower_components/angular-animate/angular-animate.min.js', false, false, true );
  wp_enqueue_script( 'angular-sanitize', get_template_directory_uri() .'/bower_components/angular-sanitize/angular-sanitize.min.js', false, false, true );
  wp_enqueue_script( 'angular-checklist-model', get_template_directory_uri() .'/bower_components/checklist-model/checklist-model.js', false, false, true );

  // ngIdle
  wp_enqueue_script( 'ng-idle', get_template_directory_uri() .'/bower_components/ng-idle/angular-idle.min.js', false, false, true );
  
  // chosen
  wp_enqueue_script( 'chosen', get_template_directory_uri() .'/bower_components/chosen/chosen.jquery.js', false, false, true );
  wp_enqueue_script( 'angular-chosen', get_template_directory_uri() .'/bower_components/angular-chosen-localytics/chosen.js', false, false, true );
  //wp_enqueue_style( 'chosen-css', get_template_directory_uri() .'/bower_components/chosen/chosen.min.css', false, false, false );
  // wp_enqueue_style( 'bootstrap-css', get_template_directory_uri() .'/css/bootstrap.css', false, false, false );
  wp_enqueue_style( 'chosen-angular-spinner-css', get_template_directory_uri() .'/bower_components/angular-chosen-localytics/chosen-spinner.css', false, false, false );

  //toaster 
  wp_enqueue_script( 'angular-toaster', get_template_directory_uri() .'/js/libs/angular.toaster.js', false, false, true );
  
  // textangular
  wp_enqueue_script( 'rangy-core', get_template_directory_uri() .'/bower_components/rangy/rangy-core.js', false, false, true );
  wp_enqueue_script( 'textangular-setup', get_template_directory_uri() .'/bower_components/textAngular/src/textAngularSetup.js', false, false, true );
  wp_enqueue_script( 'textangular-sanitize', get_template_directory_uri() .'/bower_components/textAngular/src/textAngular-sanitize.js', false, false, true );
  wp_enqueue_script( 'textangular', get_template_directory_uri() .'/bower_components/textAngular/src/textAngular.js', false, false, true );

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
  wp_enqueue_script( 'modal', get_template_directory_uri() .'/js/modal.js',false, false, true );
  wp_enqueue_script( 'directives', get_template_directory_uri() .'/js/directives.js',false, false, true );
  wp_enqueue_script( 'services', get_template_directory_uri() .'/js/services.js',false, false, true );
  wp_enqueue_script( 'filters', get_template_directory_uri() .'/js/filters.js',false, false, true );

  // cartodb from CDN
  wp_enqueue_style( 'cartodb-styles', 'http://libs.cartocdn.com/cartodb.js/v3/themes/css/cartodb.css',false, false, false );
  wp_enqueue_script( 'cartodb-script', 'http://libs.cartocdn.com/cartodb.js/v3/cartodb.js',false, false, false );

  //Main styles last to override all the bullcrap
  wp_enqueue_style( 'stylesmain', get_template_directory_uri() .'/css/global.css', false, false, false );
  

  //localize ajaxurl and nonce to the app script
  wp_localize_script(
    'app',
    'wp_data',
    array(
      'ajaxurl'      => admin_url( 'admin-ajax.php' ),
      'nonce'        => wp_create_nonce('timbyweb_front_nonce'),
      'template_url' => get_template_directory_uri(),
    )
  );
  wp_localize_script('app', 'Config', get_timby_options_json());
  
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

  // stories table
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

  // published stories table
  $tablename = $wpdb->prefix . 'published_stories';
  $wpdb->query(
    "CREATE TABLE IF NOT EXISTS `$tablename` ( 
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
      `title` varchar(255) NOT NULL, 
      `sub_title` varchar(255) NOT NULL, 
      `content` TEXT NOT NULL,
      `master_story_id` int(11) unsigned NOT NULL,
      `created` DATETIME NOT NULL, 
      PRIMARY KEY (`id`) 
    );"
  );

}

/**
 * Builds essential fields for display 
 * 
 * @param  stdClass $story a database row object
 * @return stdClass $story
 */
function build_story_data($story){

  // format story created date
  $story->created = date('jS F, Y', strtotime($story->created) );

  $story->published = ($story->published == '1');

  // return the latest copy of reports embedded in this story
  // if we queried the content field
  if( $story->content ) {
    $content = json_decode($story->content);
    
    foreach ($content as $key => $content_block) {
      if( $content_block->type == 'report'){
        $report = get_post($content_block->report->ID);

        if( count($report) > 0 ) {
          // get report data and add keys to our report object
          $report = build_report_data($report);
          $content[$key]->report = $report;
        } else{
          // report was either unpublished or
          $content[$key]->report = null;
        }
      }
    }

    // once done, encode the object and assign it
    $story->content = json_encode($content);

  }

  return $story;
}


function get_page_by_name($page_title, $output = OBJECT) {
  global $wpdb;
  $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'", $page_title ));
  if ( $post )
    return get_post($post, $output);

  return null;
}

/**
 * Create a list of default pages required by the app
 * page-{slug}.php templates already exist in our theme
 */
function timby_create_default_pages(){
  $pages_to_create = array('Dashboard', 'Stories', 'Story');

  foreach( $pages_to_create as $page ) {
    $slug = strtolower($page);
    if( is_null(get_page_by_name($slug))) {
      wp_insert_post(
        array(
          'post_type' => 'page',
          'post_title' => $page,
          'post_name' => $slug,
          'post_status' => 'publish',
          'post_content' => '',
        )
      );
    }  
  }
    
}
add_action('admin_init', 'timby_create_default_pages');

/**
 * Get all stories 
 */
function fetch_all_stories(){
  global $wpdb;
  
  $storiestable = $wpdb->prefix . 'stories';
  $published_stories_table = $wpdb->prefix . 'published_stories';

  $stories = $wpdb->get_results("
    SELECT id, title, sub_title, created,  
    (
      SELECT COUNT(id)  FROM $published_stories_table 
      WHERE master_story_id = $storiestable.id LIMIT 0, 1 
    ) as published
    FROM $storiestable ORDER BY created DESC
  ");

  foreach($stories as $key => $story){
    $story = build_story_data($story); // in functions.php
    $stories[$key] = $story;
  }

  return $stories;
}

/**
 * Fetch published stories
 * @return array $stories
 */
function fetch_published_stories(){
  global $wpdb;
  
  $tablename = $wpdb->prefix . 'published_stories';

  $stories = $wpdb->get_results("SELECT id, title, sub_title, created FROM $tablename ORDER BY created DESC");


  foreach($stories as $key => $story){
    $story = build_story_data($story); // in functions.php
    $stories[$key] = $story;
  }

  return $stories;
}

/*
 * ############################################
 * Theme Options
 * ############################################
 */
function setup_theme_admin_menus()
{
    add_menu_page('Timby Settings', 'Timby settings', 'manage_options',
        'timby_settings', 'timby_settings_services');
}

function timby_settings()
{
    echo "Timby settings";
}

function get_timby_options_json()
{
    $options = array();
    foreach (Timby_Configuration::getPublicOptions() as $option) {
        $options[$option] = get_option($option);
    }

    return json_encode($options, JSON_FORCE_OBJECT);
}

function timby_settings_services()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }

    $options = Timby_Configuration::getOptions();

    if (array_key_exists('update_settings', $_POST)) {
        foreach ($options as $option => $fields) {
            if (array_key_exists($option, $_POST)) {
                update_option($option, esc_attr($_POST[$option]));
            }
        }
    }
?>
<div class="wrap">
    <div class="update-nag">
        <p>Go to <a href="<?php echo get_site_url() ?>/dashboard" target="_blank">Timby Dashboard</a></p>
    </div>
    <h2>Timby settings</h2>

    <form action="" method="post">
        <table class="form-table">
            <tbody>
            <?php foreach ($options as $option => $field): ?>
            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo $option ?>"><?php echo $field['label'] ?></label>
                </th>
                <td>
                    <input class="regular-text" type="<?php echo $field['type'] ?>" name="<?php echo $option ?>" id="<?php echo $option ?>" value="<?php echo get_option($option) ?>"/>
                </td>
            </tr>
    <?php endforeach; ?>
            </tbody>
        </table>
        <input type="hidden" name="update_settings" value="Y"/>
        <input type="submit" value="Save"/>
    </form>
</div>
<?php
}

// This tells WordPress to call the function named "setup_theme_admin_menus"
// when it's time to create the menu pages.
add_action("admin_menu", "setup_theme_admin_menus");
