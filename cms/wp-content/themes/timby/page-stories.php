<?php
/**
 * The template for displaying stories
 *
 *
 * @package timby
 */
 get_header(); ?>
  
<!-- section -->
<section role="main" class="row-big">
  
  <header class="l-group">
    <h1><?php the_title(); ?></h1>
    <p class="p-big">
        <?php while (have_posts()) : the_post(); ?>
          <?php $content = $post -> post_content;
                echo $content;
           ?>
        <?php endwhile; //end of loop ?>
    </p>
  </header>
  
  <?php if( count($stories = fetch_published_stories()) > 0) { ?>
    <div class="row-big">
    <?php foreach($stories as $story) { 
      ?>
        <a href="<?php echo esc_url(home_url('/')) ?>/story/?id=<?php echo $story->id ?>" class="four grid-item grid-item-simple grid-item-fixed">
          <div class="grid-item-top grid-item-loose">
            <h4><?php echo $story->title ?></h4>
            <h6 class="subhead"><?php echo $story->created ?></h6>
            <p><?php echo $story->sub_title ?></p>
          </div>
          <div class="grid-item-bottom clearfix">
            <button class="btn btn-simple btn-orange pull-right">Read Story &rarr;</button>
          </div>
        </a>
    <?php } ?>
  </div>
  <?php } //end of loop ?>
</section>
<!-- /section -->

<?php get_footer(); ?>