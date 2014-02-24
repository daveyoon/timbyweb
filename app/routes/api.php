<?php

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
      $app->response->setBody(
        json_encode(
          array(
            'status' => 'OK',
            'message' => array(
              "user_id" => $data->user_id,
              "token" => $data->token
            )
          )
        )
      );
    } else {
      $app->response->setBody(
        json_encode(
          array(
            'status' => 'NOK',
            'error' => '100',
            'message' => 'Invalid login credentials'
          )
        )
      );
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
        'title' => $title,
        'content' => $description,
        // 'category' => $category,
        // 'sector' => $sector,
        'date' => date('c', strtotime($report_date)),
        // 'lat' => $lat,
        // 'long' => $long,
        // 'key' => $key,
        'author' => $user_id,
        'type' => 'report',
        'status' => 'pending'
      )
    );
    print_r($response);
    
  });

});



