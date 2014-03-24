<?php

// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# include the soundcloud library
require_once __DIR__ . '/../server/app/vendor/soundcloud/Services/Soundcloud.php';

require 'config.php';


// intialize soundcloud
$soundcloud = new Services_Soundcloud(
  $soundcloudconfig['client_key'],
  $soundcloudconfig['client_secret']
);

// fetch new audio media
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
        'value' => 'audio'
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

if( count($new_unoploaded_media) > 0){
  // exchange authorization code for access token
  try {
    // retreive the access token through credentials flow
    $credentials = $soundcloud->credentialsFlow(
      $soundcloudconfig['username'], 
      $soundcloudconfig['password']
    );  
    // set the access token
    $soundcloud->setAccessToken($credentials['access_token']);

    foreach($new_unoploaded_media as $media){
      $media_type = get_post_meta($media->ID, '_media_type', true);

      if( $media_type == 'audio') {

        // grab the file path
        $uploads = wp_upload_dir();
        $file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $media->guid );

        // try and do an upload
        $upload = $soundcloud->post('tracks', 
          array(
            'track[title]'      => get_the_title($media->post_parent),
            'track[sharing]'    => 'private',
            'track[asset_data]' => '@' . $file_path
          )
        );

        if( property_exists(json_decode($upload), 'permalink') ) {
          update_post_meta($media->ID, '_uploaded', 'true');
          update_post_meta($media->ID, '_soundcloud_track_data', $upload );
        }

      }
    }

  } catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
    exit($e->getMessage());
  }  
} else {
  echo 'no new media';
}
