angular.module('timby',[
    'ngRoute', 
    'ngSanitize',
    'textAngular',
    'checklist-model',
    'google-maps',
    'localytics.directives',
    'angularFileUpload',
    'ui.bootstrap',
    'timby.controllers',
    'timby.services',
    'timby.directives',
    'timby.filters',
  ]
)
.constant('BASE_URL', document.body.getAttribute('data-template-url'))
.config(['$routeProvider', 'wordpressProvider','BASE_URL', '$sceDelegateProvider','datepickerConfig', function($routeProvider, wordpressProvider, BASE_URL, $sceDelegateProvider, datepickerConfig){
  
  $sceDelegateProvider.resourceUrlWhitelist([
   'self',
   "http://api.soundcloud.com/**"
  ]);

  $routeProvider
    .when('/', 
      { 
        templateUrl : BASE_URL + '/templates/login.html',
        controller : ['$location', 'AuthService', 
        function($location, AuthService){
          if( AuthService.isAuthenticated() )
            $location.path( "/dashboard" )
        }],
        authenticate : false
      }
    )
    .when('/dashboard', 
      { 
        templateUrl : BASE_URL + '/templates/dashboard.html',
        controller : 'MainController',
        authenticate : false
      }
    )
    .when('/addreport', 
      { 
        templateUrl : BASE_URL + '/templates/add.report.html',
        controller : 'ReportController',
        authenticate : false
      }
    )

  $routeProvider.otherwise({ redirectTo : '/'});
  
  datepickerConfig.templateUrl = BASE_URL + '/js/libs/angularui-bootstrap/templates/';

}])
.run(['$rootScope', 'wordpress', function($rootScope, wordpressProvider){
  // fetches necessary wordpress data
  // we require for our app to run, 
  // this includes taxonomies, api users
  wordpressProvider
  .getInfo()
  .then(function(response){
    if (response.data.status == 'success') {
      $rootScope.wordpress = response.data.data
    }
  }); 
  
}]);