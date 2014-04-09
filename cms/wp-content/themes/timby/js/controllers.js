angular.module('timby.controllers', [])
.controller('MainController',
  ['$scope', '$rootScope', 'ReportService', '$sce', 'toaster',
    function($scope, $rootScope,ReportService, $sce, toaster){
      $scope.authenticated = false;
      $scope.filtercriteria = {
        sectors   : [],
        entities  : [],
        status : ['verified', 'unverified'],
        search  : ''
      };

      $rootScope.title = "Timby.org | Reporting and Visualization tool";

      $scope.getAllReports = function(){
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

      $scope.viewReport = function(id){

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

        // if report still not found
        // load it from the server
        if ( ! $scope.report ) {
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
            center: new google.maps.LatLng($scope.report.lat,$scope.report.lng)
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
      $scope.isActive = function(id){
        if($scope.report)
          return $scope.report.ID == id;

        return false;
      }

      $scope.updateReport = function(){
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

      $scope.verifyReport = function(){
        $scope.report.verified = !$scope.report.verified;
        $scope.updateReport();
      }

      $scope.trustSrc = function(src){
        return $sce.trustAsResourceUrl(src);
      }

      /**
       * Remove an entity tag from a report
       * @param  object term
       * @return void
       */
      $scope.removeEntity = function(term){
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
      $scope.detachMedia = function(id, $event){
        var elem = angular.element($event.target);
        elem.parents('.media-item').fadeOut(500, function(){
          this.remove()
        });

        ReportService
          .detachMediaObject(id, $scope.report.ID)
          .then(function(response, status, headers, config){
            if( response.data.status == 'success'){
              $scope.getAllReports();
            }
          });
      };

      // watch the entity select while filtering
      $scope.$watch(function(){
        return $scope.filter_entity_selected
      }, function(newvalue, oldvalue, scope){
        if( typeof(newvalue) == 'undefined' )
          return

        for(i=0; i < $scope.filtercriteria.entities.length; i++){
          if(angular.equals(newvalue, $scope.filtercriteria.entities[i])){
            $scope.tagexists = true;
            return;
          }
        }
        $scope.filtercriteria.entities.push($scope.filter_entity_selected);

      });

      // watch the entity select while adding new entities to select
      $scope.$watch(function(){
        if( $scope.report && typeof($scope.report.termselected) !== 'undefined' )
          return $scope.report.termselected
      }, function(newvalue, oldvalue, scope){
        $scope.tagexists = false;
        if( typeof(newvalue) == 'undefined' )
          return

        if (angular.isArray($scope.report.entities)) {
          for(i=0; i < $scope.report.entities.length; i++){
            if(angular.equals($scope.report.termselected, $scope.report.entities[i])){
              $scope.tagexists = true;
              return;
            }
          }
          $scope.report.entities.push($scope.report.termselected);
        }

      });


      $scope.removeEntityFilter = function(term){
        for (var i = 0; i < $scope.filtercriteria.entities.length; i++) {
          if (angular.equals($scope.filtercriteria.entities[i], term)) {
            $scope.filtercriteria.entities.splice(i, 1);
            break;
          }
        }
      }

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
  $scope.report = {};
  $scope.formerrors = {};
  $scope.placeholderText = "Type your description here";

  // datepicker options
  $scope.dateOptions = {
    'year-format': "'yy'",
    'starting-day': 1
  };

  $scope.placeholder = function(){
    var editor = angular.element('#taTextElement');
    var toolbar = angular.element('.ta-toolbar');
    if (editor.text() == $scope.placeholderText){
      editor.text('');
    }
    // console.log(angular.element('.ta-toolbar').hasClass('hide'));
    if (toolbar.hasClass('hide') == 'true'){
      toolbar.removeClass('hide');
    }
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
    $scope.formerrors = null

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
.controller('StoryController',['$scope', 'ReportService','$compile', function($scope, ReportService, $compile){
  // fetch all verified reports
  $scope.reports = [];

  ReportService
    .findAll(['verified=true'])
    .then(function(response){
      $scope.reports = response.data.reports;
    })

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

    // add the report
    var _parent_content_block = angular.element(evt.target).parents('.l-group');
    _parent_content_block.before($compile('<reportcard />')($scope));

    // initialize the map
    angular.extend($scope, {
      map : {
        center: {
          // map center is at Kokoyah, Liberia
          latitude: $scope.report.lat,
          longitude: $scope.report.lng
        },
        zoom: 7,
        clickedMarker: {
            title: 'Your current position',
            latitude: $scope.report.lat,
            longitude: $scope.report.lng
        }
      }
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

}]);
