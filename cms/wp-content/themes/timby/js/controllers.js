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

      $scope.viewReport = function(id){
        $scope.working = true;

        ReportService
          .findById(id)
          .then(
            function success(response, status, headers, config) {
              $scope.working = false;

              if (response.data.status == 'success') {
                $scope.report = response.data.report;
                console.log($scope.report);
              }
            },
            function error(response, status, headers, config) {
              //notify alert, could not connect to remote server
            }
          )
      }

      $scope.trustSrc = function(src){
        return $sce.trustAsResourceUrl(src);
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
