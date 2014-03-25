angular.module('timby.services', [])

.factory('ReportService', ['$http','BASE_URL', function($http, BASE_URL) {
  return {
    findAll : function(){
      return $http.get(BASE_URL + '/ajax.php?action=get_new_reports');
    },

    findById : function(id){
      return $http.get(BASE_URL + '/ajax.php?action=get_report&id='+id)
    }
  }


}]);
