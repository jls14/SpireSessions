/*LoginM.js*/
var loginM = angular.module('LoginM', ['WebserviceModule']);

loginM.service('LoginService', ['$rootScope', 'Webservice', 
                                	function($rootScope, Webservice){
	var serv 	   = this;
	var adminUrl   = "/admin";
	var isLoggedIn = false;
	
	var token = "";
	
	this.redirectIfNotLoggedIn = function(){
		if(!isLoggedIn){
			window.location = adminUrl;
			console.log("true");
			return true;
		}
		console.log("false");
		return false;
	};
	
	this.checkIsLoggedIn = function(){
		return isLoggedIn;
	};
	
	this.login = function(user){
		Webservice.getLoginToken(user.username, user.password)
		.success(function(data, status){
			isLoggedIn = true;
			token = data;
			console.log(status + "-" + angular.toJson(data));
			$rootScope.$broadcast("LoginService.login", data);
		})
		.error(function(data, status){
			serv.isLoggedIn = true;
			console.log(status + "-" + angular.toJson(data));
			$rootScope.$broadcast("LoginService.login", data);
		})
	};
	
	this.logout = function(){
		token      = "";
		isLoggedIn = false;
		window.location = adminUrl;
	};
	
	this.getToken = function(){
		return token;
	};
	
	var redirectIfNotLoggedIn = function(){
		if(!isLoggedIn){
			window.location = adminUrl;
		}
	};
	/*this.checkToken = function(){
		return Webservice.getUserFromToken(serv.token)
		.success(function(data, status){
			serv.isLoggedIn = true;
			serv.token      = data;
			$rootScope.$broadcast("LoginService.login", data);
		})
		.error(function(data, status){
			serv.isLoggedIn = true;
			console.log(status + "-" + angular.toJson(data));
			$rootScope.$broadcast("LoginService.login", data);
		})
	};*/
}]);