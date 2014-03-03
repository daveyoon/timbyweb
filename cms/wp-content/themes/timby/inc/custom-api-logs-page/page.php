<?php
/**
 *Enqueue scripts and styles
 */
function custom_api_log_scripts() {
  wp_register_style( 'api-log-css', get_template_directory_uri() . '/inc/custom-api-logs-page/css/styles.css', false, '1.0.0' );
  
  wp_enqueue_style( 'api-log-css' );
  wp_enqueue_style('jquery-ui-styles',get_template_directory_uri().'/inc/custom-api-logs-page/css/jquery-ui.css',false, '', false);

  wp_enqueue_script( 'api-log-script', get_template_directory_uri().'/inc/custom-api-logs-page/js/script.js', array('jquery','underscore','jquery-ui-core', 'jquery-ui-datepicker'), false, true);

}

add_action( 'admin_enqueue_scripts', 'custom_api_log_scripts' );


add_action('admin_menu', 'timby_api_logs_menu');
function timby_api_logs_menu(){
  add_menu_page(
    __('API Activity'), // page title
    __('API Activity'), // menu title
    'read', //capability
    'api_activity', //menu slug
    'page_output', //page output function
    'dashicons-chart-bar',
    25
  );
}

function page_output(){
  global $wpdb;

  // default range
  $range = array(
    'start' => date('Y-m-d', strtotime('1 month ago') ),
    'end' => date('Y-m-d', strtotime('today') ),
  );

  if ( !empty($_POST) && check_admin_referer('do_filter_date','filter_date') )
  {
    $range = array(
      'start' => date('Y-m-d', strtotime($_POST['start_date']) ),
      'end' => date('Y-m-d', strtotime($_POST['end_date']) ),
    );
  }

  $logs = $wpdb->get_results("
    SELECT log, DATE_FORMAT(created_at, '%e %b %Y') as created_at from timbyapi_logs
    WHERE (created_at BETWEEN '".$range['start']."' AND DATE_ADD('".$range['end']."', INTERVAL 1 DAY) )
    ORDER BY created_at desc
  ");

  require_once __DIR__ . '/views/index.php';
}