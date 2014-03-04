<?php

$app->get('/insertdata', function() use($app){
  
  $config = array();


  $cartodb =  new CartoDBClient(
    array(
      'key' => 'jTIOqWUcpsQyfvQP46s09pcGcDXEn877qhgaN44C',
      'secret' => 'VUX82GTIzm10o9NoptjJ5ksl73eO7miUbFi3M2t9',
      'email' => 'kamweti@circle.co.ke',
      'password' => 'P%)>zV:M&{f2K74',
      'subdomain' => 'kaam.cartodb.com'
    )
  );

});