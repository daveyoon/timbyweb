angular.module('timby.controllers', [])

.controller('MainController',
  ['$scope', '$rootScope', 'ReportService', '$sce', 'toaster', '$compile',
    function($scope, $rootScope,ReportService, $sce, toaster, $compile) {
        $scope.authenticated = false;
        $scope.filtercriteria = {
            sectors: [],
            entities: [],
            status: ['verified', 'unverified'],
            search: '',
            layers: []
        };
        $scope.map = null;

        $scope.markerLayer = null;
        $scope.activeLayers = [];

        var layers = {
            'allconcessions': {
                url: 'http://kaam.cartodb.com/api/v2/viz/a46166f8-c496-11e3-9920-0e10bcd91c2b/viz.json',
                options: {
                    query: "SELECT * FROM allconcessions"
                }
            }
        };

        $scope.$watch('filtercriteria.layers', function (newValue, oldValue) {
            newValue.forEach(function (element, index, array) {
                if ($scope.activeLayers[element]) {
                    $scope.activeLayers[element].show();
                }
                else {
                    cartodb.createLayer($scope.map, layers[element].url, layers[element].options).addTo($scope.map)
                        .on('done', function (layer) {
                            $scope.map.addLayer(layer);
                            $scope.activeLayers[element] = layer;
                        }).on('error', function () {
                        });
                }
            });
            _.difference(_.keys($scope.activeLayers), newValue).forEach(function (element, index, array) {
                $scope.activeLayers[element].hide();
            });
        }, true);

        $scope.$watch('filteredReports', function(newValue, oldValue){
            if ($scope.filteredReports) {
                var reportsIds = $scope.filteredReports.map(function(report){
                    return report.ID;
                });
                var query = "SELECT * FROM reports WHERE post_id IN (" + reportsIds.join(",") + ")";
                if ($scope.markerLayer) {
                    $scope.markerLayer.getSubLayer(0).setSQL(query);
                }
            }
        }, true);


        $rootScope.title = "Timby.org | Reporting and Visualization tool";


        $scope.$on('$viewContentLoaded', function () {
            $scope.map = L.map('map', {
                center: new L.LatLng(6.4336999, -9.4217516),
                zoom: 8
            });

            // base layer
            L.tileLayer('https://dnv9my2eseobd.cloudfront.net/v3/cartodb.map-4xtxp73f/{z}/{x}/{y}.png', {
                attribution: 'Mapbox <a href="http://mapbox.com/about/maps" target="_blank">Terms & Feedback</a>'
            }).addTo($scope.map);

            // populated places layer
            cartodb
                .createLayer($scope.map, 'http://kaam.cartodb.com/api/v2/viz/8f75f1ea-c172-11e3-ac41-0e73339ffa50/viz.json')
                .addTo($scope.map)
                .on('done', function (layer) {
                    $scope.markerLayer = layer;
                    var sublayer = $scope.markerLayer.getSubLayer(0);
                    sublayer.setInteraction(true);

                    sublayer.set({
                        sql: 'SELECT * FROM reports',
                        cartocss: '#example_cartodbjs_1{marker-fill: #109DCD; marker-width: 10; marker-line-color: white; marker-line-width: 0;}'
                        // interactivity : 'post_id'
                    });

                    sublayer.infowindow.set('template', function () {
                        var fields = this.model.get('content').fields;
                        if (fields && fields[0].type !== 'loading') {
                            var _post_id = _.find(fields, function (obj) {
                                return obj.title == 'post_id'
                            }).value;

                            // find a report with this id
                            if ($scope.reports.length > 0) {
                                // find this report from our report cache
                                for (var i = $scope.reports.length - 1; i >= 0; i--) {
                                    if (_post_id == $scope.reports[i].ID) {
                                        $scope.report = $scope.reports[i];
                                        break;
                                    }
                                }
                            }
                            var _compiled = $compile(angular.element('#infowindow_template').html())($scope);
                            $scope.$apply();
                            return _compiled.html();
                        }

                        return '';
                    });
                    // var _reports = $scope.reports;
                    // sublayer.infowindow.set('template', angular.element('infowindow_template').html());

                    // get sublayer 0 and set options
                    //  the infowindow template
                    // var sublayer = layer.getSubLayer(0);
                    // sublayer.set(subLayerOptions);

                });

        });


        $scope.getAllReports = function () {
            $scope.working = true;
            ReportService
                .findAll()
                .then(
                function success(response, status, headers, config) {
                    if (response.data.status == 'success') {
                        $scope.working = false;
                        $scope.reports = response.data.reports;
                    }
                },
                function error(response, status, headers, config) {
                    //notify alert, could not connect to remote server
                }
            )
        };
        $scope.getAllReports();

        $scope.viewReport = function (id) {

            // do a lookup from the object cache
            if ($scope.reports.length > 0) {
                // find this report from our report cache
                for (var i = $scope.reports.length - 1; i >= 0; i--) {
                    if (id == $scope.reports[i].ID) {
                        $scope.report = $scope.reports[i];
                        break;
                    }
                }
            }

            // if report still not found
            // load it from the server
            if (!$scope.report) {
                ReportService
                    .findById(id)
                    .then(
                    function success(response, status, headers, config) {
                        $scope.working = false;

                        if (response.data.status == 'success') {
                            $scope.report = response.data.report;
                        }
                    },
                    function error(response, status, headers, config) {
                        //notify alert, could not connect to remote server
                    }
                )
            }

            // initialize the map
            var map = new google.maps.Map(
                document.getElementById('report-location'),
                {
                    zoom: 7,
                    center: new google.maps.LatLng($scope.report.lat, $scope.report.lng)
                }
            );

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(
                    $scope.report.lat,
                    $scope.report.lng
                ),
                map: map
            });

        }

        /**
         * Checks whether the current item in the repeat is active
         */
        $scope.isActive = function (id) {
            if ($scope.report)
                return $scope.report.ID == id;

            return false;
        }

        $scope.updateReport = function () {
            $scope.working = true;
            ReportService
                .update($scope.report)
                .then(
                function success(response, status, headers, config) {
                    $scope.working = false;
                    toaster.pop('success', 'Success', 'Report saved successfuly');
                },
                function error(response, status, headers, config) {
                    $scope.working = false;
                    //notify alert, could not connect to remote server
                }
            )
        }

        $scope.verifyReport = function () {
            $scope.report.verified = !$scope.report.verified;
            $scope.updateReport();
        }

        // mark a given location as trusted
        $scope.trustSrc = function (src) {
            return $sce.trustAsResourceUrl(src);
        }

        /**
         * Remove an entity tag from a report
         * @param  object term
         * @return void
         */
        $scope.removeEntity = function (term) {
            if (angular.isArray($scope.report.entities)) {
                for (var i = 0; i < $scope.report.entities.length; i++) {
                    if (angular.equals($scope.report.entities[i], term)) {
                        $scope.report.entities.splice(i, 1);
                        break;
                    }
                }
            }
        }

        /**
         * Detach the media object from a report
         * @param  integer id object ID
         * @return void
         */
        $scope.detachMedia = function (id, $event) {
            var elem = angular.element($event.target);
            elem.parents('.media-item').fadeOut(500, function () {
                this.remove()
            });

            ReportService
                .detachMediaObject(id, $scope.report.ID)
                .then(function (response, status, headers, config) {
                    if (response.data.status == 'success') {
                        $scope.getAllReports();
                    }
                });
        };

        // watch the entity select while filtering
        $scope.$watch(function () {
            return $scope.filter_entity_selected
        }, function (newvalue, oldvalue, scope) {
            if (typeof(newvalue) == 'undefined')
                return

            for (i = 0; i < $scope.filtercriteria.entities.length; i++) {
                if (angular.equals(newvalue, $scope.filtercriteria.entities[i])) {
                    $scope.tagexists = true;
                    return;
                }
            }
            $scope.filtercriteria.entities.push($scope.filter_entity_selected);

        });

        // watch the entity select while adding new entities to select
        $scope.$watch(function () {
            if ($scope.report && typeof($scope.report.termselected) !== 'undefined')
                return $scope.report.termselected
        }, function (newvalue, oldvalue, scope) {
            $scope.tagexists = false;
            if (typeof(newvalue) == 'undefined')
                return

            if (angular.isArray($scope.report.entities)) {
                for (i = 0; i < $scope.report.entities.length; i++) {
                    if (angular.equals($scope.report.termselected, $scope.report.entities[i])) {
                        $scope.tagexists = true;
                        return;
                    }
                }
                $scope.report.entities.push($scope.report.termselected);
            }

        });


        $scope.removeEntityFilter = function (term) {
            for (var i = 0; i < $scope.filtercriteria.entities.length; i++) {
                if (angular.equals($scope.filtercriteria.entities[i], term)) {
                    $scope.filtercriteria.entities.splice(i, 1);
                    break;
                }
            }
        };


    }
  ]
)
.controller('LoginController',
  [
    '$scope', '$rootScope','$window', '$location', 'AuthService',
    function($scope,$rootScope,$window, $location, AuthService){

      $scope.login = function(){
        $scope.working = true;

        AuthService
          .login($scope.username, $scope.password)
          .then(
            function(response){
              $scope.working = false;
              if(response.data.status == 'success' && response.data.user){
                AuthService.logged_in = true;
                AuthService.user = response.data.user;

                // persistent session storage
                $window.sessionStorage.user_id = response.data.user.id;
                $window.sessionStorage.user_token = response.data.user.token;

                $location.path('/dashboard');
              } else{
                $scope.error_message = "Wrong user or password";
              }
            },
            function(){

            }
          )
      }

      /**
       * Unset the user object and
       * set login status to false
       * and go to start page
       */
      $scope.logout = function(){
        if( $window.sessionStorage.user_id && $window.sessionStorage.user_token){
          AuthService
            .logout()
            .then(function(response){
              AuthService.user = {};
              AuthService.logged_in = false;

              $window.sessionStorage.clear();

              $location.path('/');
            });
        }

        $location.path('/'); //redirect anyway
      }
    }
  ]
)
.controller('ReportController', ['$scope','$upload','ReportService', function($scope, $upload, ReportService){
  $scope.report = {
    description : '' //must make description blank or textangular's wrap-p's won't function
  };
  $scope.formerrors = {};

  // datepicker options
  $scope.dateOptions = {
    'year-format': "'yy'",
    'starting-day': 1
  };

  // Enable the new Google Maps visuals until it gets enabled by default.
  google.maps.visualRefresh = true;
  angular.extend($scope, {
    map : {
      center: {
        // map center is at Kokoyah, Liberia
        latitude: 6.550676,
        longitude: -9.488156
      },
      zoom: 7,
      clickedMarker: {
          title: 'Your current position',
          latitude: null,
          longitude: null
      },
      events: {
        click : function(mapModel, eventName, originalEventArgs){
          var e = originalEventArgs[0];

          $scope.map.clickedMarker.latitude = $scope.report.lat =  e.latLng.lat();
          $scope.map.clickedMarker.longitude = $scope.report.lng = e.latLng.lng();

          $scope.$apply();
        }
      }
    }
  });



  $scope.createReport = function(evt){

    if( !$scope.report.lat || !$scope.report.lng ) {
      $scope.formerrors.location = 'Please select a location on the map';
      return;
    } else{
      $scope.formerrors.location = null;
    }

    $scope.working = true;
    ReportService
      .create($scope.report)
      .then(
        function success(response, status, headers, config) {
          if (response.data.status == 'success') {
            // nothing to upload
            if( !$scope.report.photos && !$scope.report.video && !$scope.report.audio )
              $scope.working = false;

            $scope.filecount = 0;

            var uploadedcounter = 0;

            /**
             * Keeps track of the number of files uploaded
             * passed as a callback to uploadMedia
             */
            var uploadComplete = function(){
              uploadedcounter++

              if( uploadedcounter == $scope.filecount){
                $scope.working = false;
              }
            }

            if( $scope.report.photos && $scope.report.photos.length > 0){
              $scope.filecount += $scope.report.photos.length;
              ReportService.uploadMedia('image', $scope.report.photos, response.data.report.ID, uploadComplete);
            }

            if( $scope.report.video && $scope.report.video.length > 0){
              $scope.filecount += $scope.report.video.length;
              ReportService.uploadMedia('video', $scope.report.video, response.data.report.ID, uploadComplete);
            }

            if( $scope.report.audio && $scope.report.audio.length > 0){
              $scope.filecount += $scope.report.audio.length;
              ReportService.uploadMedia('audio', $scope.report.audio, response.data.report.ID, uploadComplete);
            }

            $scope.reset(evt); // reset the form
          }
        },
        function error(response, status, headers, config) {
          //notify alert, could not connect to remote server
        }
      );
  }

  $scope.reset = function(e){
    // peform some form cleanup

    // mute the model
    $scope.report = {};

    // clear the marker off the map
    $scope.map.clickedMarker.latitude = null;
    $scope.map.clickedMarker.longitude = null;

    // clear the error messages as well
    $scope.formerrors = {}

    //set the form to pristine, i.e user hasn't interacted with it
    $scope.addreportform.$setPristine();
  }

  $scope.onFileSelect = function($type, $files){

    if( $type == 'photo' ){
      if( ! files_are_valid($files, ['image/jpeg', 'image/png']) ){
        $scope.formerrors.photo = 'Select only valid image files.';
        $scope.addreportform.$setValidity('photo', false);
        return;
      }
      if( $scope.formerrors.photo)
        $scope.formerrors.photo = null;

      $scope.addreportform.$setValidity('photo', true);
      $scope.report.photos = $files;
    }

    if( $type == 'video'){
      if( ! files_are_valid($files,
            [
              'video/mp4', 'video/ogg','video/webm',
               //.mov
              'video/x-flv', 'video/quicktime',
               //.avi
              'application/x-troff-msvideo',
              'video/avi',
              'video/msvideo',
              'video/x-msvideo',
              'video/avs-video',
              // mkv
              'video/x-matroska'
            ])
        ){
        $scope.formerrors.video = 'Sorry we can only accept .mov, .mp4, .avi and .mkv video files.';
        $scope.addreportform.$setValidity('video', false);
        return;
      }
      if( $scope.formerrors.video)
        $scope.formerrors.video = null;
      $scope.addreportform.$setValidity('video', true);
      $scope.report.video = $files;
    }

    if( $type == 'audio'){
      if( ! files_are_valid($files, ['audio/mpeg', 'video/mp4', 'audio/mp4a-latm', 'audio/ogg']) ){
        $scope.formerrors.audio = 'Sorry we can only accept mp3, mp4, m4a, m4b, m4p and ogg audio files.';
        $scope.addreportform.$setValidity('audio', false);
        return;
      }
      if( $scope.formerrors.audio)
        $scope.formerrors.audio = null;
      $scope.addreportform.$setValidity('audio', true);
      $scope.report.audio = $files;
    }

    /**
     * validate the file types,
     * return on first
     * @param  {[type]} $files       [description]
     * @param  {[type]} $valid_types [description]
     * @return {[type]}              [description]
     */
    function files_are_valid($files, $valid_types){
      var valid = true;
      for(var i=0; i<=$files.length; $i++){
        if( $valid_types.indexOf($files[i].type) === -1 )
          valid = false;
          break;
      }
      return valid;
    }
  }
}])
.controller('StoryController',['$scope', 'ReportService','StoryService','toaster', '$routeParams','$location', 'resolvedata', '$modal', function($scope, ReportService, StoryService, toaster, $routeParams, $location, resolvedata, $modal){
  $scope.working = false;

  // fetch all verified reports
  $scope.reports = [];
  ReportService
    .findAll(['verified=on'])
    .then(function(response){
      $scope.reports = response.data.reports;
    })


  // fetch the report by id if we are editing
  if( resolvedata.story )
    $scope.story = resolvedata.story


  if( resolvedata.stories )
    $scope.stories = resolvedata.stories


  /**
   * View video on overlay
   */
  $scope.viewVideo = function(video){
    var modalInstance = $modal.open({
      template: '<iframe ng-src="'+video.vimeo.embed_url+'" width="100%"  height="500" scrolling="no" frameborder="no"></iframe>'
    });
  }

  /**
   * View fullsize photo in the bootstrap modal in a lightbox
   */
  $scope.viewPhoto = function(photo){
    var modalInstance = $modal.open({
      template: '<img src="'+photo.large+'" />'
    });
  }

  /**
   * add report to story
   *
   * @param integer id  report ID
   * @param object $event
   */
  $scope.addReportToStory = function(id, evt){

    // do a lookup from the object cache
    if( $scope.reports.length > 0){
      // find this report from our report cache
      for (var i = $scope.reports.length - 1; i >= 0; i--) {
        if( id == $scope.reports[i].ID ){
          $scope.report = $scope.reports[i];
          break;
        }
      }
    }

    $scope.story.content.push({
      type : 'report',
      report : $scope.report
    });

    $scope.search = '';
  }


  /**
   * Add a content editor
   * onto the story structure
   *
   * @param object evt
   */
  $scope.addContentEditor = function(evt){
    $scope.story.content.push({
      type : 'editor',
      text : ''
    });
  }


  /**
   * Remove content editor
   *
   * @param object evt
   */
  $scope.removeContentBlock = function($index, evt){
    angular.forEach($scope.story.content, function(content, index){
      if( $index == index )
        $scope.story.content.splice(index, 1);
    });
    angular
      .element(evt.target).parents('.l-group')
      .fadeOut(500, function(){
        this.remove()
      });
  }

  /**
   * Removes a report from the story
   * removes the element from the DOM
   * and updates the story manifest
   *
   * @param integer id  report ID
   * @param object $event
   * @todo: update the story json manifest
   */
  $scope.removeReportFromStory = function( reportid, evt){
    angular.element(evt.target).parent().remove();
  }


  // this is called by both save() and publish
  function save_story(){

  }

  $scope.save = function(){
    // check if we are updating an
    // existing story
    var updating_story = false;
    if( $scope.story.id )
      updating_story = true;

    $scope.working = true;
    StoryService
      .save($scope.story)
      .then(function(response){
        $scope.working = false;
        toaster.pop('success', 'Success', 'Story saved successfuly');

        if( ! updating_story ) {
          // redirect to the edit story view
          $location.path('/story/edit/'+response.data.id)
        }

      });
  }

  $scope.publish = function(){
    $scope.working = true;

    StoryService
      .saveAndPublish($scope.story)
      .then(function(response){
        $scope.working = false;
        if( response.data.published_story_id ) {
          // remain at current state if we are editing the story
          // navigate to /story/edit/{id} if this is a new story
          if( ! $scope.story.id )
            $location.path('/story/edit/'+response.data.id)

          toaster.pop('success', 'Success', 'Story published successfuly!');
        }
      });
  }


}]);
