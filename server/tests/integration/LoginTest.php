<?php 

class LoginTest extends Slim_Framework_TestCase
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

  public function testLoginValid()
  {
    $this->post('/api/login',
      array(
        'user_name' => $this->user,
        'password'  => $this->password
      )
    );

    $this->assertEquals(200, $this->response->status());
    $this->assertObjectHasAttribute('key', json_decode($this->response->body())->message);
  }

}