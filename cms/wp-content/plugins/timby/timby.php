<?php
/**
 * Plugin Name: Timby
 * Plugin URI: http://timby.org
 * Description: Plugin for Timby reporting application
 * Version: 0.5
 * Author: Circle
 * Author URI: http://circle.co.ke
 * License: GPL2
 */

require_once __DIR__ . '/src/Timby.php';
require_once __DIR__ . '/src/API/Singletons/introspector.php';


$timby = new Timby();

// not cool but that's the way for now ;)
global $json_api;
@$json_api->introspector = new JSON_API_Timby_Introspector();

register_activation_hook(__FILE__, array($timby, 'checkRequirements'));

add_filter('json_api_timby_controller_path', array($timby, 'TimbyCoreAPIControllerPath'));
add_filter('json_api_timbyposts_controller_path', array($timby, 'TimbyPostsAPIControllerPath'));
add_filter('json_api_timbyusers_controller_path', array($timby, 'TimbyUsersAPIControllerPath'));
add_filter('json_api_controllers', array($timby, 'addAPIControllers'));
