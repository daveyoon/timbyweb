<?php
/**
 * The template for displaying a single story
 *
 *
 * @package timby
 */
get_header(); 
$story_id = (int) sanitize_text_field($_REQUEST['id']);

global $wpdb;
$tablename = $wpdb->prefix . 'published_stories';

$story = $wpdb->get_row("SELECT id, title,  sub_title, content FROM $tablename WHERE id = $story_id");
$story = build_story_data($story);

// parse the json story content string
$story->content = json_decode($story->content);
?>
<!-- section -->
<section role="main" class="row-big">
  
  <div class="header-group l-group">
    <h1><?php echo $story->title ?></h1>
    <h3><?php echo $story->sub_title ?></h3>
  </div>
  
  <div class="content-blocks l-group">
    <?php foreach ($story->content as $content) { ?>
      <?php if( $content->type == 'editor') { ?>
        <p><?php echo $content->text ?></p>        
      <?php } ?>

      <?php if( $content->type == 'report') { ?>
        <div class="l-group ">
          <div class="report-thumb">
            <header>
              TIMBY REPORT
            </header>
            <div class="report-thumb-content">
              <div class="twelve report-thumb-info">
                <h6 class="list-title">
                  <?php echo $content->report->post_title ?>
                </h6>
                <div class="list-content">
                  <span class="list-details text-muted">
                    <?php echo $content->report->date_reported ?>
                  </span>
                  <div class="list-content-description">
                    <?php echo $content->report->post_content ?>
                  </div>
                </div>
              </div>
              <div class="report-thumb-media">
                <div class="three report-thumb-map">
                  <div style="width: 200px; height: 200px;"class="timby-thumb-map" data-lat="<?php echo $content->report->lat ?>"  data-lng="<?php echo $content->report->lng ?>" ></div>
                </div>
                <?php foreach($content->report->media->photos as $photo) { ?>
                  <div class="three">
                    <a href="">
                      <img src="<?php echo $photo->small ?>">
                    </a>
                  </div>
                <?php } ?>
                <?php foreach($content->report->media->audio as $audio) { ?>
                  <div class="three">
                    <iframe src="<?php echo $audio->soundcloud->embed_url ?>" width="100%"  height="120" scrolling="no" frameborder="no"></iframe>
                  </div>
                <?php } ?>

                <?php foreach($content->report->media->video as $video) { ?>
                  <div class="three">
                    <iframe
                        src="<?php echo $video->vimeo->embed_url ?>"
                        frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen>
                    </iframe>
                  </div>
                <?php } ?>
              </div>
            </div>
            <footer>
              <button class="btn-small" ng-click="showEmbed=!showEmbed">Embed</button>
              <a class="btn-small" href="<?php echo $content->report->download_link ?>">Download</a>
              <div class="embed" ng-show="showEmbed">
                <h5>Embed this report</h5>
                <textarea name="" id="" style="width:100%; height:100px;" ><?php echo $content->report->embed_code ?></textarea>
              </div>
            </footer>
          </div>
        </div>
      <?php } ?>
    <?php } ?>
  </div>

</section>
<!-- /section -->

<?php get_footer(); ?>