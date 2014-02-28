<?php
class ApiLogTest extends Slim_Framework_TestCase
{

  public function testLogActivity(){
    global $testconfig,$http_response_header;

    //mock the server
    $_SERVER = array(
      'REQUEST_URI'     => '/timbyweb/server/public/api/insertobject',
      'REQUEST_METHOD'  => 'POST',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.117 Safari/537.36',
      'SERVER_ADDR'     => '::1'
    );


    $this->post('/api/login', 
      array(
        'username' => 'someusername',
        'password' => 'somepass',
      )
    );

    $expected_log = array(
      'request' => array(
        'url'        => $_SERVER['REQUEST_URI'],
        'method'     => $_SERVER['REQUEST_METHOD'],
        'parameters' => $_REQUEST,
        'time'       => time(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'ip'         => $_SERVER['SERVER_ADDR'],
      ),
      'response' => array(
        'body'    => $this->response->body(),
        'headers' => $http_response_header,
        'time'    => time(),
      ),
    );


    $actual_log = $this->app->config('app.log');
    
    $this->assertTrue($expected_log['request']['url'] == $actual_log['request']['url']);
    $this->assertSame(
      $expected_log['response']['body'],
      $actual_log['response']['body']
    );


  }

}