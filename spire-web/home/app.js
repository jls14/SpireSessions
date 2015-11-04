var app = angular.module('spire', ['ui.bootstrap', 'ngRoute', 'ngAnimate', 'ngSanitize', 
                                   'NavbarCV','SessionCV','SessionM','ArtistsCV','ArtistsM',
                                   'ArtistsEditCV', 'ArtistsEditM', 'ngFileUpload']);

app.config(['$routeProvider', '$locationProvider', 
  function($routeProvider,$locationProvider){
    $routeProvider
      .when('/',{
        templateUrl:'Session/sessionChoiceView.html',
         controller:'SessionChoiceCtrl'        
      })
      .when('/session/:sessionId',{
        templateUrl:'Session/sessionView.html',
         controller: 'SessionCtrl'
      })
      .when('/artists', {
        templateUrl:'Artists/artistsView.html',
         controller:'ArtistsCtrl'
      })
      .when('/artists-edit/:artistId', {
        templateUrl:'ArtistsEdit/artistsEditView.html',
         controller:'ArtistsEditCtrl'
      })
      .otherwise({
		    redirectTo: '/'
      });
    //$locationProvider.html5Mode(true);
}]);

app.controller('MainCtrl', ['$scope', function($scope) {
}]);