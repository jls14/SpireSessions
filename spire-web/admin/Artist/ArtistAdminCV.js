var artistAdmin = angular.module('ArtistAdminCV', ['WebserviceModule', 'SpireFactoryModule']);

artistAdmin.controller('ArtistAdminCtrl', ['$scope', '$rootScope','Webservice', 'SpireFactory', 'HelperFactory', 'LoginService',
                                             	function($scope, $rootScope, Webservice, SpireFactory, HelperFactory, LoginService){
	
	$scope.opts          = {};
	$scope.artists       = [];
	$scope.createArtist  = SpireFactory.getNewArtist();
	$scope.artistToEdit  = $scope.createArtist;
	$scope.isListLoading = false;
	
	$scope.setArtists = function(){
		$scope.isListLoading  = true;
		Webservice.getArtists()
		.success(function(data, status){
			$scope.createArtist = SpireFactory.getNewArtist();
			$scope.artists = SpireFactory.getArtistArray(data);
			$scope.isListLoading = false;
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
			$scope.isListLoading = false;
		});
	};
	
	$scope.isNewArtist = function(){
		return HelperFactory.isNullOrUndefined($scope.artistToEdit.id);
	};
	
	$scope.isActiveClass = function(id){
		if(id == $scope.artistToEdit.id)
			return "admin-edit-active";
		return "";
	};
	
	$scope.updateArtist = function(){
		$scope.artistToEdit.prepareForUpload();
		var promise;
		if($scope.isNewArtist()){
			promise = Webservice.createArtist($scope.artistToEdit, LoginService.getToken())
		}
		else{
			promise = Webservice.updateArtist($scope.artistToEdit, LoginService.getToken())
		}
		
		promise
		.success(function(data, status){
			alert(data);
			$scope.setArtists();
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.deleteArtist = function(){
		Webservice.deleteArtist($scope.artistToEdit.id, LoginService.getToken())
		.success(function(data, status){
			alert(data);
			$scope.setArtists();
			$scope.setArtistToEdit($scope.createArtist);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
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
					      	 url: SpireFactory.getField("URL", "input", "text"),
					   is_active: SpireFactory.getField("Active", "input", "checkbox")};
	$scope.fieldsOrder = ['id', 'is_active', 'name', 'bio', 'area', 'twitter_id', 
	                      	'facebook_id', 'google_id', 'instagram_id',
	                      	'tumblr_id', 'img_url', 'url'];
					 
	$scope.setArtistToEdit = function(art){
		console.log(angular.toJson(art));
		$scope.artistToEdit = art;
	};
	
	$scope.showView = function(){
		return LoginService.checkIsLoggedIn();
	};

	if(!LoginService.redirectIfNotLoggedIn()){
		$scope.setArtists();
	}
}]);
