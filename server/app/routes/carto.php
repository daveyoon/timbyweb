<?php
// use Illuminate\Database\Capsule\Manager as Capsule;

// $capsule = new Capsule;

// $capsule->addConnection(
//   array(
//     'driver'    => 'pgsql'
//   )
// );

// // Make this Capsule instance available globally via static methods
// $capsule->setAsGlobal();

// // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
// $capsule->bootEloquent();


$app->get('/carto/insert', function() use($app){
  echo Capsule::table('reports')->toSql();
  exit;

  $config = array();


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
      'description' => "'hello my gutfriend'",
      'the_geom' => "ST_SetSRID(ST_Point(-31.23543, 22.24244),4326)"
    )
  );


});