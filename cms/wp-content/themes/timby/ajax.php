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

  case 'update_report':
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){ 
      if(! isset($data->ID) ) exit(0);
      $updatable_fields = array(
        'post_title', 'post_content'
      );

      $post = array();
      $post['ID'] = $data->ID;
      unset($data->ID);
      foreach($data as $key=>$value){
        if( in_array($key, $updatable_fields)){
          $post[$key] = $value;
        }
      }

      if( ! wp_update_post($post) == 0 ){
        echo json_encode(
          array(
            'status' => 'success'
          )
        );

        // update custom fields if set
        if( isset($data->custom_fields) ){
          foreach ($data->custom_fields as $meta_key => $meta_value) {
            update_post_meta( $post['ID'], $meta_key, $meta_value );        
          }
        }

      }
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

    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){ 
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