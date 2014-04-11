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
        authenticate : false,
        resolve: ['$q', '$location', '$window', 
          function($q, $location, $window) {
            var deferred = $q.defer(); 
            if ($window.sessionStorage.user_id && $window.sessionStorage.user_token) {
               $location.path('/dashboard');
            }
            deferred.resolve();
            return deferred.promise;
          }
        ]
      }
    )
    .when('/dashboard',
      {
        templateUrl : BASE_URL + '/templates/dashboard.html',
        controller : 'MainController',
        resolve : {
          'status' : 'checkAuthStatus'
        }
      }
    )
    .when('/dashboard/map',
      {
        templateUrl : BASE_URL + '/templates/dashboard.map.html',
        controller : 'MainController',
        resolve : {
          'status' : 'checkAuthStatus'
        }
      }
    )
    .when('/addreport',
      {
        templateUrl : BASE_URL + '/templates/add.report.html',
        controller : 'ReportController',
        resolve : {
          'status' : 'checkAuthStatus'
        }
      }
    )
    .when('/story/create',
      {
        templateUrl : BASE_URL + '/templates/story.create.html',
        controller : 'StoryController',
        resolve : {
          status : 'checkAuthStatus',
          resolvedata : function(){
            // initialize a empty story object
            return {
              story : {
                content : [
                  {
                    type : 'editor',
                    text : ''
                  }
                ]
              }
            }
            
          }
        }
      }
    )
    .when('/story/edit/:id/',
      {
        templateUrl : BASE_URL + '/templates/story.edit.html',
        controller : 'StoryController',
        resolve : {
          status : 'checkAuthStatus',
          resolvedata : function(StoryService,$route){
            return StoryService
                      .findById($route.current.params.id)
                      .then(function(response){
                        return { story : response.data.story }
                      });
          }
        }
      }
    )
    .when('/stories',
      {
        templateUrl : BASE_URL + '/templates/story.list.html',
        controller : 'StoryController',
        resolve : {
          status : 'checkAuthStatus',
          resolvedata : function(StoryService,$route){
            return StoryService
                    .findAll()
                    .then(function(response){
                      return { stories : response.data.stories };
                    })
          }
        }
      }
    )

  $routeProvider.otherwise({ redirectTo : '/'});

  datepickerConfig.templateUrl = BASE_URL + '/js/libs/angularui-bootstrap/templates/';

}])
.run(['$rootScope', '$window', 'wordpress','$location', function($rootScope, $window, wordpressProvider, $location){

  // redirect all non logged in users
  // this is when a route changes
  $rootScope.$on('$routeChangeStart', function(event, next, current){
    //validate an existing session
    if( !$window.sessionStorage.user_id && !$window.sessionStorage.user_token ) {
      $location.path( "/" );       
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

