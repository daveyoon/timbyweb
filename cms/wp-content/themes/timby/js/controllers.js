angular.module('timby.controllers', [])
.controller('MainController', 
  ['$scope', '$rootScope', 'ReportService', '$sce',
    function($scope, $rootScope,ReportService, $sce){
      $scope.authenticated = false;

      // redirect all non logged in users
      $rootScope.$on('$routeChangeStart', function(event, next, current){
        if( next.authenticate && !AuthService.isAuthenticated()){
          $location.path( "/login" )
        }
      })

      $rootScope.title = "Timby.org | Reporting and Visualization tool";

      /**
       * Fetches all terms 
       * for taxonomies sector, entity
       * 
       * @return {[type]} [description]
       */
      $scope.getAllTerms = function(){
        ReportService
          .getAllTerms()
          .then(
            function success(response, status, headers, config) {
              if (response.data.status == 'success') {
                $scope.terms = response.data.terms;
              }
            },
            function error(response, status, headers, config) {
              //notify alert, could not connect to remote server
            }
          )
      };
      $scope.getAllTerms();

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

      $scope.addEntity = function(){
        if (angular.isArray($scope.report.entities)) {
          for(i=0; i<=$scope.report.entities.length; i++){
            if(angular.equals($scope.termselected, $scope.report.entities[i])){
              $scope.tagexists = true;
              return;
            }
          }
          $scope.report.entities.push($scope.termselected);
        }
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
);
