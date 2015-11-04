var fbApp = angular.module('FbModule', ['SpireFactoryModule']);

fbApp.service('FbService', ['$log', 'HelperFactory','$rootScope', 
                            	function($log, HelperFactory, $rootScope){
	var FbService = this;
	
	this.loginResponse = null;
	this.isFBInit 	   = false;
	
	var setLoginResponse = function(resp){
		FbService.loginResponse = resp;
		$rootScope.$broadcast("onFBResponseChange");
	};

	this.resetLoginResponse = function(){
		try{
			FB.getLoginStatus(function(response) {
				setLoginResponse(response);
			});
		} catch(e){
			$log.error("FbService: FB is not set");
		}
	};
	
	this.checkIsConnected = function(){
		try{
			return FbService.loginResponse.status === "connected";
		} catch(e){
			$log.error("FbService: loginResponse is not set");
		}
		
	};
	
	this.fbLogout = function(){
		if(this.checkIsConnected()){
			try{
				FB.logout(function(response){
					setLoginResponse(response);
				});
			} catch(e){
				$log.error("FbService: FB is not set");
			}
		}
		else{
			$log.error("Already Not Connected");
		}
	};
	
	this.fbLogin = function(){
		if(!this.checkIsConnected()){
			try{
				FB.login(function(response){
					setLoginResponse(response);
				},{scope: 'public_profile,email'});
			} catch(e){
				$log.error("FbService: FB is not set");
			}
		}
		else{
			$log.error("Already Connected");
		}
	};
	
	$rootScope.$on("onFBResponseChange", function(event){
		FB.api("/"+FbService.loginResponse.userID,
				function (response) {
		      		if (response && !response.error) {
		      			/* handle the result */
		      		}
		    	}
		);
	});
	
	window.fbAsyncInit = function() {
		FB.init({
			appId : '879340512104772',
			xfbml : true,
			version : 'v2.3'
		});
		FB.getLoginStatus(function(response) {
			setLoginResponse(response);
		});
	};
	
}]);
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3&appId=879340512104772";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));