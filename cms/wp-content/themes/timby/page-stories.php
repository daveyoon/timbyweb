<?php
/**
 * The template for displaying stories
 *
 *
 * @package timby
 */
 get_header(); ?>
  
<!-- section -->
<section role="main" class="row">
  
  <h1><?php the_title(); ?></h1>
  
  <?php if( count($stories = fetch_published_stories()) > 0) { ?>
    <?php foreach($stories as $story) { ?>
      <div class="row">
        <a href="<?php echo esc_url(home_url('/')) ?>/story/?id<?php echo $story->id ?>" class="four grid-item grid-item-simple grid-item-fixed">
          <div class="grid-item-top">
            <h4><?php echo $story->title ?></h4>
            <h6 class="subhead"><?php echo $story->created ?></h6>
            <p><?php echo $story->sub_title ?></p>
          </div>
          <div class="grid-item-bottom">
            <button class="btn-small btn-simple">Edit this story</button>
          </div>
        </a>
      </div>
    <?php } ?>
  <?php } //end of loop ?>
</section>
<!-- /section -->

<?php get_footer(); ?>