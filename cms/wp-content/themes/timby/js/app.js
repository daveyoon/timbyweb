angular.module('timby',[
    'ngRoute',
    'ngSanitize',
    'ngAnimate',
    'textAngular',
    'checklist-model',
    'google-maps',
    'localytics.directives',
    'angularFileUpload',
    'ui.bootstrap',
    'toaster',
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
        authenticate : true
      }
    )
    .when('/addreport',
      {
        templateUrl : BASE_URL + '/templates/add.report.html',
        controller : 'ReportController',
        authenticate : true
      }
    )
    .when('/story/create',
      {
        templateUrl : BASE_URL + '/templates/story.create.html',
        controller : 'ReportController',
        authenticate : true
      }
    )
    .when('/story',
      {
        templateUrl : BASE_URL + '/templates/story.list.html',
        controller : 'ReportController',
        authenticate : true
      }
    )

  $routeProvider.otherwise({ redirectTo : '/'});

  datepickerConfig.templateUrl = BASE_URL + '/js/libs/angularui-bootstrap/templates/';

}])
.run(['$rootScope', '$window', 'wordpress','$location','AuthService', function($rootScope, $window, wordpressProvider, $location, AuthService){

  // redirect all non logged in users
  $rootScope.$on('$routeChangeStart', function(event, next, current){
    if( next.$$route.authenticate){
      
      //validate an existing session
      if( $window.sessionStorage.user_id && $window.sessionStorage.user_token ) {
        AuthService
          .tokenCheck()
          .then(function(response){
            if( response.data.status == 'error'){
              $location.path( "/" );
            }
          });        
      } else{
        $location.path( "/" );
      }
    }
    
  });

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
