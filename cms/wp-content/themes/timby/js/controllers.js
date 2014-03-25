angular.module('timby.controllers', [])

.controller('HomepageController', 
  ['$scope', '$rootScope', 'ReportService', '$sce',
  function($scope, $rootScope,ReportService, $sce){
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

}])

