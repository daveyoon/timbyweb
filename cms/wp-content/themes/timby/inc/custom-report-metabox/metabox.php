<?php
/**
 * 
 */

 function map_enqueue($hook) {
   if( $hook == 'post.php' || $hook == 'post-new.php' || $hook == 'page-new.php' || $hook == 'page.php' ) {
     wp_enqueue_script( 'google-map', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' );
     wp_enqueue_script( 'map-script', get_bloginfo('template_url'). '/inc/custom-report-metabox/js/map.js' );
   }
    
 }
 add_action( 'admin_enqueue_scripts', 'map_enqueue' );


/**
 * Custom metabox that shows related products for 
 * the selected product
 */
function map_metabox(){
  global $post;

  $post_type = $post->post_type;
  $post_types = array('report');     //limit meta box to certain post types

  if ( in_array( $post_type, $post_types )) {
    add_meta_box(
    'map_metabox', 
    'Set a location', 
    function(){
      include( dirname(__FILE__) . '/views/metabox-html.php' );
    }, $post_type );
  }
}
add_action( 'add_meta_boxes', 'map_metabox' );


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
 function map_save_postdata( $post_id ) {

   /*
    * We need to verify this came from the our screen and with proper authorization,
    * because save_post can be triggered at other times.
    */

   // If this is an autosave, our form has not been submitted, so we don't want to do anything.
   if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
       return $post_id;

   // Check the user's permissions.
   if ( 'page' == $_POST['post_type'] ) {

     if ( ! current_user_can( 'edit_page', $post_id ) )
         return $post_id;
  
   } else {

     if ( ! current_user_can( 'edit_post', $post_id ) )
         return $post_id;
   }

   // Sanitize user input.
   if( array_key_exists('_latitude', $_POST) && array_key_exists('_longitude', $_POST)  )
   {
     $longitude = $_POST['_latitude'];
     $latitude = $_POST['_longitude'];    
   } else {
     $longitude = '0';
     $latitude = '0'; 
   }
    // Update the meta field in the database.
   update_post_meta( $post_id, '_latitude', $latitude );
   update_post_meta( $post_id, '_longitude', $longitude );

 }
 add_action( 'save_post', 'map_save_postdata' );