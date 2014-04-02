angular.module('timby.controllers', [])
.controller('MainController', 
  ['$scope', '$rootScope', 'ReportService', '$sce',
    function($scope, $rootScope,ReportService, $sce){
      $scope.authenticated = false;
      $scope.filtercriteria = { 
        sectors   : [],
        entities  : [],
        search  : ''
      };

      // redirect all non logged in users
      $rootScope.$on('$routeChangeStart', function(event, next, current){
        if( next.authenticate && !AuthService.isAuthenticated()){
          $location.path( "/login" )
        }
      });


      $rootScope.title = "Timby.org | Reporting and Visualization tool";

      $scope.getAllReports = function(){
        ReportService
          .findAll()
          .then(
            function success(response, status, headers, config) {
              if (response.data.status == 'success') {
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
        $scope.working = true;

        ReportService
          .findById(id)
          .then(
            function success(response, status, headers, config) {
              $scope.working = false;

              if (response.data.status == 'success') {
                $scope.report = response.data.report;

                // initialize the map
                var map = new google.maps.Map(
                  document.getElementById('report-location'),
                  {
                    zoom: 7,
                    center: new google.maps.LatLng(response.data.report.lat,response.data.report.lng)
                  }
                );

                var marker = new google.maps.Marker({
                  position: new google.maps.LatLng(
                    response.data.report.lat,
                    response.data.report.lng
                  ),
                  map: map
                });

              }
            },
            function error(response, status, headers, config) {
              //notify alert, could not connect to remote server
            }
          )
      }

      $scope.updateReport = function(){
        $scope.working = true;
        ReportService
          .update($scope.report)
          .then(
            function success(response, status, headers, config) {
              $scope.working = false;
              if (response.data.status == 'success') {
                $scope.getAllReports();
              }
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
        return $scope.termselected
      }, function(newvalue, oldvalue, scope){
        $scope.tagexists = false;
        if( typeof(newvalue) == 'undefined' )
          return

        if (angular.isArray($scope.report.entities)) {
          for(i=0; i < $scope.report.entities.length; i++){
            if(angular.equals($scope.termselected, $scope.report.entities[i])){
              $scope.tagexists = true;
              return;
            }
          }
          $scope.report.entities.push($scope.termselected);
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
    '$scope', '$rootScope', 'AuthService', '$location', 
    function($scope,$rootScope, AuthService, $location){

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
                $location.path('/dashboard');
              } else{
                $scope.error_message = "Invalid login, please try again";
              }
            },
            function(){

            }
          )

      }
    }
  ]
)
.controller('ReportController', ['$scope','$upload','ReportService', function($scope, $upload, ReportService){
  $scope.report = {};
  $scope.report.placeholderText = "Type your description here";
  $scope.placeholder = function(){
    var editor = angular.element('#taTextElement');
    var toolbar = angular.element('.ta-toolbar');
    if (editor.text() == $scope.report.placeholderText){
      editor.text('');
    }
    // console.log(angular.element('.ta-toolbar').hasClass('hide'));
    if (toolbar.hasClass('hide') == 'true'){
      toolbar.removeClass('hide');
    }
  };

  // Enable the new Google Maps visuals until it gets enabled by default.
  google.maps.visualRefresh = true;

  // map center is at Kokoyah, Liberia
  $scope.report.lat = 6.550676;
  $scope.report.lng = -9.488156;

  angular.extend($scope, {
    map : {
      center: {
        latitude: $scope.report.lat,
        longitude: $scope.report.lng
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

  $scope.dateOptions = {
    'year-format': "'yy'",
    'starting-day': 1
  };

  $scope.createReport = function(evt){

    $scope.working = true;
    ReportService
      .create($scope.report)
      .then(
        function success(response, status, headers, config) {
          if (response.data.status == 'success') {
            $scope.uploadMedia(response.data.report.ID); //upload the media files selected
            $scope.working = false;

            // reset the form and
            // mute the model
            $scope.report = {};
            evt.target.reset();
          }
        },
        function error(response, status, headers, config) {
          //notify alert, could not connect to remote server
        }
      );
  }


  $scope.onFileSelect = function($type, $files){
    $scope.invalid = {};

    if( $type == 'photo' ){
      if( ! files_are_valid($files, ['image/jpeg', 'image/png']) ){
        $scope.invalid.photo = 'Select only valid image files.';
        return;
      }
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
        $scope.invalid.video = 'Select only valid video files.';
        return;
      }
      $scope.report.video = $files;
    }

    if( $type == 'audio'){
      if( ! files_are_valid($files, ['audio/mp3','audio/mp4', 'audio/ogg']) ){
        $scope.invalid.audio = 'Select only valid audio files.';
        return;
      }
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

  /**
   * Upload media attachements
   *   
   * @param  string id the report id
   * @return void
   */
  $scope.uploadMedia = function(id){
    if( $scope.report.photos && $scope.report.photos.length > 0)
      ReportService.uploadMedia('image', $scope.report.photos, id)

    if( $scope.report.video && $scope.report.video.length > 0)
      ReportService.uploadMedia('video', $scope.report.video, id)

    if( $scope.report.audio && $scope.report.audio.length > 0)
      ReportService.uploadMedia('audio', $scope.report.audio, id)

  }

}]);
