<?php

/*------------------------------------------------------------------
[Custom Post Types]

* Report
* Devices
-------------------------------------------------------------------*/

add_action( 'init', 'register_custom_post_type_report' );
function register_custom_post_type_report() {

    $labels = array( 
        'name' => _x( 'Reports', 'timbyweb' ),
        'singular_name' => _x( 'Report', 'timbyweb' ),
        'add_new' => _x( 'Add New', 'timbyweb' ),
        'add_new_item' => _x( 'Add a new report', 'timbyweb' ),
        'edit_item' => _x( 'Edit report', 'timbyweb' ),
        'new_item' => _x( 'New report', 'timbyweb' ),
        'view_item' => _x( 'View report', 'timbyweb' ),
        'search_items' => _x( 'Search Report', 'timbyweb' ),
        'not_found' => _x( 'No report found', 'timbyweb' ),
        'not_found_in_trash' => _x( 'No report found in Trash', 'timbyweb' ),
        'parent_item_colon' => _x( 'Parent report:', 'timbyweb' ),
        'menu_name' => _x( 'Reports', 'timbyweb' )
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Report',
        'supports' => array( 'title', 'editor'),
        'taxonomies' => array('category'),

        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-format-status'
    );

    register_post_type( 'report', $args );

}




add_action( 'init', 'register_custom_post_type_phone' );
function register_custom_post_type_phone() {

    $labels = array( 
        'name' => _x( 'Phones', 'timbyweb' ),
        'singular_name' => _x( 'Phone', 'timbyweb' ),
        'add_new' => _x( 'Add New', 'timbyweb' ),
        'add_new_item' => _x( 'Add a new phone', 'timbyweb' ),
        'edit_item' => _x( 'Edit phone', 'timbyweb' ),
        'new_item' => _x( 'New phone', 'timbyweb' ),
        'view_item' => _x( 'View phone', 'timbyweb' ),
        'search_items' => _x( 'Search Phone', 'timbyweb' ),
        'not_found' => _x( 'No phone found', 'timbyweb' ),
        'not_found_in_trash' => _x( 'No phone found in Trash', 'timbyweb' ),
        'parent_item_colon' => _x( 'Parent phone:', 'timbyweb' ),
        'menu_name' => _x( 'Phone Devices', 'timbyweb' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'description' => 'Devices on the ground',
        'supports' => array( 'title'),
        
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        
        
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-smartphone'
    );

    register_post_type( 'phone', $args );

}