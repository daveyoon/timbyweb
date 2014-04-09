angular.module('timby.directives', [])
.directive('reportcard', function(){
  return  {
    restrict: 'E',
    templateUrl : 'report_template' // the id of the template
  }
})