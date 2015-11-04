//ArtistsAdminM.js
var artAdmMApp = angular.module('ArtistsEditM', ['SpireFactoryModule', 'WebserviceModule', 'HomeLoginM', 'ngFileUpload']);

artAdmMApp.service('ArtistsEditService', ['$q', 'Webservice', 'HomeLoginService', '$rootScope', 'HelperFactory', 'SpireFactory', '$rootScope', 'Upload',
											function($q, Webservice, HomeLoginService, $rootScope, HelperFactory, SpireFactory, $rootScope, Upload){
	var ArtistsAdmin = this;
	var artists      = null;
	var token        = null;
	var videos       = {};

	this.getArtistFromUser = function(){
		var deferred = $q.defer();
		if(HelperFactory.isNullOrUndefined(artists) 
				|| token != HomeLoginService.getToken()){
			Webservice.getUserArtist(HomeLoginService.getToken())
			.success(function(data, status){
				setArtists(data);
				token = HomeLoginService.getToken();
				deferred.resolve(HelperFactory.servResponse(true, artists));
			})
			.error(function(data, status){
				deferred.resolve(HelperFactory.servResponse(false, data));
			});
		}
		else{
			deferred.resolve(HelperFactory.servResponse(true, artists));
		}
		return deferred.promise;
	};

	this.updateArtist = function(artist){
		var deferred = $q.defer();
		artist.prepareForUpload();
		Webservice.updateArtist(artist, HomeLoginService.getToken())
		.success(function(data, status){
			deferred.resolve(HelperFactory.servResponse(true, data));
		})
		.error(function(data, status){
			deferred.resolve(HelperFactory.servResponse(false, data));
		});
		return deferred.promise;
	};

	this.getArtistFromId = function(artistId){
		var deferred = $q.defer();
		var resp    = null;
		this.getArtistFromUser()
		.then(function(response, status){
			if(response.ok){
				for(var i in response.data){
					if(response.data[i].id == artistId){
						resp = HelperFactory.servResponse(true, response.data[i]);
					}
				}
				if(HelperFactory.isNullOrUndefined(resp)){
					resp = HelperFactory.servResponse(false, "Artist Not Found");
				}
			}
			if(HelperFactory.isNullOrUndefined(resp)){
				resp = HelperFactory.servResponse(false, "Service Error");
			} 
			deferred.resolve(resp);
		});
		return deferred.promise;
	};

	this.getVideosFromArtistId = function(artistId){
		var deferred = $q.defer();
		var resp     = null;
		Webservice.getVideos(artistId)
		.success(function(data,status){
			videos = SpireFactory.getVideoArray(data);
			deferred.resolve(HelperFactory.servResponse(true, videos));
		})
		.error(function(data, status){
			deferred.resolve(HelperFactory.servResponse(false, data));
		});
		return deferred.promise;
	};

	this.createVideo = function(video, file){
		var deferred = $q.defer();
		var resp     = null;
		Webservice.createVideoArtist(video, file, HomeLoginService.getToken())
		.success(function(status, data){
			deferred.resolve(HelperFactory.servResponse(true, "Video Updated"));
		})
		.error(function(status, data){
			deferred.resolve(HelperFactory.servResponse(false, "Error Updating Video"));
		});
		return deferred.promise;
    };

    this.updateVideo = function(video){
    	var deferred = $q.defer();
    	deferred.resolve(HelperFactory.servResponse(true, "Update Not created yet"));
    	return deferred.promise;
    };

	var setArtists = function(arts){
		artists = SpireFactory.getArtistArray(arts);
		$rootScope.$broadcast('ArtistsAdminService.setArtists');
	};
}]);