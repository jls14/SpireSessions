var navCV = angular.module('NavbarCV', ['SpireFactoryModule', 'LoginM']);

//creates the custom directive to show the navbar
navCV.directive('navbarView', function(){
  return {
    restrict:'E',
    templateUrl:'Navbar/navbarView.html',
    controller:'NavbarCtrl'
  };
});
//handles navbar's gui interaction properties
navCV.controller('NavbarCtrl', ['$scope','$location', 'SpireFactory', 'LoginService', 
                                function($scope, $location, SpireFactory, LoginService){
  $scope.isCollapsed = true;
  
  $scope.toggleCollapsed = function(){
    $scope.isCollapsed = !$scope.isCollapsed;
  };
  
  $scope.collapse = function(){
	  $scope.isCollapsed = true;
  };
  
  $scope.goTo = function (path) {
      $location.path(path);
  };
  
  $scope.showNavLinks = function(){
	  return LoginService.checkIsLoggedIn();
  };
  
  $scope.logout = function(){
	  return LoginService.logout();
  };

  $scope.navLinks = [SpireFactory.getNavLink("#/session", "Session"),
                     SpireFactory.getNavLink("#/artist", "Artist"),
                     SpireFactory.getNavLink("#/video", "Video"),
                     SpireFactory.getNavLink("#/user", "User")];
  
}]);
