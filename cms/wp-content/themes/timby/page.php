<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package timby
 */
 get_header(); 
 
 ?>

	
<!-- section -->
<section role="main" class="row">
	
	<h1><?php the_title(); ?></h1>
	
	<?php while (have_posts()) : the_post(); ?>
		<?php get_template_part('content', 'page'); ?>		
	<?php endwhile; //end of loop ?>
</section>
<!-- /section -->

<?php get_footer(); ?>