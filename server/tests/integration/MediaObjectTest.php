<?php

  class MediaObjectTest extends Slim_Framework_TestCase
  {
    public function testLoginValid()
    {
      $response = $this->login_to_wordpress($this->user, $this->password);

      $this->assertEquals(200, $response['status']);
      $this->assertObjectHasAttribute('key', $response['data']);

      return $response['data'];
    }

    
    /**
     * @depends testLoginValid
     */
    public function testUploadMedia($user)
    {
      //create a report
      $this->post('/api/createreport', 
        array(
          'title'   => 'Sample Test Report',
          'content' => 'Sample report Description',
          'author'  => 1,
          'date'    => '2014-02-05 16:46:03',
          'type'    => 'report',
          'status'  => 'pending',
          'token'   => 'asdf',
          'key'     => 'asdf'
        )
      );

      $reportid = json_decode( $this->response->body())->message->id;

      if( $reportid )
      {
        // create a media object and tie it to this report
        $params = array(
          'user_id'     => $user->user_id,
          'key'         => $user->key,
          'token'       => $user->token,
          'title'       => 'Sample media object',
          'object_type' => 'image',
          'narrrative'  => 'here is a sample attachment',
          'report_id'   => $report_id,
        );

        $this->post('/api/insertobject', $params);

        $this->assertEquals(200, $this->response->status());
        $this->assertObjectHasAttribute('object_id', json_decode($this->response->body())->message);
        
      }

    }



  }