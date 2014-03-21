<?php

/**
 * Define the metabox and field configurations.
 * these metaboxes will be visible to specific pages
 * tagged with predefined page templates
 * 
 * @param  array $meta_boxes
 * @return array
 */
function cmb_sample_metaboxes( array $meta_boxes ) {

    // Start with an underscore to hide fields from custom fields list
    $prefix = '_cmb_';

    /**
     * Phone fields
     *   - phone number
     *   - phone ID/IMEI
     *   - phone make/model
     *   
     * Post type : Phone
     */
    $meta_boxes[] = array(
        'id'         => 'phone_details',
        'title'      => 'Phone Details',
        'pages'      => array( 'phone' ), // Post type
        'context'    => 'normal',
        'priority'   => 'high',
        'show_names' => true, // Show field names on the left
        'fields'     => array(
          array(
            'name' => 'Phone Number',
            'desc' => 'e.g +231776696035',
            'id'   => $prefix . 'number',
            'type' => 'text',
          ),
          array(
            'name' => 'Phone ID/IMEI',
            'desc' => 'The phone IMEI number',
            'id'   => $prefix . 'imei',
            'type' => 'text',
          ),
          array(
            'name' => 'Phone make and model',
            'desc' => 'e.g Samsung Galaxy S3',
            'id'   => $prefix . 'model',
            'type' => 'text',
          )
        ),
    );    


    /**
     * Verified Status
     *   - verified
     *   
     * Post type : Phone
     */
    $meta_boxes[] = array(
      'id'         => 'verification_status',
      'title'      => 'Verification',
      'pages'      => array( 'report' ), // Post type
      'context'    => 'normal',
      'priority'   => 'high',
      'show_names' => true, // Show field names on the left
      'fields'     => array(
        array(
          'name' => 'Verified',
          'desc' => 'Has this story been verified?',
          'id'   => $prefix . 'verified',
          'type' => 'checkbox'
        )
      ),
    );


    
    // Add other metaboxes as needed
    return $meta_boxes;
}



add_filter( 'cmb_meta_boxes', 'cmb_sample_metaboxes' );


// require more complex metaboxes
require 'custom-report-metabox/metabox.php';



