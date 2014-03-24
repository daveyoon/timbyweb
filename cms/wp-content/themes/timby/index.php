<?php get_header(); ?>
<nav class="navbar" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Timby</a>
    </div>
  </div>
</nav>
<ul class="nav nav-tabs">
  <li class="active"><a href="#">Moderation</a></li>
  <li><a href="#">Create a Story</a></li>
  <li class="pull-right nav-pills"><a href="#" class="btn btn-success">Add Report</a></li>
</ul>
<div class="container-fluid">
  <div class="row main-unit">
    <div class="column forty filtering">
      <section class="filters padding-item gray">
        <form class="form-horizontal" role="form">
          <div class="form-group">
            <label for="search" class="col-sm-2 control-label">Search</label>
            <div class="col-sm-10">
              <input type="search" class="form-control" id="search" placeholder="Search Reports">
            </div>
          </div>
          <div class="form-group">
            <label for="search" class="col-sm-2 control-label">Sector</label>
            <div class="col-md-10">
              <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-default">
                  <input type="checkbox"> Logging
                </label>
                <label class="btn btn-default">
                  <input type="checkbox"> Palm Oil
                </label>
                <label class="btn btn-default">
                  <input type="checkbox"> Community Dev Fund
                </label>
              </div>
            </div>
          </div><!--end row-->
          <div class="form-group">
            <label for="search" class="col-sm-2 control-label">Tags</label>
            <div class="col-sm-10">
              <input type="search" class="form-control" id="search" placeholder="Start typing to bring up tags">
            </div>
          </div>
          <div class="form-group">
            <label for="search" class="col-sm-2 control-label">Date</label>
            <div class="col-sm-4">
              <input type="search" class="form-control" id="search" placeholder="Start Date">
            </div>
            <div class="col-sm-1">
              <span class="text-center text-muted">to</span>
            </div>
            <div class="col-sm-4">
              <input type="search" class="form-control" id="search" placeholder="End Date">
            </div>
          </div>
        </form>
      </section>
      <section class="results">
        <ul class="list-group">

          <?php if( count($reports = fetch_new_reports()) > 0) { ?>
            <?php foreach($reports as $report) { ?>
              <a href="#" class="list-group-item clearfix list-report" data-id="<?php echo $report->ID ?>">
                <div class="column eighty">
                  <?php if($report->verified) { ?>
                    <span class="label label-success">Verified</span>
                  <?php } else { ?>
                    <span class="label label-warning">Unverified</span>
                  <?php } ?>

                  <h4 class="list-group-item-heading">
                    <?php echo $report->post_title ?>
                  </h4>
                  <p class="list-group-item-text">
                    Date Reported: <?php echo $report->date_reported ?>
                    | by <?php echo $report->reporter ?>
                  </p>
                </div>
                <span class="column twenty text-right text-muted">
                  <?php echo count($report->media->photos) ?> <span class="glyphicon glyphicon-picture"></span>&nbsp;&nbsp;
                  <?php echo count($report->media->video) ?> <span class="glyphicon glyphicon-facetime-video"></span>&nbsp;&nbsp;
                  <?php echo count($report->media->audio) ?> <span class="glyphicon glyphicon-music"></span>
                </span>
              </a>
            <?php } ?>
          <?php } else { ?>
            <p>No reports found</p>
          <?php } ?>
        </ul>
      </section>
  
    </div>
    <div class="column sixty report-wrap"></div>
  </div>
</div>


<!-- templates -->
<script type="text/html" id="report_template">
  <div class="padding-item">
    <div class="row item">
      <div class="col-md-8">
      <a href="" class="btn btn-primary">Edit this Report</a>
        <h1><%=post_title%></h1>
        <h4>Date <%=date_reported%> by <%=reporter%> on device</h4>
        <span class="label label-info">Sector: <%=sector%> </span>
      </div>
      <div class="col-md-4">
        <div id="report-location" style="width:200px; height:200px;"></div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
      <%=post_content%>
      </div>
    </div>
    <div class="row">
      <% if (media.photos.length > 0){ %>
        <% for ( var i = 0; i < media.photos.length; i++ ) { %>
          <div class="col-md-3">
            <a href="#" class="thumbnail">
              <img src="<%=media.photos[i].guid%>">
            </a>
          </div>
        <% } %>
      <% } %>
      <% if (media.audio.length > 0){ %>
        <% for ( var i = 0; i < media.audio.length; i++ ) { %>
          <% if (media.audio[i].soundcloud){ %>
            <div class="col-md-3">
              <iframe 
              width="100%" 
              height="166" 
              scrolling="no" 
              frameborder="no" 
              src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/<%=media.audio[i].soundcloud.id%>%3Fsecret_token%3D<%=media.audio[i].soundcloud.secret_token%>&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_artwork=true">
              </iframe>
            </div>
          <% } %>
        <% } %>
      <% } %>

      <% if (media.video.length > 0){ %>
        <% for ( var i = 0; i < media.video.length; i++ ) { %>
          <% if (media.video[i].vimeo){ %>
            <iframe 
                src="//player.vimeo.com/video/<%=media.video[i].vimeo.video_id%>" 
                width="500" 
                height="281" 
                frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen>
            </iframe> 
          <% } %>
        <% } %>
      <% } %>

    </div>
    <div class="row">
      <div class="col-md-12">
        <a href="" class="btn btn-primary">Verify</a>
      </div>
    </div>
  </div>
</script>
<?php get_footer() ?>