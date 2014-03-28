angular.module('timby.services', [])
.factory('ReportService', ['$http','$window', function($http, $window) {
  return {
    findAll : function(){
      return $http.get($window.wp_data.template_url + '/ajax.php?action=get_new_reports');
    },

    findById : function(id){
      return $http.get($window.wp_data.template_url + '/ajax.php?action=get_report&id='+id)
    },
    update : function(report){
      return $http.post(
        $window.wp_data.template_url + '/ajax.php?action=update_report',
        {
          'ID' : report.ID,
          'post_title' : report.post_title,
          'post_content' : report.post_content,
          'custom_fields' : {
            '_cmb_verified' : report.verified ? 'on' : ''
          },
          'taxonomies' : {
            'sector' : report.sectors,
            'entity' : report.entities,
            'categorie' : report.categories
          },
          'nonce' : $window.wp_data.nonce,
        }
      )
    },
    getAllTerms : function(){
      return $http.get($window.wp_data.template_url + '/ajax.php?action=get_all_terms');
    },
    create : function(report){
      return $http.post(
        $window.wp_data.template_url + '/ajax.php?action=create_report',
        {
          'post_title' : report.title,
          'post_content' : report.description,
          'taxonomies' : {
            'sector' : report.sectors,
            'entity' : report.entities
          },
          'nonce' : $window.wp_data.nonce,
        }
      );
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
