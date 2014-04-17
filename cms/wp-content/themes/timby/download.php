<?php
require_once __DIR__ . '/../../../wp-load.php';

require __DIR__ . '/../../../../server/vendor/autoload.php';



$reportid = (int) sanitize_text_field($_GET['id']);

$args = array(
  'post__in' => array($reportid),
  'post_type'   => 'report',
  'post_status' => 'publish',
  'meta_query' => array(
    array(
      'key'   => '_report_status',
      'value' => 'public'
    ),
  )
);
$report = get_posts($args);
$report = $report[0];
$report = build_report_data($report);

$archivedata = array(
  'ATTRIBUTION.txt' => dirname( __FILE__ ) .'/download_assets/ATTRIBUTION.txt' 
);

// generate the report.txt file from the template
$template = file_get_contents( dirname( __FILE__ ) .'/download_assets/report_download_template.txt');

$data = array(
  'title'           => $report->post_title,
  'date_reported'   => $report->date_reported,
  'lng'             => $report->lng,
  'lat'             => $report->lat,
  'sectors'         => implode(',', array_map('nice_tags', $report->sectors)),
  'tags'            => implode(',', array_map('nice_tags', $report->entities)),
  'description'     => $report->post_content,
  'storyurl'        => '',
  'embed_code'      => $report->embed_code,
  'vimeo_file_urls' => implode('\\r\\n', 
                        array_map(
                          function($video){
                            return 'http://vimeo.com/' . $video->vimeo['id'];
                          }, 
                          $report->media->video
                        )
                      ),
  'audio_file_urls' => implode('\\r\\n', 
                        array_map(
                          function($audio){
                            return $audio->soundcloud->permalink_url;
                          }, 
                          $report->media->audio
                        )
                      ),
);
foreach ($data as $key => $value) {
  $template = str_replace('{{'.$key.'}}', $value, $template);
}


$temp_report_file_name = dirname( __FILE__ ) . '/'. generateRandomString() . '.txt';

// write to file
if (($temp_report_file_handle = fopen( $temp_report_file_name, 'w' )) !== false) { 
  fwrite($temp_report_file_handle, $template);
  fclose($temp_report_file_handle); 
  $archivedata['report.txt'] = $temp_report_file_name;
}



foreach ($report->media->photos as $photo) {

  // get the meta tag stripped version of this image    
  $uploads = wp_upload_dir();

  $raw_file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $photo->guid );
  $rawinfo = pathinfo($raw_file_path);

  $stripped_file = $rawinfo['filename'] . '-stripped' .'.'. $rawinfo['extension'];

  $stripped_file_path = $rawinfo['dirname'] . '/' .$stripped_file;

  if( file_exists($stripped_file_path) ){
    $archivedata["images/{$rawinfo['basename']}"] = $stripped_file_path;      
  }

}


use Alchemy\Zippy\Zippy;

$zippy = Zippy::load();

// create a zip file
$zip_file = dirname( __FILE__ ) . '/report.zip';
$archiveZip = $zippy->create(
  $zip_file, 
  $archivedata
);

// trigger download
if (file_exists($zip_file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($zip_file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zip_file));
    ob_clean();
    flush();
    readfile($zip_file);

    // cleanup the files created
    unlink($zip_file);
    unlink($temp_report_file_name);

    exit;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function nice_tags($tag){
  return $tag['name'];
}