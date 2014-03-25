<?php 
require_once __DIR__ . '/../../../wp-load.php';


switch($_REQUEST['action']){
  case 'get_report':
    $ID = (int) $_REQUEST['id'];

    $report = get_post($ID);

    if( count($report) > 0 ) {
      // get report data and add keys to our report object
      $report = build_report_data($report);
      echo json_encode(
        array(
          'status' => 'success',
          'report' => $report,
        )
      );      
    }


    break;

  case 'get_new_reports':
    echo json_encode(
      array(
        'status' => 'success',
        'reports' => fetch_new_reports()
      )
    );

  break;
  default:
    break;
}