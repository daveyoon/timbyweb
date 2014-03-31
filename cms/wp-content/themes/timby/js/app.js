angular.module('timby',[
    'ngRoute', 
    'ngSanitize',
    'textAngular',
    'checklist-model',
    'google-maps',
    'localytics.directives',
    'angularFileUpload',
    'timby.controllers',
    'timby.services',
    'timby.directives',
  ]
)
.constant('BASE_URL', document.body.getAttribute('data-template-url'))
.config(['$routeProvider', 'BASE_URL', '$sceDelegateProvider', function($routeProvider, BASE_URL, $sceDelegateProvider){
  
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
  

}]);