<?php

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'log.enable' => false,
    'debug' => true,
    'wordpress_site_url' => 'http://localhost/wordpress/',
    'wordpress_site_username' => 'admin',
    'wordpress_site_password' => 'admin'
  ));
});

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
  $app->config(array(
    'log.enable' => true,
    'debug' => false,
    'wordpress_site_url' => 'http://circle.co.ke/timbyweb/wordpress',
    'wordpress_site_username' => '',
    'wordpress_site_password' => ''
  ));
});




// set the mode based on the host
switch ($_SERVER['HTTP_HOST']) {
  case 'localhost':
    $_ENV['SLIM_MODE'] = 'development';
    break;
  
  case 'circle.co.ke':
    $_ENV['SLIM_MODE'] = 'test';
    break;

  case 'timby.org':
    $_ENV['SLIM_MODE'] = 'production';
    break;
}



