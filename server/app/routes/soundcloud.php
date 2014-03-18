<?php

$app->group('/soundcloud', function() use ($app){

  /**
   * Login
   * 
   * Params
   *   user_name
   *   password
   *   
   */
  $app->post('/upload', function() use ($app) {
  

    $track = array(
        'track[title]' => '',
        'track[tags]' => 'dubstep rofl',
        'track[asset_data]' => '@/absolute/path/to/track.mp3'
    );

    try {
        $response = $soundcloud->post('tracks', $track);
    } catch (Services_Soundcloud_Invalid_Http_Response_Code_Exception $e) {
        exit($e->getMessage());
    }
  });

});