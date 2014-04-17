<?php

// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# include vimeo and soundcloud libs
require_once __DIR__ . '/../server/app/vendor/soundcloud/Services/Soundcloud.php';
require_once __DIR__ . '/../server/app/vendor/vimeo/vimeo.php';

require 'config.php';


// fetch public reports
$public_reports = get_posts(
  array(
    'post_type'   => 'report',
    'post_status' => 'publish',
    'meta_query' => array(
      array(
        'key'   => '_report_status',
        'value' => 'public'
      ),
    )
  )
);

$media_to_publisize = array();
foreach($public_reports as $post){
  // fetch image media that hasn't yet been uploaded to s3
  $args = array(
    'post_type'   => 'attachment',
    'numberposts' => null,
    'post_status' => null,
    'post_parent' => $post->ID,
    'meta_query' => array(
      'relation' => 'AND',
      array(
        'key'   => '_media_type',
        'value' => array('audio', 'video'),
        'compare' => 'IN',
      ),
      array(
        'key'   => '_made_public',
        'value' => '',
        'compare' => 'NOT EXISTS'
      )
    )
  );

  $media = get_posts($args);
  if( count($media) > 0 ) {
    $media_to_publisize = array_merge($media_to_publisize, $media);
  }

}

try {
  foreach ($media_to_publisize as $media ) {
    if( get_post_meta($media->ID, '_uploaded', true ) == 'true' ){
      if( get_post_meta($media->ID, '_media_type', true ) == 'video'){
        // publisize vimeo video
        $vimeodata = get_post_meta($media->ID, '_vimeo', true );

        // intialize vimeo
        $vimeo = new Vimeo(
          $vimeoconfig['client_key'], 
          $vimeoconfig['client_secret'], 
          $vimeoconfig['access_token'],
          $vimeoconfig['access_token_secret']
        );

        // set video privacy to public
        $vimeo->call(
          'vimeo.videos.setPrivacy', 
          array(
            'video_id' => $vimeodata['id'],
            'privacy'  => 'anybody'
          )
        );

        // get the video thumbnails
        $response = $vimeo->call(
          'vimeo.videos.getThumbnailUrls',
          array(
            'video_id' => $vimeodata['id'],
          )
        );
        // record the id and thumbnails
        $thumbnails = $response->thumbnails->thumbnail; //vimeo returns 3 thumbnail sizes

        update_post_meta($media->ID, '_vimeo', array(
            'id'         => $vimeodata['id'],             
            'thumbnails' => array(
              'small'  => $thumbnails[0]->_content,
              'medium' => $thumbnails[1]->_content,
              'large'  => $thumbnails[2]->_content,
            )
          )
        );
      }

      if( get_post_meta($media->ID, '_media_type', true ) == 'audio'){
        $soundcloud = new Services_Soundcloud(
          $soundcloudconfig['client_key'],
          $soundcloudconfig['client_secret']
        );
        // retreive the access token through credentials flow
        $credentials = $soundcloud->credentialsFlow(
          $soundcloudconfig['username'], 
          $soundcloudconfig['password']
        );  
        // set the access token
        $soundcloud->setAccessToken($credentials['access_token']);

        // publisize soundcloud audio
        $trackdata = json_decode(get_post_meta($media->ID, '_soundcloud_track_data', true ));

        // fetch a track by it's ID
        $track = json_decode($soundcloud->get('tracks/'.$trackdata->id));

        // update the track's metadata
        $soundcloud->put('tracks/'.$track->id, array(
          'track[sharing]'    => 'public'
        ));
      }

      update_post_meta($media->ID, '_made_public', 'true' );

    }
  }
} catch(Error $e){
  echo "There was an making one of the media objects public.";
}
