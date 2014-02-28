<?php

/**
 * Success response
 * @param  $message response message
 * @return null
 */
if( !function_exists('success')){
  function success($message){
    echo  json_encode(
            array(
              'status' => 'OK',
              'message' => $message
            )
          );
  }  
}


/**
 * Error response
 * @param  $message response message
 * @param  $code error code
 * @return null
 */
if( !function_exists('error')){
  function error($message, $code = '102'){
    echo  json_encode(
            array(
              'status'  => 'NOK',
              'error'   => $code,
              'message' => $message
            )
          );
  }
}

/**
 * Upload hook
 * 
 *  Does a few things here
 *  1. Uploading the file to a temporary location
 *  2. Forwarding the uploaded file to the Wordpress api
 *  3. Returning a response
 *  4. Deleting the temporary file
 *   
 * @return null
 */

$app->hook('upload', function ($params) use($app) {

  // upload the file
  $upload_file_path = $app->config('TEMPORARY_UPLOADS_DIR') . $_FILES['attachment']['name'];

  if(move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_file_path)){
    $params['attachment'] = '@'. $upload_file_path; // attachment will be available to wordpress in $_FILES

    //upload the attachment
    $ch = curl_init($app->config('wordpress_site_url').'/api/posts/create_attachment');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);

    $responsebody = json_decode($response);
    if( $responsebody->status == 'ok' ){
      success(
        array( 
          'object_id' => $responsebody->id
        )
      );
    } else {
      error($responsebody->error);
    }
    // unset the temporarily uploaded file
    unlink($upload_file_path);
  } else {
    error('Unable to upload the file');
  }

});


$app->hook('slim.before', function() use($app){
  $logdata = array(
    'request' => array(
      'url'        => $_SERVER['REQUEST_URI'],
      'method'     => $_SERVER['REQUEST_METHOD'],
      'parameters' => $_REQUEST,
      'time'       => time(),
      'user_agent' => $_SERVER['HTTP_USER_AGENT'],
      'ip'         => $_SERVER['SERVER_ADDR'],
    )
  );
  $app->config('app.log', $logdata);
});

$app->hook('slim.after', function() use($app){
  global $http_response_header;
  $logdata = $app->config('app.log');
  $logdata['response'] = array(
                            'body'    => $app->response->body(),
                            'headers' => $http_response_header,
                            'time'    => time(),
                        );
  $app->config('app.log', $logdata);

  // write log to file or database
  if(  $file = fopen( $app->config('PUBLIC_FOLDER') . '/log.txt', 'a' ) ) {
    fwrite($file, json_encode($logdata) ."\r\n");
  }
  

});


/**
*  
*         THE API
*  Api calls are grouped in /api
*                                                 
**********************************************/

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
    $user_name = $app->request->params('user_name');
    $password = $app->request->params('password');

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
          "key"     => $data->api_key,
          "token"   => $data->api_token
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
    $token = $app->request->params('token');
    $user_id = $app->request->params('user_id');
    $key = $app->request->params('key');

    $title = $app->request->params('title');
    $description = $app->request->params('description');
    $sector = $app->request->params('sector');
    $report_date = $app->request->params('report_date');
    $lat = $app->request->params('lat') != false ? $app->request->params('lat') : 0;
    $long = $app->request->params('long') != false ? $app->request->params('long') : 0;

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

    $responsebody = json_decode($response->body);
    if( isset($responsebody->post) ) {
      success(
        array(
          'id' => $responsebody->post->id
        )
      );
    } else {
      error($responsebody->error);
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

    $username = $app->request->params('user_name');
    $password = $app->request->params('password');

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

    $user_id = $app->request->params('user_id');
    $token = $app->request->params('token');

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


  /**
   * Get categories
   *
   * Params
   *   user_id
   *   key
   *   token
   */
  $app->post('/getcategories', function() use($app){
    // grab the params but we don't actually use them
    // this call doesn't require authenticating the user
    // since categories are basically available to the public
    $params = array(
      'user_id' => $_POST['user_id'],
      'key'     => $_POST['key'],
      'token'   => $_POST['token']
    );

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/get_all_terms_for_taxonomy',
      array('Accept' => 'application/json'),
      array(
        'taxonomy' => 'category'
      )
    );

    $responsebody = json_decode($response->body);
    if( $responsebody->terms){
      success(
        array( 
          'categories' => $responsebody->terms
        )
      );
    }

  });


  /**
   * Get sectors, similar to /getcategories
   *
   * Params
   *   user_id
   *   key
   *   token
   */
  $app->post('/getsectors', function() use($app){

    $response = Requests::post(
      $app->config('wordpress_site_url').'/api/get_all_terms_for_taxonomy',
      array('Accept' => 'application/json'),
      array(
        'taxonomy' => 'sector'
      )
    );

    $responsebody = json_decode($response->body);
    if( $responsebody->terms ){
      success(
        array( 
          'sectors' => $responsebody->terms
        )
      );
    }

  });




  /**
   * Insert an object, creates a wordpress media object
   * 
   * user_id
   * key
   * token
   * title (optional)
   * object_type : one of (“narrative”, “image”, “video”, “audio”, “entity”)
   * narrative
   * report_id
   * (The object to upload - if it is uploadable)
   */
  $app->post('/insertobject', function() use($app){

    $params = array(
      'author'     => $app->request->params('user_id'),
      'key'        => $app->request->params('key'),
      'token'      => $app->request->params('token'),
      'title'      => $app->request->params('title'),
      'content'    => $app->request->params('narrative'),
      'id'         => $app->request->params('report_id')
    );

    // we have to upload the file here before passing it on to wordpress
    // see http://stackoverflow.com/questions/13928747/sending-files-information-to-another-script-using-curl
    if( in_array($app->request->params('object_type'), array('application/octet-stream','image','audio','video')))
    {
      switch( $app->request->params('object_type'))
      {
        case 'image':
          if( in_array($_FILES['attachment']['type'], array('application/octet-stream','image/jpeg', 'image/png') ) )
            $app->applyHook('upload', $params);
          else
            error('Please upload a valid image type');
            return;

          break;
        case 'video':
          
          if( in_array($_FILES['attachment']['type'], array('application/octet-stream','video/mp4', 'video/ogg','video/webm', 'video/x-flv') ) )
            $app->applyHook('upload', $params);
          else 
            error('Please upload a valid video type');
            return;
          
          break;
        case 'audio':

          if( in_array($_FILES['attachment']['type'], array('audio/mp3','audio/mp4', 'audio/ogg') ) )
            $app->applyHook('upload', $params);
          else
            error('Please upload a valid audio type');
            return;

          break;
        default:
          break;        
      }

    }

  });

});


