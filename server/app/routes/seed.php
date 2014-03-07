<?php

// generate some seed data
$app->get('/seed', function() use($app) {
  $coords = array(
    "6.5139812, -10.6773857",
    "4.676354, -8.2339446",
    "6.4458329, -8.2339446",
    "7.069609, -10.48656",
    "5.882795, -10.0436599",
    "6.8275, -8.34278",
    "6.3931689, -10.4930564",
    "6.422572, -10.725322",
    "5.9696869, -10.1438925",
  );

  $data_limit = 20;
  for($i = 0; $i < $data_limit; $i++)
  {
    $faker = Faker\Factory::create();

    $lnglat = explode(',', $coords[mt_rand(0, count($coords) - 1)]); //generates random coords

    Requests::post(
      $app->config('wordpress_site_url').'/api/posts/create_post',
      array('Accept' => 'application/json'), 
      array(
        'title'   => $faker->sentence(6),
        'content' => $faker->realText(80),
        'author'  => '2',
        'date'    => $faker->dateTimeBetween('30 days ago','now')->format('Y-m-d h:i:s'),
        'type'    => 'report',
        'status'  => 'pending',
        'custom_fields' =>  array(
          '_latitude'  => $lnglat[0],
          '_longitude' => $lnglat[1]
        )
      )
    );

  }

});