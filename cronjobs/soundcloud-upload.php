#!/usr/bin/php
<?php

// get the database config details from wordpress
if( ! file_exists( __DIR__ . '/../cms/wp-load.php') ){
  die('unable to find a wordpress installation, please install wordpress in the cms directory');
}

require_once __DIR__ . '/../cms/wp-load.php';

# The vimeo LIB
require_once __DIR__ . '/../server/app/vendor/vimeo/vimeo.php';
require_once __DIR__ . '/../server/app/vendor/soundcloud/Services/Soundcloud.php';

require 'config.php';
