<?php 

class LoginTest extends Slim_Framework_TestCase
{

  public function testLoginInvalid()
  {
    $response = $this->login_to_wordpress('somebaduser', 'somebadpass');

    $this->assertEquals(200, $response['status']);
    $this->assertEquals('Invalid login credentials', $response['data']);
  }

  public function testLoginValid()
  {
    $response = $this->login_to_wordpress($this->user, $this->password);

    $this->assertEquals(200, $response['status']);
    $this->assertObjectHasAttribute('key', $response['data']);

    return $response['data'];
  }



}