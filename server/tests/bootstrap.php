<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!file_exists( __DIR__ . '/config.php')) {
  echo 'Please copy config.php.example to config.php and fill in your details';
  die(1);
}
require __DIR__ .'/config.php';

class Slim_Framework_TestCase extends PHPUnit_Framework_TestCase{

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


  /**
   * Login to wordpress and return the user's details
   * @return array user object containing user_name, key and token
   */
  public function login_to_wordpress($user = '', $password = '')
  {
    $params = array(
      'user_name' => $user,
      'password'  => $password
    );

    $this->post('/api/login', $params);

    return array(
      'data'   => json_decode($this->response->body())->message,
      'status' => $this->response->status()
    );
  }

  // Abstract way to make a request to SlimPHP, this allows us to mock the
  // slim environment
  public function request($method, $path, $options = array())
  {
    // Capture STDOUT
    ob_start();

    // Prepare a mock environment
    \Slim\Environment::mock(array(
        'REQUEST_METHOD' => $method,
        'PATH_INFO'      => $path,
        'SERVER_NAME'    => 'local.dev',
        'QUERY_STRING'   => http_build_query($options)
    ));

    // Establish some useful references to the slim app properties
    $this->request  = $this->app->request();
    $this->response = $this->app->response();

    // Execute our app
    $this->app->run();

    // Return the application output. Also available in `response->body()`
    return ob_get_clean();
  }

  public function get($path, $options = array())
  {
      return $this->request('GET', $path, $options);
  }

  public function post($path, $options = array(), $postVars = array())
  {
    $options['slim.input'] = http_build_query($postVars);
    return $this->request('POST', $path, $options);
  }

  public function patch($path, $options = array(), $postVars = array())
  {
    $options['slim.input'] = http_build_query($postVars);
    return $this->request('PATCH', $path, $options);
  }

  public function put($path, $options = array(), $postVars = array())
  {
    $options['slim.input'] = http_build_query($postVars);
    return $this->request('PUT', $path, $options);
  }

  public function delete($path, $options = array())
  {
    return $this->request('DELETE', $path, $options);
  }

  public function head($path, $options = array())
  {
    return $this->request('HEAD', $path, $options);
  }

}