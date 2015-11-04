var navCV = angular.module('NavbarCV', [ 'FbModule', 'ui.bootstrap', 'HomeLoginM', 'SessionM', 'SpireFactoryModule', 'ArtistsEditM']);

// creates the custom directive to show the navbar
navCV.directive('navbarView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Navbar/navbarView.html',
		controller : 'NavbarCtrl'
	};
});

// handles navbar's gui interaction properties
navCV.controller('NavbarCtrl', [ '$scope', '$log', '$rootScope', '$location', '$modal', 'SpireFactory', 'HomeLoginService', 'SessionService', 'ArtistsEditService',
                                 	function($scope, $log, $rootScope, $location, $modal, SpireFactory, HomeLoginService, SessionService, ArtistsEditService) {
	
			$scope.isCollapsed = true;
			$scope.isConnected = HomeLoginService.isConnected();

			$scope.navLinks = [SpireFactory.getNavLink("#/", "Sessions"),
                     		   SpireFactory.getNavLink("#/artists", "Artists")];

            $scope.artists = [];

			var setIsConnected = function(){
				$scope.isConnected = HomeLoginService.isConnected();
			};

			$scope.fbLogin = function() {
				HomeLoginService.login();
			};
			$scope.fbLogout = function() {
				HomeLoginService.logout();
			};
			$scope.toggleCollapsed = function() {
				$scope.isCollapsed = !$scope.isCollapsed;
			};
			$scope.collapse = function() {
				$scope.isCollapsed = true;
			};
			$scope.goTo = function(path) {
				$location.path(path);
			};
			$scope.isUserArtist = function(){
				return HomeLoginService.isUserArtist();
			};
			
			var setArtistsLinks = function(){
				ArtistsEditService.getArtistFromUser()
				.then(function(response, status){
					if(response.ok){
						$scope.artists = response.data;
					}
				});
			};
			
			$rootScope.$on('onMustLogin', function(event){
				$scope.openSignInUpModal('error');
			});
			$rootScope.$on('HomeLoginService.setToken', function(event){
				setIsConnected();
				setArtistsLinks();
			});
			
			$scope.openSignInUpModal = function(type) {
			    var modalInstance = $modal.open({
					      templateUrl: 'Navbar/signInUpModal.html',
					      	     size: 'sm',
					          resolve:{
					    	  			type:function(){
					    		  				return type;
					    	  			} 
					      	  },
					          controller: function($scope, $rootScope, $modalInstance, type, HomeLoginService){
							  		$scope.type  = type;
									
							  		$scope.fields = {         id: SpireFactory.getField("Id", "p"), 
													        name: SpireFactory.getField("Name", "input", "text"), 
													         bio: SpireFactory.getField("Bio", "input","text"),
													        area: SpireFactory.getField("Area", "input","text"), 
													  twitter_id: SpireFactory.getField("Twitter ID", "input", "text"),
													 facebook_id: SpireFactory.getField("Facebook ID", "input", "text"),
													   google_id: SpireFactory.getField("Google ID", "input", "text"),
													instagram_id: SpireFactory.getField("Instagram ID", "input", "text"),
													   tumblr_id: SpireFactory.getField("Tumblr ID", "input", "text"),
													     img_url: SpireFactory.getField("Img URL", "input", "text"),
													      	 url: SpireFactory.getField("URL", "input", "text")};
									$scope.fieldsOrder = ['name', 'facebook_id', 'img_url', 'url'];
									$scope.newArtist = {name:'', facebook_id:'', img_url:'', url:''};

							  		$scope.isConnected = function(){
							  			return HomeLoginService.isConnected();	
							  		} 

							  		$scope.signUpResult = {show:null, message:null, class:null};

							 	 	$scope.submit = function () {
								  		HomeLoginService.signupArtist($scope.newArtist)
							    		.then(function(servResponse){
							    			$scope.signUpResult.show = true;
							    			if(servResponse.ok){
								    			$scope.signUpResult.message = "A request to the admin has been submitted.";
								    			$scope.signUpResult.class   = "success";							    				
							    			}
							    			else{
							    				$log.error(angular.toJson(servResponse));
							    				$scope.signUpResult.message = "Contact support@spiresessions.com";
								    			$scope.signUpResult.class   = "danger";	
							    			}

							    		})
							  		};
							
							  		$scope.close = function () {
								  		$modalInstance.dismiss();
							  		};

							  		$scope.signIn = function (){
							  			HomeLoginService.login();
							  		};
							  
							  		$rootScope.$on('onFBResponseChange', function(event){
								  		$modalInstance.dismiss();
							  		});
						  	  }
			    });

			    modalInstance.result.then(
			    	function (artist) {
			    		/*submission*/
			    		console.log(angular.toJson(artist));

			    	}, 
			    	function () {/*dismiss*/}
			    );
			};

} ]);
