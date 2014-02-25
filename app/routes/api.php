<?php

/**
 * Success response
 * @param  $message response message
 * @return null
 */
function success($message){
  global $app;
  $app->response->setBody(
    json_encode(
      array(
        'status' => 'OK',
        'message' => $message
      )
    )
  );
}

/**
 * Error response
 * @param  $message response message
 * @param  $code error code
 * @return null
 */
function error($message, $code = '102'){
  global $app;
  $app->response->setBody(
    json_encode(
      array(
        'status'  => 'NOK',
        'error'   => $code,
        'message' => $message
      )
    )
  );
}

// API group
$app->group('/api', function () use ($app) {



  $app->response->headers->set('Content-Type', 'application/json'); //always return json

  /**
   * Login
   * 
   * Params
   *   user_name
   *   password
   *   
   */
  $app->post('/login', function() use ($app) {
    $user_name = $app->request->post('user_name');
    $password = $app->request->post('password');

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/users.authenticate', 
      array('Accept' => 'application/json'), 
      array(
        'username' => $user_name,
        'password' => $password
      )
    );

    $data = json_decode($response->body);
    if( isset($data->user_id) ) {
      success(
        array(
          "user_id" => $data->user_id,
          "token" => $data->token
        )
      );
    } else {
      error('Invalid login credentials');
    }

  });


  /**
   * Create Report
   * 
   * Params
   *   user_id
   *   token
   *   title
   *   description (optional)
   *   category (optional)
   *   sector
   *   report_date (format: Y-m-d h:m:s 2012-02-01 21:12:21)
   *   lat (optional)
   *   long (optional)
   *   key
   *   
   */
  $app->post('/createreport', function() use($app) {
    // check the params provided against required params
    $token = $app->request->post('token');
    $user_id = $app->request->post('user_id');
    $key = $app->request->post('key');

    $title = $app->request->post('title');
    $description = $app->request->post('description');
    $sector = $app->request->post('sector');
    $report_date = $app->request->post('report_date');
    $lat = $app->request->post('lat') != false ? $app->request->post('lat') : 0;
    $long = $app->request->post('long') != false ? $app->request->post('long') : 0;

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/posts/create_post',
      array('Accept' => 'application/json'), 
      array(
        'title'   => $title,
        'content' => $description,
        'author'  => $user_id,
        'date'    => date('c', strtotime($report_date)),
        'type'    => 'report',
        'status'  => 'pending',
        
        'token'   => $token
      )
    );

    $data = json_decode($response->body);
    if( isset($data->post) ) {
      success(
        array(
          'id' => $data->post->id
        )
      );
    }

  });

  /**
   * Logout the current user
   * 
   * Params
   *   username
   *   password
   *   
   */
  $app->post('/logout', function() use($app) {

    $username = $app->request->post('user_name');
    $password = $app->request->post('password');

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/users.logout',
      array('Accept' => 'application/json'), 
      array(
        'username' => $username,
        'password' => $password
      )
    );

    if($response->body == 'true'){
      success('User logged out successfuly');
    } else {
      error('Unknown error occured while trying to logout.');
    }

  });


  /**
   * Checks to see the status of a token (if still valid). 
   * No API Key required for this (the only function that will not require a key, 
   * which is outside of login and logout).
   * 
   * Params
   *   user_id
   *   token
   *   
   */
  $app->post('/tokencheck', function() use($app) {

    $user_id = $app->request->post('user_id');
    $token = $app->request->post('token');

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/users.tokenstatus',
      array('Accept' => 'application/json'), 
      array(
        'user_id' => $user_id,
        'token' => $token
      )
    );

    $data = json_decode($response->body);
    if(isset($data->token_status) ) {
      if($data->token_status == "valid") {
        success('Token is valid');
      } else {
        error('Invalid token');
      }
    } else {
      error('User was not found');
    }

  });


  /**
   * Update an existing report
   *
   * Params
   *   user_id
   *   key
   *   token
   *   title (optional)
   *   description (optional)
   *   category (optional)
   *   sector (optional )
   *   report_date (format: Y-m-d h:m:s 2012-02-01 21:12:21) (optional)
   *   lat (optional)
   *   long (optional)
   *   report_id
   */
  $app->post('/updatereport', function() use($app){
    // first add the mandatory parameters
    $params = array(
      'user_id' => $_POST['user_id'],
      'key'     => $_POST['key'],
      'token'   => $_POST['token'],
      'post_id' => $_POST['report_id'] //report id is our post id
    );

    // all other optional parameters for hte update
    $_optional_params = array(
      "title","description", "category", 
      "sector", "report_date", "lat", "long"
    );

    foreach($_optional_params as $param) {
      if(array_key_exists($param, $_POST)) {
        $params[$param] = $_POST[$param];
      }
    }

    $params['post_type'] = 'report';

    // response
    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/posts/update_post',
      array('Accept' => 'application/json'), 
      $params
    );

    $responsebody = json_decode($response->body);
    if( $responsebody->status == "ok" ){
      // return the report for convenience
      success(
        array(
          'report_id' => $responsebody->post->id,
          'title' => $responsebody->post->title,
          'description' => $responsebody->post->content
        )
      );
    } else {
      error($responsebody->error);
    }

  });
  
  /**
   * Delete an existing report
   *
   * Params
   *   user_id
   *   key
   *   token
   *   report_id
   */
  $app->post('/deletereport', function() use($app){
    // mandatory params
    $params = array(
      'user_id' => $_POST['user_id'],
      'key'     => $_POST['key'],
      'token'   => $_POST['token'],
      'post_id' => $_POST['report_id'] //report id is our post id
    );

    $params['post_type'] = 'report';
    
    // response
    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/posts/delete_post',
      array('Accept' => 'application/json'), 
      $params
    );

    $responsebody = json_decode($response->body);
    if( $responsebody->status == 'ok'){
      success('Report deleted');
    } else {
      error($responsebody->error);
    }


  });

});



