<?php
require '../vendor/autoload.php';
require '../app/bootstrap.php';
require '../app/config.php';

# Automatically load router files
$routers = glob('../app/routes/*.php');
foreach ($routers as $router) {
    require $router;
}

$app->run();

