<?php 

/**
 * Custom Post types
 */
require get_template_directory() . '/inc/custom-post-types.php';

/**
 * Custom User Roles
 */
require get_template_directory() . '/inc/custom-user-roles.php';

/**
 * Custom Taxonomies
 */
require get_template_directory() . '/inc/custom-taxonomies.php';


if ( ! function_exists( 'timbyweb_setup' ) ) :
  /**
   * Sets up theme defaults and registers support for various WordPress features.
   *
   * Note that this function is hooked into the after_setup_theme hook, which runs
   * before the init hook. The init hook is too late for some features, such as indicating
   * support post thumbnails.
   */
  function timbyweb_setup() {

    /**
     * Make theme available for translation
     * Translations can be filed in the /languages/ directory
     * If you're building a theme based on timbyweb, use a find and replace
     * to change 'timbyweb' to the name of your theme in all the template files
     */
    load_theme_textdomain( 'timbyweb', get_template_directory() . '/languages' );

    /**
     * Add default posts and comments RSS feed links to head
     * Add post thumbnails
     */
    add_theme_support( 'automatic-feed-links', 'post-thumbnails' );

  }
endif; // timbyweb_setup
add_action( 'after_setup_theme', 'timbyweb_setup' );