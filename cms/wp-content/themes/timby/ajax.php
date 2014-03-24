<?php 
require_once __DIR__ . '/../../../wp-load.php';

switch($_REQUEST['action']){
  case 'get_report':
    $ID = (int) $_REQUEST['id'];

    $report = get_post($ID);

    // get report data and add keys to our report object
    $report_data = get_report_data($report);
    foreach($report_data as $key => $val){
      $report->{$key} = $val;
    }

    echo json_encode(
      array(
        'status' => 'success',
        'data' => $report,
      )
    );      


    break;
  default:
    break;
}