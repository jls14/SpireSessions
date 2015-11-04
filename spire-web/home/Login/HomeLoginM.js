/*LoginM.js*/
var loginM = angular.module('HomeLoginM', ['WebserviceModule', 'FbModule', 'SpireFactoryModule']);

loginM.service('HomeLoginService', ['$rootScope', 'Webservice', 'FbService', 'HelperFactory', '$log',
                                	function($rootScope, Webservice, FbService, HelperFactory, $log){
	var serv    = this;
	var token   = null;
	var session = null;
	var user    = null;
	
	var setToken = function(tok){
		token = tok;
		$rootScope.$broadcast('HomeLoginService.setToken');
	};

	var setUser = function(usr){
		user = usr;
		$rootScope.$broadcast('HomeLoginService.setUser');
	};
	
	this.getToken = function(){
		return token;
	}; 

	this.getUser = function(){
		return user;
	};

	this.isConnected = function(){
		return !HelperFactory.isNullOrUndefined(token);
	};

	this.logout = function(){
		FbService.fbLogout();
	};

	this.login = function(){
		FbService.fbLogin();
	};

	this.refreshUser = function(){
		if(!HelperFactory.isNullOrUndefined(user)){
			Webservice.getUser(user.id)
			.success(function(data, status){
				setUser(data);
			})
			.error(function(data, status){
				$log.error("HomeLoginService.refreshUser: "+status+ "-" + data);
			});
		}
		else{
			$log.error("HomeLoginService.refreshUser: user not set yet");
		}
	};

	this.isUserArtist = function(){
		if(!HelperFactory.isNullOrUndefined(user)){
			return user.user_type_nbr == 1;
		}
		return false;
	};

	this.signupArtist = function(artist) {
	    var deferred = $q.defer();
	    Webservice.signupArtist(artist, HomeLoginService.getToken())
	  	.success(function(data, status){
	      deferred.resolve(HelperFactory.servResponse(true, data));
	      HomeLoginService.refreshUser();
	  	})
	  	.error(function(data, status){
	  		deferred.resolve(HelperFactory.servResponse(false, 'Error occured: '+angular.toJson(data)));
	  	});
	  	return deferred.promise;
  	};

	var setTokenFromWs = function(){
		if(FbService.checkIsConnected() /*&& !HelperFactory.isNullOrUndefined(session)*/){
			Webservice.getLoginTokenFb(FbService.loginResponse.authResponse, session)
			.success(function(data, status){
				setToken(data.token);
				setUser(data.user);
			})
			.error(function(data, status){
				Webservice.handleHttpError(data, status);
			});
		}
		else{
			setToken(null);
		}
	};

	$rootScope.$on('onFBResponseChange', function(event){
		setTokenFromWs();
	});
	$rootScope.$on('setSessionCtrl', function(event, session){
		session = session.id;
		setTokenFromWs();
	});
}]);