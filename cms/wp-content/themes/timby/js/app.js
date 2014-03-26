require.config({
  paths: {
    'angular': './bower_components/angular/angular',
    'lodash': './bower_components/lodash/dist/lodash',
    'scribe': './bower_components/scribe/scribe',
    'scribe-plugin-blockquote-command': './bower_components/scribe-plugin-blockquote-command/scribe-plugin-blockquote-command',
    'scribe-plugin-curly-quotes': './bower_components/scribe-plugin-curly-quotes/scribe-plugin-curly-quotes',
    'scribe-plugin-formatter-plain-text-convert-new-lines-to-html': './bower_components/scribe-plugin-formatter-plain-text-convert-new-lines-to-html/scribe-plugin-formatter-plain-text-convert-new-lines-to-html',
    'scribe-plugin-heading-command': './bower_components/scribe-plugin-heading-command/scribe-plugin-heading-command',
    'scribe-plugin-intelligent-unlink-command': './bower_components/scribe-plugin-intelligent-unlink-command/scribe-plugin-intelligent-unlink-command',
    'scribe-plugin-keyboard-shortcuts': './bower_components/scribe-plugin-keyboard-shortcuts/scribe-plugin-keyboard-shortcuts',
    'scribe-plugin-link-prompt-command': './bower_components/scribe-plugin-link-prompt-command/scribe-plugin-link-prompt-command',
    'scribe-plugin-sanitizer': './bower_components/scribe-plugin-sanitizer/scribe-plugin-sanitizer',
    'scribe-plugin-smart-lists': './bower_components/scribe-plugin-smart-lists/scribe-plugin-smart-lists',
    'scribe-plugin-toolbar': './bower_components/scribe-plugin-toolbar/scribe-plugin-toolbar'
  },
  shim: {
    'angular' : { exports: 'angular' }
  }
});

require([
  'angular',
  'scribe',
  'scribe-plugin-blockquote-command',
  'scribe-plugin-curly-quotes',
  'scribe-plugin-formatter-plain-text-convert-new-lines-to-html',
  'scribe-plugin-heading-command',
  'scribe-plugin-intelligent-unlink-command',
  'scribe-plugin-keyboard-shortcuts',
  'scribe-plugin-link-prompt-command',
  'scribe-plugin-sanitizer',
  'scribe-plugin-toolbar',
  'lodash'
], function(
  angular,
  Scribe,
  scribePluginBlockquoteCommand,
  scribePluginCurlyQuotes,
  scribePluginFormatterPlainTextConvertNewLinesToHtml,
  scribePluginHeadingCommand,
  scribePluginIntelligentUnlinkCommand,
  scribePluginKeyboardShortcuts,
  scribePluginLinkPromptCommand,
  scribePluginSanitizer,
  scribePluginToolbar,
  _
) {
  angular.module('timby',[
    'ngSanitize',
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

    $routeProvider.otherwise({ redirectTo : '/'});

  }]);  
}


