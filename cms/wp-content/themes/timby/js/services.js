angular.module('timby.services', [])
.factory('ReportService', ['$http','$window', '$upload','AuthService', function($http, $window, $upload, AuthService) {
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
            'category' : report.categories
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
          'post_author' : AuthService.user.ID,
          'taxonomies' : {
            'sector' : report.sectors,
            'entity' : report.entities
          },
          'nonce' : $window.wp_data.nonce,
          'custom_fields' : {
            '_lat' : report.lat,
            '_lng' : report.lng,
            '_reporter_id' : report.reporter.id,
            '_date_reported' : new Date(report.date_reported).toISOString()
          },
        }
      );
    },

    uploadMedia : function(mediatype, files, reportid){
      for(var i = 0; i < files.length; i++){
        var file = files[i];
        $upload.upload({
          url: $window.wp_data.template_url + '/ajax.php?action=upload_media',
          method: 'POST',
          data: {
            media_type : mediatype,
            reportid   : reportid,
            nonce      : $window.wp_data.nonce
          },
          file: file,
        }).progress(function(evt) {
          console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total) );
        }).success(function(data, status, headers, config) {
          // file is uploaded successfully
          console.log(data);
        });

      }
    }

  }
}])
.factory('AuthService', ['$http','$window', function($http, $window) {
  var _self = this, logged_in = false;

  return {
    user: {},
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
}])
.provider('wordpress', function wordpressProvider(){

  this.$get = ['$http','$window', function($http, $window) {
    return {
      getInfo : function(){
        return $http.post($window.wp_data.template_url + '/ajax.php?action=info')
      }
    };
  }];

});
