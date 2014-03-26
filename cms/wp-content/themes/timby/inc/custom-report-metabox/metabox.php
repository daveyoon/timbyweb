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

