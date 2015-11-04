//ArtistsAdminCV.js
var artAdmApp = angular.module('ArtistsEditCV', ['ArtistsEditM', 'SpireFactoryModule', 'HomeLoginM', 'ngRoute', 'ngFileUpload']);

artAdmApp.controller('ArtistsEditCtrl', ['$scope', 'SpireFactory', 'ArtistsEditService', '$routeParams', 'HomeLoginService', '$location', 'HelperFactory',
										function($scope, SpireFactory, ArtistsEditService, $routeParams, HomeLoginService, $location, HelperFactory){

	$scope.tabLinks = [SpireFactory.getTabLink("profile", 
											   "Artist Profile", 
											    true, 
											   "ArtistsEdit/artistsProfileView.html"),
					   SpireFactory.getTabLink("videos", 
					  						   "Videos", 
					  						   false,
					  						   "ArtistsEdit/artistsVideosView.html")
					   ];
	$scope.activeTab = $scope.tabLinks[0].href;
	
	$scope.isActiveTab = function(tab){
		return $scope.activeTab == tab.href;
	};

	$scope.setActiveTab = function(tab){
		return $scope.activeTab = tab.href;
	};

	$scope.getActiveClass = function(tab){
		if($scope.isActiveTab(tab)){
			return "active";
		}	
		return "";
	}

	var controllerSetup = function(){
		if(!HomeLoginService.isConnected()){
			$location.path("#/");
		}
	};

	controllerSetup();
}])
.controller("EditProfileCtrl", ['$scope', 'SpireFactory', 'ArtistsEditService', '$routeParams', 'HomeLoginService', '$location', 'HelperFactory',
										function($scope, SpireFactory, ArtistsEditService, $routeParams, HomeLoginService, $location, HelperFactory){
	var ctrl = this;
	$scope.artistToEdit = {};

	$scope.fields = {         id: SpireFactory.getField("Id", "input", "disabled"), 
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
					   is_active: SpireFactory.getField("Active", "input", "disabled")};
	$scope.fieldsOrder = ['id', 'is_active', 'name', 'bio', 'area', 'twitter_id', 
	                      	'facebook_id', 'google_id', 'instagram_id',
	                      	'tumblr_id', 'img_url', 'url'];

	$scope.updateArtist = function(){
		ArtistsEditService.updateArtist($scope.artistToEdit)
		.then(function(response, status){
			if(response.ok){
				alert(response.data);
			}
			else{
				alert("Error Updating Artist");
			}
		});
	};

	ctrl.controllerSetup = function(){
		if(!HomeLoginService.isConnected()){
			$location.path("#/");
		}
		else{
			ArtistsEditService.getArtistFromId($routeParams.artistId)
			.then(function(response, status){
				if(response.ok){
					$scope.artistToEdit = response.data;
				}
				else{
					alert(response.data)
				}
			});
		}
	};

	ctrl.controllerSetup();

}])
.controller("EditVideosCtrl", ['$scope', 'SpireFactory', 'ArtistsEditService', '$routeParams', 'HomeLoginService', '$location', 'HelperFactory','ArtistsEditService', 'Webservice',
									function($scope, SpireFactory, ArtistsEditService, $routeParams, HomeLoginService, $location, HelperFactory, ArtistsEditService, Webservice){
	var ctrl = this;

	$scope.videos        = [];
	$scope.previewVideos = {};
	$scope.createVideo   = SpireFactory.getBlankVideo();
	$scope.videoToUpload = null;
	$scope.videoToEdit   = $scope.createVideo;
	$scope.sessionList   = [];
	$scope.isSubmLoading = false;

	$scope.fields = {         id: SpireFactory.getField("Id", "input", "disabled"), 
					        name: SpireFactory.getField("Name", "input", "text"),
					  session_id: SpireFactory.getField("Session", "select", null, []),
					  iframe_url: SpireFactory.getField("IFrame URL", "input", "text"),
					  youtube_id: SpireFactory.getField("Youtube ID", "input", "text"),
					   is_active: SpireFactory.getField("Active", "input", "disabled"),
					        file: SpireFactory.getField("Video File", "input", "file")
					};

	$scope.getFieldsOrder = function(){
		if($scope.isNewVideo($scope.videoToEdit)){
			return ['id', 'is_active', 'name', 'session_id'];
		}
		return  ['id', 'is_active', 'name', 'session_id', 'iframe_url'];
	};
	
	$scope.previewVideo = function(index){
		$scope.previewVideos[index] = !$scope.previewVideos[index];
	};
	
	$scope.setVideos = function(){
		$scope.isListLoading = true;
		ArtistsEditService.getVideosFromArtistId($routeParams.artistId)
		.then(function(promise){
			if(promise.ok){
				$scope.createVideo = SpireFactory.getBlankVideo();
				$scope.videos      = promise.data;
			}
			$scope.isListLoading = false;
		});
	};
	
	$scope.setSelectLists = function(){
		Webservice.getSessions()
		.success(function(data, status){
			$scope.fields.session_id.options = SpireFactory.getSessionArray(data);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.isNewVideo = function(){
		return HelperFactory.isNullOrUndefined($scope.videoToEdit.id) || $scope.videoToEdit.id == "";
	};
	
	$scope.isActiveClass = function(id){
		if(id == $scope.videoToEdit.id)
			return "admin-edit-active";
		return "";
	};
	
	$scope.updateVideo = function(videoToUpload){
		$scope.isSubmLoading = true;
		var promise;
		try{
			if($scope.isNewVideo() && HelperFactory.isNullOrUndefined(videoToUpload.$error)){
				console.log(videoToUpload);
				$scope.videoToEdit.artist_id = $routeParams.artistId;
				promise = ArtistsEditService.createVideo($scope.videoToEdit, videoToUpload, HomeLoginService.getToken());
			}
			else{
				$scope.videoToEdit.prepareForUpload();
				promise = ArtistsEditService.updateVideo($scope.videoToEdit, HomeLoginService.getToken());
			}
			promise
			.then(function(promise){
				if(promise.ok){
					alert(promise.data);
					$scope.setVideos();
				}
				$scope.isSubmLoading = false;
			});
		} catch(e){
			$scope.isSubmLoading = false;
		}

	};
	
	$scope.deleteVideo = function(){
		/*Webservice.deleteVideo($scope.videoToEdit.id, HomeLoginService.getToken())
		.success(function(data, status){
			alert(data);
			$scope.setVideos();
			$scope.setVideoToEdit($scope.createVideo);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});*/
	};
	
	$scope.setVideoToEdit = function(art){
		console.log(angular.toJson(art));
		$scope.videoToEdit = art;
	};
	
	$scope.sanitize = function(url){
		if(angular.isObject(url)){
			return url;
		}
		return HelperFactory.sanitizeUrl(url);
	};
	
	$scope.showView = function(){
		return HomeLoginService.isConnected();
	};

	ctrl.controllerSetup = function(){
		if(!HomeLoginService.isConnected()){
			$location.path("#/");
		}
		else{
			$scope.setVideos();
			$scope.setSelectLists();
		}
	};
	ctrl.controllerSetup();
}]);