<?php
require_once __DIR__ . '/../../../wp-load.php';


$reportid = (int) $_GET['id'];
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

if( empty($report)) exit('Sorry that report was either not found or removed!');
$report = build_report_data($report)
?>
<!DOCTYPE html>
<html lang="en" ng-app="timby">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>

    <style>
      .report-thumb {
          border: 1px solid #ccc;
          *zoom: 1
      }

      .report-thumb:before,.report-thumb:after {
          content: " ";
          display: table
      }

      .report-thumb:after {
          clear: both
      }

      .report-thumb-info {
          padding: 1em
      }

      .report-thumb-map {
          padding: 4px
      }

      #report-location {
          width: 100%;
          height: 150px
      }
    </style>
    
    <script>
      // google analytics event tracking
      if( _gaq )
         _gaq.push(['_trackEvent', 'Reports - Embedd', 'Viewed', '<?php echo $report->post_title ?>']);

    </script>
    <script>
      // passed as a callback after the maps script loads
      function initialize(){
        var _map_element = document.getElementById('report-location');

        // initialize the map
        var map = new google.maps.Map(
          _map_element,
          {
            zoom: 7,
            center: new google.maps.LatLng(
              _map_element.getAttribute('data-lat'),
              _map_element.getAttribute('data-lng')
            )
          }
        );
        // add marker
        new google.maps.Marker({
          position: new google.maps.LatLng(
            _map_element.getAttribute('data-lat'),
            _map_element.getAttribute('data-lng')
          ),
          map: map
        });
      }

      // load the maps library if its not yet loaded
      function loadScript(){
        if( !window.hasOwnProperty('google') || !window.google.hasOwnProperty('maps') ){
          var gm = document.createElement('script');
          gm.type = 'text/javascript';
          gm.src = "//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&callback=initialize";
          document.body.appendChild(gm);
        }        
      }
      window.onload = loadScript;
    </script>
  </head>
  <body>
    <div class="report-thumb">
      <div class="seven report-thumb-info">
        <h6 class="list-title"><?php echo $report->post_title ?></h6>
        <p class="list-content text-muted">
          <?php echo $report->date_reported . ' by '. $report->reporter->name; ?>
        </p>
        <div class="horz-list text-muted">
          <i class="fa fa-camera"></i> <?php echo count($report->media->photos) ?>
          <i class="fa fa-video-camera"></i> <?php echo count($report->video->photos) ?>
          <i class="fa fa-volume-up"></i> <?php echo count($report->audio->photos) ?>
        </div>
      </div>

      <div class="five report-thumb-map">
        <div id="report-location" data-lat="<?php echo $report->lat ?>" data-lng="<?php echo $report->lng ?>" ></div>
    </div>
  </body>
</html>