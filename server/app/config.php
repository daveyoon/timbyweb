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


// get the database config details from the cms
require_once __DIR__ . '/../../cms/wp-config.php';
use Illuminate\Database\Capsule\Manager as Capsule;
$capsule = new Capsule;

// Database Connection,
// Settings borrowed from the wordpress' config.php
$capsule->addConnection(
  array(
    'driver'    => 'mysql',
    'host'      => DB_HOST,
    'database'  => DB_NAME,
    'username'  => DB_USER,
    'password'  => DB_PASSWORD,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
  )
);

// Make this Capsule instance available globally via static methods
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// create a `timbyapi_logs` schema
if( !Capsule::schema()->hasTable('timbyapi_logs') ) {
  Capsule::schema()->create('timbyapi_logs', function($table)
  {
    $table->increments('id');
    $table->text('log');
    $table->timestamps();
  });
}
