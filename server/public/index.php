<?php
require '../vendor/autoload.php';
require '../app/bootstrap.php';
require '../app/config.php';
# Automatically load router files
$routers = glob('../app/routes/*.php');
foreach ($routers as $router) {
    require $router;
}

# Configure the uploads directory
$app->config(array(
  'TEMPORARY_UPLOADS_DIR' => dirname(__FILE__) . '/temp_uploads/',
));

$app->run();

