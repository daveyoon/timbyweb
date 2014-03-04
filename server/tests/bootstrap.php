<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!file_exists( __DIR__ . '/config.php')) {
  echo 'Please copy config.php.example to config.php and fill in your details';
  die(1);
}
require __DIR__ .'/config.php';

class Slim_Framework_TestCase extends PHPUnit_Framework_TestCase{

  private $testingMethods = array('get', 'post', 'patch', 'put', 'delete', 'head');

  public function setup()
  {

    global $testconfig;

    date_default_timezone_set('Africa/Lagos');

    //bootstrap slim and configure
    $app = new \Slim\Slim(array(
      'version'               => '0.0.0',
      'debug'                 => false,
      'mode'                  => 'testing',
      'wordpress_site_url'    => $testconfig['url'],
      'TEMPORARY_UPLOADS_DIR' => __DIR__ . '/temp_uploads/'
    ));

    require __DIR__ . '/../app/routes/api.php';

    $this->user = $testconfig['username'];
    $this->password = $testconfig['password'];

    // Establish a local reference to the Slim app object
    $this->app = $app;
  }


  // Abstract way to make a request to SlimPHP, this allows us to mock the
  // slim environment
  public function request($method, $path, $formVars = array(), $optionalHeaders = array())
  {
    // Capture STDOUT
    ob_start();

    // Prepare a mock environment
    \Slim\Environment::mock(
      array_merge(
        array(
          'REQUEST_METHOD' => strtoupper($method),
          'PATH_INFO'      => $path,
          'SERVER_NAME'    => 'local.dev',
          'slim.input'     => http_build_query($formVars)
        ),
        $optionalHeaders
      )

    );

    // Establish some useful references to the slim app properties
    $this->request  = $this->app->request();
    $this->response = $this->app->response();

    // Execute our app
    $this->app->run();

    // Return the application output. Also available in `response->body()`
    return ob_get_clean();
  }

  // Implement our `get`, `post`, and other http operations
  public function __call($method, $arguments){
    if( in_array($method, $this->testingMethods) ) {
      list($path, $formVars, $headers) = array_pad($arguments, 3, array()); // make a 3 item array containing necessary items
      $this->request($method, $path,$formVars, $headers); 
    }
  }


}