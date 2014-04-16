<?php get_header(); ?>
	
	<!-- section -->
	<section role="main">
	
	<?php if (have_posts()): while (have_posts()) : the_post(); ?>
	
		<!-- article -->
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		
		
			<!-- post title -->
			<h1>
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</h1>
			<!-- /post title -->
			
			
		</article>
		<!-- /article -->
		
	<?php endwhile; ?>
	<?php endif; ?>
	
	</section>
	<!-- /section -->

<?php get_footer(); ?>