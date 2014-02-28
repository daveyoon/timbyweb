<?php
// set the mode based on the host
switch ($_SERVER['HTTP_HOST']) {
  case 'localhost':
    $_ENV['SLIM_MODE'] = 'development';
    break;
  
  case 'uat.circle.co.ke':
    $_ENV['SLIM_MODE'] = 'uat';
    break;

  case 'timby.org':
    $_ENV['SLIM_MODE'] = 'production';
    break;
}

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
  $app->config(array(
    'log.enable' => false,
    'debug' => true,
    'wordpress_site_url' => 'http://localhost/timbyweb/cms'
  ));
});

// Only invoked if mode is "uat"
$app->configureMode('uat', function () use ($app) {
  $app->config(array(
    'log.enable' => true,
    'debug' => false,
    'wordpress_site_url' => 'http://uat.circle.co.ke/timbyweb/cms'
  ));
});






