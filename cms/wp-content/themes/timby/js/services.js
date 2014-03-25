angular.module('timby.services', [])

.factory('ReportService', ['$http','$window', function($http, $window) {
  return {
    findAll : function(){
      return $http.get($window.wp_data.template_url + '/ajax.php?action=get_new_reports');
    },

    findById : function(id){
      return $http.get($window.wp_data.template_url + '/ajax.php?action=get_report&id='+id)
    }
  }
}])
.factory('AuthService', ['$http','$window', function($http, $window) {
  var _self = this, logged_in = false;

  return {
    isAuthenticated : function(){
      return _self.logged_in
    },
    login : function(user, password){
      return  $http.post(
                $window.wp_data.template_url + '/ajax.php?action=login', 
                {
                  'user' : user,
                  'password' : password,
                  'nonce' : $window.wp_data.nonce,
                }
              );

    },
    logout : function(){

    }
  }
}]);
