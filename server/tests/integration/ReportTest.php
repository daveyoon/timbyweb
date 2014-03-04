<?php

  class ReportTest extends Slim_Framework_TestCase
  {
    
    public function testLoginInvalid()
    {
      $this->post('/api/login',
        array(
          'user_name' => 'invaliduser',
          'password'  => 'invalidpassword'
        )
      );

      $this->assertEquals(200, $this->response->status());
      $this->assertEquals('Invalid login credentials', json_decode($this->response->body())->message);
    }
    
    /**
     * @depends testLoginValid
     */
    public function testCreateReport($user)
    {
      //create a report
      $this->post('/api/createreport', 
        array(
          'title'       => 'Sample Test Report',
          'description' => 'Sample report Description',
          'sector'      => 1,
          'report_date' => '2014-02-05 16:46:03',
          'type'        => 'report',
          'status'      => 'pending',
          'user_id'     => $user->user_id,
          'token'       => $user->token,
          'key'         => $user->key
        )
      );
      $this->assertEquals(200, $this->response->status());

      $this->assertObjectHasAttribute('id', json_decode($this->response->body())->message);

    }



  }