var sessM = angular.module('SessionM', ['ngSanitize', 'SpireFactoryModule', 'WebserviceModule', 'ArtistsM', 'HomeLoginM']);

//Essentially the persistent data of the Session
sessM.service('SessionService', ['$q','SpireFactory', 'HelperFactory', '$interval', 'Webservice','ArtistsService', '$rootScope', 'HomeLoginService', 
                                 function($q, SpireFactory, HelperFactory, $interval, Webservice, ArtistsService, $rootScope, HomeLoginService){
  var SessionService = this;
  
  this.session     = {};
  this.countdown   = {};
  this.videos      = [];
  this.allSessions = null;

  /*Setting Functions*/	 
  //Sets the this.session, this.countdown, this.videos(via setVideos(...))
  this.setSessionCountdownVideos = function(sessionId){
    if(this.allSessions != null){
        this.session   = getSessionFromAllSessions(sessionId);
        SessionService.countdown = SpireFactory.getNewCountdown(SessionService.session.end_dt);
        SessionService.countdown.begin(.8);
        SessionService.setVideos(SessionService.session.id, 1);
    }
    else{
      SessionService.getSetAllSessions()
      .then(function(servResponse, status){
        if(servResponse.ok){
          SessionService.setSessionCountdownVideos(sessionId);
        }        
        else{

        }
      });
    }
  };

  this.getSetAllSessions = function(){
    var deferred = $q.defer();
    if(this.allSessions != null){
      deferred.resolve(HelperFactory.servResponse(true, SessionService.allSessions));
    }
    else{
      Webservice.getSessions(null, true)
      .success(function(data, status){
        SessionService.allSessions = SpireFactory.getSessionArray(data, true);
        deferred.resolve(HelperFactory.servResponse(true, SessionService.allSessions));
      })
      .error(function(data, status){
        Webservice.handleHttpError(data, status);
        deferred.resolve(HelperFactory.servResponse(false, "Error occured: " + angular.toJson(data)));
      });
    }
    return deferred.promise;
  };
  
  //Sets the this.videos
  this.setVideos = function(sessionId, activeFlag){
  	Webservice.getVideos()
  	.success(function(data, status){
  		SessionService.videos = SpireFactory.getVideoArray(data, sessionId, activeFlag);
  		$rootScope.$broadcast("setSessionCtrl", SessionService.session);
  	})
  	.error(function(data, status){
  		Webservice.handleHttpError(data, status);
  	});
  };
  
  this.broadcastActiveArtistPastEntries = function(artistId){
	  Webservice.getVideos()
	  .success(function(data, status){
		  $rootScope.$broadcast("setActiveArtisPastEntries", SpireFactory.getVideoArray(data, null, false, artistId));
	  })
	  .error(function(data, status){
		 Webservice.handleHttpError(data, status);
	  });
  };

  this.getWinningVideo = function(){
  	var deferred = $q.defer();
  	Webservice.getWinningVideoId(SessionService.session.id, HomeLoginService.getToken())
  	.success(function(data, status){
  		deferred.resolve(HelperFactory.servResponse(true, data));
  	})
  	.error(function(data, status){
  		deferred.resolve(HelperFactory.servResponse(false, 'Error occured: '+angular.toJson(data)));
  	});
  	return deferred.promise;
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

  this.addVote = function(vidId){
    var deferred = $q.defer();
    Webservice.addVote(vidId, SessionService.session.id, HomeLoginService.getToken())
    .success(function(data, status){
      deferred.resolve(HelperFactory.servResponse(true, "Vote Updated"));
    })
    .error(function(data, status){
      //Webservice.handleHttpError(data, status);
      deferred.resolve(HelperFactory.servResponse(false, data));
    });
    return deferred.promise; 
  };

  var getSessionFromAllSessions = function(sessionId){
    var session = null;
    angular.forEach(SessionService.allSessions, 
                            function(value, key){
      if(value.id == sessionId){
        session = value;
      }
    });
    return session;
  };
}]);
