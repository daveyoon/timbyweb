<?php

$app->get('/carto/insert', function() use($app){

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
  $response = $cartodb->insertRow(
    'reports', 
    array(
      'post_id' => "'345'",
      'status' => "'0'",
      'the_geom' => "ST_SetSRID(ST_Point(-31.23543, 22.24244),4326)"
    )
  );


});