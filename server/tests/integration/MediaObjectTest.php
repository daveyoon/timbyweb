<?php

  class MediaObjectTest extends Slim_Framework_TestCase
  {
    public function testUploadMedia()
    {
      global $testconfig;

      //login
      $response = Requests::post(
        $testconfig['url'].'/api/users.authenticate', 
        array('Accept' => 'application/json'), 
        array(
          'username' => $testconfig['username'],
          'password' => $testconfig['password']
        )
      );
      $user = json_decode($response->body);

      //create a report
      $response = Requests::post(
        $testconfig['url'].'/api/posts/create_post',
        array('Accept' => 'application/json'), 
        array(
          'title'   => 'Sample title',
          'content' => 'Sample test description',
          'author'  => $user->user_id,
          'date'    => '2014-02-09 13:56:00:00',
          'type'    => 'report',
          'status'  => 'pending',
          'token'   => $user->api_token
        )
      );

      $report = json_decode( $response->body);

      if( $report->post )
      {
        // create a media object and tie it to this report
        $params = array(
          'user_id'     => $user->user_id,
          'key'         => $user->api_key,
          'token'       => $user->api_token,
          'title'       => 'Sample media object',
          'object_type' => 'image',
          'narrrative'  => 'here is a sample attachment',
          'report_id'   => $report->post->id,
        );

        $params['attachment'] = '@'. __DIR__ . '/_files/test.jpg'; // attachment will be available to wordpress in $_FILES

        //mock the upload
        $ch = curl_init($testconfig['server_url'].'/api/insertobject');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        echo $response;

        $this->assertObjectHasAttribute('object_id', json_decode($response)->message);
        
      }

    }

  }