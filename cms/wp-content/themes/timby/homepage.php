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
 * Template Name: Homepage    
 */
 get_header(); 
 
 ?>

	
<!-- section -->
<section role="main" class="row-big">
	<section class="watercolor l-group">
    <div class="three">
        <div class="watercolor-sprite watercolor-rice"></div>
      </div>
    <div class="six">
        <div class="l-group">
          <h2 class="yellow text-center text-light">We are grassroots activists using cutting edge technology to hold the Liberian government to its commitments</h2>
          <!-- <a href="about-us/" class="btn btn-yellow btn-center" style="width:110px;">Learn more</a> -->
        </div>
        <div class="watercolor-sprite watercolor-tractor pull-right"></div>
      </div>
      <div class="three">
        <div class="watercolor-sprite watercolor-tree"></div>
      </div>
  </section>
  <section class="findings l-group">
    <header class="clearfix l-group">
      <h3 class="pull-left orange">Our Findings</h3>
      <a href="stories" class="btn btn-orange pull-right" >See All Stories</a>
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
      
    </div>    
  </section>
  
</section>
<!-- /section -->

<?php get_footer(); ?>