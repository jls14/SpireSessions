var videoAdmin = angular.module('VideoAdminCV', ['WebserviceModule', 'SpireFactoryModule']);

videoAdmin.controller('VideoAdminCtrl', ['$scope', '$rootScope','Webservice', 'SpireFactory', 'HelperFactory', 'LoginService',
                                             	function($scope, $rootScope, Webservice, SpireFactory, HelperFactory, LoginService){
	
	$scope.videos      = [];
	$scope.createVideo = SpireFactory.getNewVideo();
	$scope.videoToEdit = $scope.createVideo;
	$scope.sessionList = [];
	$scope.artistList  = [];
	$scope.isListLoading = false;
	$scope.showPreview = false;
	
	$scope.previewVideo = function(){
		$scope.showPreview = !$scope.showPreview;
	};
	
	$scope.setVideos = function(){
		$scope.isListLoading = true;
		Webservice.getVideos()
		.success(function(data, status){
			$scope.createVideo = SpireFactory.getNewVideo();
			$scope.videos      = SpireFactory.getVideoArray(data);
			$scope.isListLoading = false;
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
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
		Webservice.getArtists()
		.success(function(data, status){
			$scope.fields.artist_id.options = SpireFactory.getArtistArray(data);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.isNewVideo = function(){
		return HelperFactory.isNullOrUndefined($scope.videoToEdit.id);
	};
	
	$scope.isActiveClass = function(id){
		if(id == $scope.videoToEdit.id)
			return "admin-edit-active";
		return "";
	};
	
	$scope.updateVideo = function(){
		$scope.videoToEdit.prepareForUpload();
		var promise;
		if($scope.isNewVideo()){
			promise = Webservice.createVideo($scope.videoToEdit, LoginService.getToken())
		}
		else{
			promise = Webservice.updateVideo($scope.videoToEdit, LoginService.getToken())
		}
		promise
		.success(function(data, status){
			alert(data);
			$scope.setVideos();
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.deleteVideo = function(){
		Webservice.deleteVideo($scope.videoToEdit.id, LoginService.getToken())
		.success(function(data, status){
			alert(data);
			$scope.setVideos();
			$scope.setVideoToEdit($scope.createVideo);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};

	$scope.isFileValid = function(){
		return !HelperFactory.isNullOrUndefined($scope.videoToEdit.file);
	};
	
	$scope.fields = {         id: SpireFactory.getField("Id", "p"), 
					        name: SpireFactory.getField("Name", "input", "text"), 
					   artist_id: SpireFactory.getField("Artist", "select", null, []),
					  session_id: SpireFactory.getField("Session", "select", null, []),
					  iframe_url: SpireFactory.getField("IFrame URL", "input", "text"),
					  youtube_id: SpireFactory.getField("Youtube ID", "input", "text"),
					   is_active: SpireFactory.getField("Active", "input", "checkbox"),
					   		file: SpireFactory.getField("Video File", "a", "")
					  };
	$scope.fieldsOrder = ['id',  'is_active', 'name', 'artist_id', 'session_id', 'iframe_url', 
	                      	'youtube_id', 'file'];
					 
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
		return LoginService.checkIsLoggedIn();
	};

	if(!LoginService.redirectIfNotLoggedIn()){
		$scope.setVideos();
		$scope.setSelectLists();
	}
}]);
