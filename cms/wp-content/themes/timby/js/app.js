angular.module('timby',[
    'ngRoute',
    'ngIdle',
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
.config(
  ['$routeProvider', 'wordpressProvider','BASE_URL', '$sceDelegateProvider','datepickerConfig', '$provide', '$keepaliveProvider', '$idleProvider',
  function($routeProvider, wordpressProvider, BASE_URL, $sceDelegateProvider, datepickerConfig, $provide, $keepaliveProvider, $idleProvider){

  // configure ng-idle
  $idleProvider.idleDuration(1800);
  $idleProvider.warningDuration(0);

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

  // configure default textAngular options, see https://github.com/fraywing/textAngular/wiki/Setting-Defaults
  $provide.decorator('taOptions', ['$delegate', function(taOptions){
      // $delegate is the taOptions we are decorating
      // here we override the default toolbars and classes specified in taOptions.
      taOptions.toolbar = [
        ['bold', 'italics','underline', 'p'],
        ['ul', 'ol', 'quote', 'insertLink'],
        ['redo', 'undo']
      ];
      taOptions.classes = {
        focussed: 'focussed',
        toolbar: 'btn-toolbar',
        toolbarGroup: 'btn-group',
        toolbarButton: 'btn btn-default',
        toolbarButtonActive: 'active',
        disabled: 'disabled',
        textEditor: 'form-control',
        htmlEditor: 'form-control'
      };
      return taOptions; // whatever you return will be the taOptions
  }]);

}])
.run(['$rootScope', '$window', 'wordpress','$location','$sce','$idle', function($rootScope, $window, wordpressProvider, $location, $sce, $idle){

  $rootScope.baseURL = angular.element('body').attr('data-template-url');

  // redirect all non logged in users
  // this is when a route changes
  $rootScope.$on('$routeChangeStart', function(event, next, current){
    //validate an existing session
    if( !$window.sessionStorage.user_id && !$window.sessionStorage.user_token ) {
      $location.path( "/" );       
    }

  });

  $idle.watch();
  $rootScope.$on('$idleTimeout', function(){
    $window.sessionStorage.clear();
    $location.path( "/" );
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

  // App title
  $rootScope.title = "Timby.org | Reporting and Visualization tool";

  // mark a given location as trusted
  $rootScope.trustSrc = function (src) {
    return $sce.trustAsResourceUrl(src);
  }

}]);

