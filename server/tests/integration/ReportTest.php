<?php

  class ReportTest extends Slim_Framework_TestCase
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