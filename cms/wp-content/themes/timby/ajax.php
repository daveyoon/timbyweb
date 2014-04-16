<?php
require_once __DIR__ . '/../../../wp-load.php';


switch($_REQUEST['action']){

  case 'info':

    echo json_encode(
      array(
        'status' => 'success',
        'data' => array(
          'terms'     => _get_all_terms(),
          'api_users' => _get_all_api_users(),
        )
      )
    );
    break;

  case 'report.get':
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

  case 'report.update':
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

  case 'report.create':
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
      
      // set our custom date reported, which is different from post_created
      if( !property_exists($data, 'custom_fields'))
        $data->custom_fields = new stdClass;

      if( !isset($data->custom_fields->_date_reported))
        $data->custom_fields->_date_reported = date('c', time() );

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
            'status' => 'success',
            'report' => array(
              'ID' => $ID
            )
          )
        );


      }
    }

    break;

  case 'reports.all':
    $args = array(
      'meta_query' => array()
    );

    if( isset($_GET['verified'])){
      array_push(
        $args['meta_query'], 
        array(
          'key' => '_cmb_verified',
          'value' => sanitize_text_field($_GET['verified']),
        )
      );
    }

    if( isset($_GET['status']) ){
      array_push(
        $args['meta_query'], 
        array(
          'key' => '_report_status',
          'value' => sanitize_text_field($_GET['status']),
        )
      );
    }

    $args = array_merge(
      array(
        'post_type'      => 'report',
        'posts_per_page' => -1,
        'orderby'        => 'meta_key = _date_reported',
        'order'          => 'DESC',
      ),
      $args
    );
    

    $reports = get_posts($args);

    foreach($reports as $key => $report){
      $report = build_report_data($report); // in functions.php
      $reports[$key] = $report;
    }


    echo json_encode(
      array(
        'status' => 'success',
        'reports' => $reports
      )
    );
    break;

  case 'story.save':
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){ 

      if( $story_id = save_story($data) ) {
        echo json_encode(
          array(
            'status' => 'success',
            'id'     => $story_id
          )
        );        
      } else{
        echo json_encode(
          array(
            'status'  => 'error',
            'message' => 'Saving failed'
          )
        ); 
      }
    }

    break;

  case 'story.saveandpublish':
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){ 

      // make reports contained in the story public
      foreach( $data->story->content as $key => $thecontent) {
        if( $thecontent->type == 'report' && $thecontent->report->status !== 'public' ){
          update_post_meta($thecontent->report->ID, '_report_status', 'public'); //set the report as public
          $data->story->content[$key]->report->status = 'public';
        }
      }
      
      // the save
      if( $story_id = save_story($data) ) {
        // publish and return both the story id and published story id
        if( $published_story_id = publish_story($story_id, $data) ) {
          echo json_encode(
            array(
              'status'             => 'success',
              'story_id'           => $story_id,
              'published_story_id' => $published_story_id
            )
          );        
        }
      } else{
        echo json_encode(
          array(
            'status'  => 'error',
            'message' => 'Publishing failed'
          )
        ); 
      }

    }

    break;

  case 'story.get':
    $ID = (int) $_REQUEST['id'];

    global $wpdb;
    $tablename = $wpdb->prefix . 'stories';

    $story = $wpdb->get_row("SELECT id, title,  sub_title, content FROM $tablename WHERE id = $ID");

    // parse the json story content string
    $story->content = json_decode($story->content);

    if( !is_null($story) ) {
      echo json_encode(
        array(
          'status' => 'success',
          'story' => $story,
        )
      );
    }
    break;

  case 'stories.all':
    global $wpdb;

    $tablename = $wpdb->prefix . 'stories';

    $stories = $wpdb->get_results("
      SELECT id, title, sub_title, created,  
      (
        SELECT COUNT(id)  FROM wp_published_stories 
        WHERE master_story_id = wp_stories.id LIMIT 0, 1 
      ) as published
      FROM wp_stories ORDER BY created DESC
    ");

    foreach($stories as $key => $story){
      $story = build_story_data($story); // in functions.php
      $stories[$key] = $story;
    }

    echo json_encode(
      array(
        'status' => 'success',
        'stories' => $stories
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
          // create a user token and save it
          $token = sha1(time());
          update_user_meta( $user->data->ID, '_login_token', $token );

          $niceuser = array(
            'id'    => $user->data->ID,
            'name'  => $user->data->display_name,
            'token' => $token
          );

          echo json_encode(
            array(
              'status' => 'success',
              'user' => $niceuser
            )
          );
        } else {
          echo json_encode(
            array(
              'status' => 'error',
              'user' => 'Invalid login'
            )
          );
        }
      }
    } 
    break;

  case 'tokencheck':
    $data = file_get_contents("php://input");
    $data = json_decode($data);

    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){
      if( isset($data->user_id) && isset($data->user_token) ){
        if( $data->user_token == get_user_meta($data->user_id, '_login_token', true) ){
          echo json_encode(
            array(
              'status' => 'success',
              'message' => 'Token is valid',
            )
          );
        } else{
          echo json_encode(
            array(
              'status' => 'error',
              'message' => 'Bad token provided',
            )
          );
        }
      }
    }
    break;

  case 'logout':
    $data = file_get_contents("php://input");
    $data = json_decode($data);

    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){
      if( isset($data->user_id) && isset($data->user_token) ){
        if( $data->user_token == get_user_meta($data->user_id, '_login_token', true) ){
          // clear the token
          update_user_meta($data->user_id, '_login_token', '');

          echo json_encode(
            array(
              'status' => 'success',
              'message' => 'Logged out successfuly',
            )
          );
        }
      }
    }
    break;

  case 'upload_media':
    $required_fields = array('reportid', 'media_type', 'nonce');
    foreach ($required_fields as $key ) {
      if( !array_key_exists($key, $_POST) ){
        echo json_encode(
          array(
            'status' => 'error',
            'message' => 'Upload requires all parameters',
          )
        );
        exit(0);
      }
    }

    if( !empty($_POST) && wp_verify_nonce( $_POST['nonce'], 'timbyweb_front_nonce') == true ){
      
      if (!empty($_FILES['file'])) {

        include_once ABSPATH . '/wp-admin/includes/file.php';
        include_once ABSPATH . '/wp-admin/includes/media.php';
        include_once ABSPATH . '/wp-admin/includes/image.php';
        $attachment_id = media_handle_upload('file', $_POST['reportid']);
        unset($_FILES['file']);

        if (!is_wp_error( $attachment_id ) ) {
          // set the media type as a meta
          update_post_meta($attachment_id, '_media_type', $_POST['media_type']);

          echo json_encode(
            array(
              'status' => 'success',
              'id' => $attachment_id
            )
          );          
        } else {
          echo json_encode(
            array(
              'status' => 'error',
              'message' => 'Error uploading media item'
            )
          );
        }


      } else {
        echo json_encode(
          array(
            'status' => 'error',
            'message' => 'Please attach a file to upload.',
          )
        );
      }

    }

    break;

  case 'detach_media_object':
    $data = file_get_contents("php://input");
    $data = json_decode($data);
    if( !empty($data) && wp_verify_nonce( $data->nonce, 'timbyweb_front_nonce') == true ){

      $args = array(
        'ID'     => $data->media_ID,
        'post_parent' =>  null, //reset the parent of this object
      );

      if( ! is_wp_error(wp_update_post($args, true))){
        echo json_encode(
          array(
            'status' => 'success',
            'message' => 'Media object detached succesfuly'
          )
        );
      }
    }

    break;
  default:
    break;
}


function save_story($data){
  global $wpdb;
  $table = $wpdb->prefix . 'stories';

  $d = array(
    'title'     => $data->story->title,
    'sub_title' => $data->story->sub_title,
    'content'   => json_encode($data->story->content),
  );

  if( isset($data->story->id) ) {
    // perfom an update
    $where = array( 'id' => (int) $data->story->id );
    if( $wpdb->update( $table, $d, $where ) !== false ) {
      return $data->story->id;
    }
  } else {
    // create a new record
    $d['author_id'] = $data->user_id;
    $d['created']   = date('c', time());

    if( $wpdb->insert( $table, $d ) ) {
      return $wpdb->insert_id;
    }
  }

  return false;
}

/**
 * Publish a story
 *
 * checks whether the story has been published before
 * if yes, it does an update if not, it will create a 
 * new record 
 *     
 * @param  int $story_id the master story id
 * @param  array $data story data
 * @return mixed  an published story id or false if the publish fails
 */
function publish_story($story_id, $data){
  global $wpdb;
  $table = $wpdb->prefix . 'published_stories';

  $d = array(
    'title'     => $data->story->title,
    'sub_title' => $data->story->sub_title,
    'content'   => json_encode($data->story->content),
  );
  
  // if story had earlier been published, perfom an update
  $published_story = $wpdb->get_row("SELECT id FROM $table WHERE master_story_id = $story_id");

  if( !is_null($published_story) ) {
    $where = array( 'id' => $published_story->id );
    if( $wpdb->update( $table, $d, $where ) !== false ) {
      return $published_story->id;
    }      
  }

  // create a new record, and reference the master story
  $d['master_story_id']   = $story_id;
  $d['created']   = date('c', time() );

  if( $wpdb->insert( $table, $d ) ) {
    return $wpdb->insert_id;
  }


  return false;
}

// utility functions to fetch various info from the cms
function _get_all_terms(){
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
  return $result;
}

function _get_all_api_users(){
  $args = array(
    'role' => 'timbymobileapp'
  );
  $users = get_users($args);
  $nice_users = array();
  foreach ($users as $user) {
    $nice_users[] = array(
      'id'   => $user->ID,
      'name' => $user->display_name,
    );
  }
  return $nice_users;
}

