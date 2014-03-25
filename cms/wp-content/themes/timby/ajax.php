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

  case 'login':
    $data = file_get_contents("php://input");
    $data = json_decode($data);

    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_login_nonce') == true ){ 
      if( isset($data->user) && isset($data->password) ) { 

        $user = wp_signon( array( 
          'user_login' => sanitize_text_field($data->user), 
          'user_password' => sanitize_text_field($data->password) 
        ), false); 

        if( !is_wp_error($user) ) { 
          echo json_encode(
            array(
              'status' => 'success',
              'user' => $user
            )
          );
        } 
      }
    } 
    break;

  default:
    break;
}