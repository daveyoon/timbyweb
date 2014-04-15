<?php

// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# include the soundcloud library
require_once __DIR__ . '/../server/app/vendor/soundcloud/Services/Soundcloud.php';

require 'config.php';


// Include the SDK using the Composer autoloader
require '../server/vendor/autoload.php';

use Aws\S3\S3Client;



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

$images_toupload = array();
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
        'value' => 'image'
      ),
      array(
        'key'   => '_s3',
        'compare' => 'NOT EXISTS',
        'value' => '' //passing an invalid string here, see the docs http://codex.wordpress.org/Class_Reference/WP_Query
      )
    )
  );

  $media = get_posts($args);
  if( count($media) > 0 ) {
    $images_toupload = array_merge($images_toupload, $media);
  }
}

try {
  // Instantiate the S3 client
  $s3 = S3Client::factory(array(
    'key'    => $amazon_s3config['access_key'],
    'secret' => $amazon_s3config['access_secret']
  ));

  foreach($images_toupload as $media){

    // grab the file path
    $uploads = wp_upload_dir();

    $raw_file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $media->guid );
    $rawinfo = pathinfo($raw_file_path);

    $stripped_file = $rawinfo['filename'] . '-stripped' .'.'. $rawinfo['extension'];

    $stripped_file_path = $rawinfo['dirname'] . '/' .$stripped_file;

    //strip the metadata using our exiftool
    if( !file_exists( $stripped_file_path) ){
      exec("exiftool  -all= -out {$stripped_file_path} {$raw_file_path}" );
    }

    $newfilename = generate_file_key() .'.'. $pathinfo['extension']; // this is just a random generated code that we use to alias the file on s3
    
    // try and do an upload the stripped file
    $s3->putObject(array(
      'Bucket' => 'timby',
      'Key'    => $newfilename,
      'Body'   => fopen($stripped_file_path, 'r'),
      'ACL'    => 'public-read',
    ));

    // We can poll the object until it is accessible
    $s3->waitUntilObjectExists(array(
      'Bucket' => 'timby',
      'Key'    => $newfilename
    ));

    update_post_meta($media->ID, '_s3', 
      array(
        'file' => $newfilename
      )
    );

  }
  // poll until object is ready
} catch (S3Exception $e) {
    echo "There was an error uploading the file.\n";
}

function generate_file_key() {
  $token = md5(time());
  $token_len = strlen($token);
  $token_half = ceil($token_len / 2);
  $token = substr($token, $token_half, $token_half - 2);
  return $token;
}