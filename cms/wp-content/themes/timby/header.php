<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <title><?php wp_title(''); ?><?php
            if (wp_title('', false)) {
                echo ' :';
            }
            ?> <?php bloginfo('name'); ?></title>

        <link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
        <link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">
        <link href="<?php echo get_template_directory_uri(); ?>/css/frontend.css" rel="stylesheet" type="text/css">
        <!-- <link rel="stylesheet" href="http://basehold.it/24"> -->

        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <meta name="description" content="<?php bloginfo('description'); ?>">
        <script type="text/javascript">
            window.Config = <?php echo get_timby_options_json(); ?>;
        </script>

<?php wp_head(); ?>

    </head>
    <body <?php body_class(); ?>>
        <header class="front-header">
            <div class="row-big">
                <div class="logo-front pull-left">
                    <a href="/timbyweb/cms/">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/logo-orange.png" alt="" width="55" height="55">
                    </a>
                </div>
                <?php $args = array(    
                'include'     => '135,137,146');
                ?>
                <?php wp_page_menu( $args); ?>
            </div>
            
        </header>
