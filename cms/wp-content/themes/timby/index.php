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
              <a href="#" class="list-group-item clearfix">
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
                  <?php echo $report->mediacount->photos ?> <span class="glyphicon glyphicon-picture"></span>&nbsp;&nbsp;
                  <?php echo $report->mediacount->video ?> <span class="glyphicon glyphicon-facetime-video"></span>&nbsp;&nbsp;
                  <?php echo $report->mediacount->audio ?> <span class="glyphicon glyphicon-music"></span>
                </span>
              </a>
            <?php } ?>
          <?php } else { ?>
            <p>No reports found</p>
          <?php } ?>
        </ul>
      </section>
  
    </div>
    <div class="column sixty">
      <div class="padding-item">
        <div class="row item">
          <div class="col-md-8">
          <a href="" class="btn btn-primary">Edit this Report</a>
            <h1>Report title</h1>
            <h4>Date by reporter name on device</h4>
            <span class="label label-info">Sector</span>
            <span class="label label-warning">Entity</span>
            <span class="label label-warning">Entity</span>
            <span class="label label-warning">Entity</span>
          </div>
          <div class="col-md-4">
            <img src="http://fillmurray.com/g/200/200">
          </div>
        </div><!--- end header group-->
        <div class="row">
          <div class="col-md-12">
            <p>This is where the content of the report goes. Text goes first then media. Tags are between the header and the text. This is where the content of the report goes. Text goes first then media. Tags are between the header and the text. This is where the content of the report goes. Text goes first then media. Tags are between the header and the text</p>
          </div>
        </div><!-- end text -->
        <div class="row">
          <div class="col-md-3">
            <a href="#" class="thumbnail">
              <img src="http://fillmurray.com/g/200/200">
            </a>
          </div>
          <div class="col-md-3">
            <a href="#" class="thumbnail">
              <img src="http://fillmurray.com/g/200/200">
            </a>
          </div>
          <div class="col-md-3">
            <a href="#" class="thumbnail">
              <img src="http://fillmurray.com/g/200/200">
            </a>
          </div>
          <div class="col-md-3">
            <a href="#" class="thumbnail">
              <img src="http://fillmurray.com/g/200/200">
            </a>
          </div>
        </div><!-- end media -->
        <div class="row">
          <div class="col-md-12">
            <a href="" class="btn btn-primary">Verify</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bower_components/bootstrap-sass/vendor/assets/javascripts/bootstrap/button.js"></script>
</body>
</html>