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
	<section class="watercolor l-group-big">
    <div class="three">
        <div class="watercolor-sprite watercolor-rice"></div>
      </div>
    <div class="six">
        <div class="l-group-big">
          <h2 class="yellow text-center text-light">We are grassroots activists using cutting edge technology to hold the Liberian government to its commitments</h2>
          <!-- <a href="about-us/" class="btn btn-yellow btn-center" style="width:110px;">Learn more</a> -->
        </div>
        <div class="watercolor-sprite watercolor-tractor pull-right"></div>
      </div>
      <div class="three">
        <div class="watercolor-sprite watercolor-tree"></div>
      </div>
  </section>
  <section class="l-group-big how-we-do-it">
    <header class="l-group-big">
      <h3 class="text-center text-orange">How we do it</h3>
      <div class="six shift-three"><p class="text-center">Our on the ground activists use our smartphone app, moderation platform and reporting tool to get information from the forests of Liberia to the screens of the world.</p></div>
    </header>
    <div class="row-big">
      <div class="four">
        <div class="watercolor-sprite watercolor-monitor l-group"></div>
        <h5 class="text-center text-green">Monitor</h5>
        <p class="p-sans text-center p-limited">On the ground activists use our purpose built app to take photos, videos and audio recordings</p>
      </div>
      <div class="four">
        <div class="watercolor-sprite watercolor-moderate l-group"></div>
        <h5 class="text-center text-yellow">Moderate</h5>
        <p class="p-sans text-center p-limited">Our expert moderators clean up these reports, verifying information and categorizing them for later</p>
      </div>
      <div class="four">
        <div class="watercolor-sprite watercolor-story l-group"></div>
        <h5 class="text-center text-orange">Report</h5>
        <p class="p-sans text-center p-limited">We use these reports to create special stories to tell you know what is happening and why it’s important</p>
      </div>
    </div>
  </section>
  <section class="findings l-group">
    <header class="clearfix l-group-big">
      <h3 class="text-center text-orange">Our Findings</h3>
      <div class="six shift-three text-center">
        <p>We pull together all of our grassroots reporting into clear narratives that explain what is happening and why it is important</p>
        <a href="stories" class="btn btn-orange btn-center" >See All Stories</a>
      </div>
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