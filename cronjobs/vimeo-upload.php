<?php

// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# The vimeo LIB
require_once __DIR__ . '/../server/app/vendor/vimeo/vimeo.php';

# Config file
require 'config.php';

# Get new video reports and upload assets to vimeo
$newreports = get_posts(
  array(
    'post_type'   => 'report',
    'post_status' => 'publish'
  )
);

$new_unoploaded_media = array();
foreach($newreports as $post){

  $args = array(
    'post_type'   => 'attachment',
    'numberposts' => null,
    'post_status' => null,
    'post_parent' => $post->ID,
    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key'   => '_media_type',
        'value' => 'video'
      ),
      array(
        'key'   => '_uploaded',
        'compare' => 'NOT EXISTS',
        'value' => '' //passing an invalid string here, see the docs http://codex.wordpress.org/Class_Reference/WP_Query
      )
    )
  );
  $media = get_posts($args);
  if( count($media) > 0 ) {
    $new_unoploaded_media = array_merge($new_unoploaded_media, $media);
  }
}

foreach($new_unoploaded_media as $media){
  $media_type = get_post_meta($media->ID, '_media_type', true);

  if( $media_type == 'video') {
    // intialize vimeo
    $vimeo = new Vimeo(
      $vimeoconfig['client_key'], 
      $vimeoconfig['client_secret'], 
      $vimeoconfig['access_token'],
      $vimeoconfig['access_token_secret']
    );

    // grab the file path
    $uploads = wp_upload_dir();
    $file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $media->guid );

    if( $video_id = $vimeo->upload( $file_path ) ) {
      update_post_meta($media->ID, '_uploaded', 'true');

      // store vimeo id, later we will store thumbnails
      $vimeodata = array(
        'id'  => $video_id
      );
      update_post_meta($media->ID, '_vimeo', $vimeodata );

      // set privacy to password
      $vimeo->call(
        'vimeo.videos.setPrivacy', 
        array(
          'privacy'  => 'password', 
          'video_id' => $video_id,
          'password' => 'timby'
        )
      );

      // set the title to the report title
      $vimeo->call(
        'vimeo.videos.setTitle', 
        array(
          'title'    => get_the_title($media->post_parent),  //title of the parent
          'video_id' => $video_id
        )
      );

      exit(0);
    } 

  }
}

