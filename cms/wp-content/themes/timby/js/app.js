angular.module('timby',[
  'ngRoute',
  'timby.controllers',
  'timby.services',
  'timby.directives'
])
.constant('BASE_URL', document.body.getAttribute('data-template-url'))
.config(['$routeProvider', 'BASE_URL', '$sceDelegateProvider', function($routeProvider, BASE_URL, $sceDelegateProvider){
  
  $sceDelegateProvider.resourceUrlWhitelist([
   'self',
   "http://api.soundcloud.com/**"
  ]);

  $routeProvider
    .when('/', 
      { 
        templateUrl : BASE_URL + '/templates/home.html',
        controller : 'HomepageController'
      }
    )

  $routeProvider.otherwise({ redirectTo : '/'});

}]);
