<?php

class Timby_Configuration
{
    public static function getOptions()
    {
        return array(
            'google_analytics_id' => array(
                'type' => 'text',
                'label' => 'Google Analytics tracking ID'
            ),
            'cartodb_key' => array(
                'type' => 'text',
                'label' => 'CartoDB Key'
            ),
            'cartodb_secret' => array(
                'type' => 'text',
                'label' => 'CartoDB Secret'
            ),
            'cartodb_email' => array(
                'type' => 'text',
                'label' => 'CartoDB email'
            ),
            'cartodb_password' => array(
                'type' => 'text',
                'label' => 'CartoDB Password'
            ),
            'cartodb_subdomain' => array(
                'type' => 'text',
                'label' => 'CartoDB Subdomain'
            ),
            'cartodb_visualisation_api_url' => array(
                'type' => 'text',
                'label' => 'CartoDB visualization URL'
            ),
            'vimeo_client_key' => array(
                'type' => 'text',
                'label' => 'Vimeo Client ID'
            ), //client id
            'vimeo_client_secret' => array(
                'type' => 'text',
                'label' => 'Vimeo Client Secret'
            ), //client secret
            'vimeo_access_token' => array(
                'type' => 'text',
                'label' => 'Vimeo Access Token'
            ), //access token,
            'vimeo_access_token_secret' => array(
                'type' => 'text',
                'label' => 'Vimeo Token Secret'
            ),
            'soundcloud_client_key' => array(
                'type' => 'text',
                'label' => 'SoundCloud Client Key'
            ),
            'soundcloud_client_secret' => array(
                'type' => 'text',
                'label' => 'SoundCloud Client Secret'
            ),
            'soundcloud_username' => array(
                'type' => 'text',
                'label' => 'SoundCloud Username'
            ),
            'soundcloud_password' => array(
                'type' => 'text',
                'label' => 'SoundCloud Password'
            ),
            'amazons3_access_key' => array(
                'type' => 'text',
                'label' => 'Amazon S3 access key'
            ),
            'amazons3_access_secret' => array(
                'type' => 'text',
                'label' => 'Amazon S3 access secret'
            ),
        );
    }

    public static function getPublicOptions()
    {
        return array(
            'google_analytics_id',
            'cartodb_key',
            'cartodb_secret',
            'cartodb_email',
            'cartodb_password',
            'cartodb_subdomain'
        );
    }
} 
