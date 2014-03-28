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
        // update custom fields if set
        if( isset($data->custom_fields) ){
          foreach ($data->custom_fields as $meta_key => $meta_value) {
            update_post_meta( $post['ID'], $meta_key, $meta_value );        
          }
        }

        // update terms
        if( isset($data->taxonomies) ){
          foreach ($data->taxonomies as $taxonomy => $terms) {
            $tagids = array_map(
              function($term){ 
                return $term->id;
              },
              $terms
            );
            wp_set_post_terms( $post['ID'], $tagids, $taxonomy, false );  // replace existing terms      
          }
        }

        echo json_encode(
          array(
            'status' => 'success'
          )
        );


      }
    }

    break;

  case 'create_report':
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){ 
      if(! isset($data->ID) ) unset($data->ID);
      $post = array();

      foreach($data as $key=>$value){
        $post[$key] = $value;
      }

      $post['post_type'] = 'report';
      $post['post_status'] = 'publish';
      
      $data->custom_fields = array(
        '_date_reported' => date('c', time() )
      );

      if( ! ($ID = wp_insert_post($post)) == 0 ){
        // update custom fields if set
        if( isset($data->custom_fields) ){
          foreach ($data->custom_fields as $meta_key => $meta_value) {
            update_post_meta( $ID, $meta_key, $meta_value );        
          }
        }

        // update terms
        if( isset($data->taxonomies) ){
          foreach ($data->taxonomies as $taxonomy => $terms) {
            $tagids = array_map(
              function($term){ 
                return $term->id;
              },
              is_array($terms) ? $terms : array($terms)
            );
            wp_set_post_terms( $ID, $tagids, $taxonomy, false );  // replace existing terms      
          }
        }

        echo json_encode(
          array(
            'status' => 'success'
          )
        );


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

  case 'get_all_terms':
      $result = array();
      $taxonomies = array('sector', 'entity', 'category');
      foreach($taxonomies as $taxonomy){
        $terms = get_terms($taxonomy, array('hide_empty' => false, 'fields' => 'id=>name') );
        $nice_terms = array();
        foreach($terms as $id=>$name){
          $nice_terms[] = array('id' => $id, 'name'=> $name);
        }
        $result[$taxonomy] = $nice_terms;
      }
      echo json_encode(
        array(
          'status' => 'success',
          'terms' => $result,
        )
      );
    break;

  default:
    break;
}