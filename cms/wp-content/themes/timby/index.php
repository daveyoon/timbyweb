<!DOCTYPE html>
<html lang="en" ng-app="timby">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{title}}</title>

    <!-- Bootstrap -->
    <link href="<?php echo get_template_directory_uri() ?>/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo get_template_directory_uri() ?>/css/global.css" rel="stylesheet">
  </head>
  <body data-template-url="<?php echo get_template_directory_uri() ?>">

    <div ng-view></div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
  
    <!-- angular core plus modules-->
    <script src="<?php echo get_template_directory_uri() ?>/bower_components/angular/angular.min.js"></script>
    <script src="<?php echo get_template_directory_uri() ?>/bower_components/angular-route/angular-route.min.js"></script>
    
    <script src="<?php echo get_template_directory_uri() ?>/js/controllers.js"></script>
    <script src="<?php echo get_template_directory_uri() ?>/js/directives.js"></script>
    <script src="<?php echo get_template_directory_uri() ?>/js/services.js"></script>
    <script src="<?php echo get_template_directory_uri() ?>/js/app.js"></script>

    <!-- custom site scripts -->
    <script src="<?php echo get_template_directory_uri() ?>/js/build/production.min.js"></script>

  </body>
</html>