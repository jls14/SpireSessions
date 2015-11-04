//app.js
var app = angular.module('spire', ['ui.bootstrap', 'ngRoute', 'ngSanitize', 
                                   'NavbarCV', 'LoginCV', 'LoginM', 'SessionAdminCV',
                                   'ArtistAdminCV', 'VideoAdminCV']);

app.config(['$routeProvider', '$locationProvider', 
  function($routeProvider,$locationProvider){
    $routeProvider
      .when('/',{
        templateUrl:'Login/loginView.html',
        controller: 'LoginCtrl'
      })
      .when('/artist', {
        templateUrl:'Artist/artistAdminView.html',
        controller:'ArtistAdminCtrl'
      })
      .when('/session', {
        templateUrl:'Session/sessionAdminView.html',
        controller:'SessionAdminCtrl'
      })
      .when('/video', {
        templateUrl:'Video/videoAdminView.html',
        controller:'VideoAdminCtrl'
      })
      .otherwise({
		redirectTo:'/'
      });;
    //$locationProvider.html5Mode(true);
}]);

app.controller('MainCtrl', ['$scope', function($scope) {
}]);