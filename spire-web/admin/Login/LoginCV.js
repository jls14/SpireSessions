/*LoginCV.js*/
var loginCV = angular.module('LoginCV', ['WebserviceModule', 'LoginM']);

loginCV.directive('loginView', function(){
	return {
		restrict:'E',
    	templateUrl:'Login/loginView.html',
    	controller:'LoginCtrl'
	};
});
loginCV.controller('LoginCtrl', ['$scope', '$rootScope', 'Webservice', 'LoginService', '$location',
                                 		function($scope, $rootScope, Webservice, LoginService, $location){
	$scope.title    = "Admin Login";
	$scope.user     = {username:"", password:""};
	$scope.remember = false;

	$scope.login = function(){
		$scope.user.password = hex_sha512($scope.user.password);
		LoginService.login($scope.user);
	};
	
	$rootScope.$on('LoginService.login', function(event){
		redirectIfLoggedIn();
	});
	
	var redirectIfLoggedIn = function(){
		console.log("session");
		if(LoginService.checkIsLoggedIn()){
			$location.url($location.url()+"session");
		}
	};
	
	redirectIfLoggedIn();
}]);