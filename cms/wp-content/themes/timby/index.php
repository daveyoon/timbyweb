<!DOCTYPE html>
<html lang="en" ng-app="timby">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{title}}</title>

    <!-- Bootstrap -->
    <!-- <link href="<?php echo get_template_directory_uri() ?>/css/bootstrap.css" rel="stylesheet"> -->
    <link href="<?php echo get_template_directory_uri() ?>/css/global.css" rel="stylesheet">

    <?php wp_head() ?>
  </head>
  <body data-template-url="<?php echo get_template_directory_uri() ?>" controller="MainController">
    <header class="clearfix">
        <div class="pull-left">
          <h3>TIMBY</h3>
        </div>
        <div class="pull-right">
          <button>Logout</button>
        </div>
    </header>
    <nav class="tabs">
        <li class="tab-item active"><a href="#">Moderation</a></li>
        <li class="tab-item"><a href="#">Create a Story</a></li>
        <li class="tab-item"><a href="#">Add Report</a></li>
    </nav>
    <div ng-view></div>

    <?php wp_footer(); // this loads jQuery, Angular and our Angular app, see functions.php ?>

    <!-- Load any other custom scripts below -->
    <script src="<?php echo get_template_directory_uri() ?>/js/build/production.min.js"></script>


  </body>
</html>