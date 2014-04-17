<?php
// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# include the cartodb library
require_once __DIR__ . '/../server/app/vendor/cartodb/cartodb.class.php';

require_once __DIR__ .'/config.php';

function sync_reports_to_carto(){
  global $cartodbconfig;

  // initialize cartodb
  $cartodb =  new CartoDBClient(
    array(
      'key'       => $cartodbconfig['consumer_key'],
      'secret'    => $cartodbconfig['consumer_secret'],
      'email'     => $cartodbconfig['email'],
      'password'  => $cartodbconfig['password'],
      'subdomain' => $cartodbconfig['subdomain']
    )
  );
  # Check if the key and secret work fine and you are authorized
  if(!$cartodb->authorized) {
    echo("There is a problem authenticating, check the key and secret");
    die();
  }

  // query all reports without a cartodb id field
  $args = array(
    'post_type'   => 'report',
    'post_status' => 'publish',
    'meta_query' => array(
      array(
        'key'     => '_synced_to_carto',
        'compare' => 'NOT EXISTS'
      )
    )
  );
  $reports = get_posts($args);

  if( count($reports) > 0){
    $data = array();
    //syncs these new reports with carto
    foreach($reports as $post){
      $lnglat = array(
        'lng' => get_post_meta($post->ID, '_lng', true ) ?: '0', //ternary operator here assigning a default value http://cn2.php.net/ternary#language.operators.comparison.ternary
        'lat' => get_post_meta($post->ID, '_lat', true ) ?: '0',
      );

      // try inserting data into table
      $data[] = "( '$post->ID', ST_SetSRID(ST_Point(".$lnglat['lng'].", ".$lnglat['lat']."),4326) )";
      update_post_meta( $post->ID, '_synced_to_carto', 'true' ); // mark this report as synced
    }
  }

  $query = "INSERT INTO reports (post_id,the_geom) VALUES  ".implode(',', $data).";";
  $cartodb->runSql($query, false);

}

sync_reports_to_carto();
