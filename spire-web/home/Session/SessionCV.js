var sessionCV = angular.module('SessionCV', [ 'SessionM', 
											  'ArtistsM', 
											  'SpireFactoryModule', 
											  'FbModule', 
											  'WebserviceModule', 
											  'HomeLoginM',
											  'ngRoute']);

//creates the custom directive to display the countdown
sessionCV.directive('countdownView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Session/countdownView.html'
	};
});

sessionCV.directive('activeArtistView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Session/activeArtistView.html'
	};
});

sessionCV.directive('activeArtistMobileView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Session/activeArtistMobileView.html'
	};
});

sessionCV.directive('videoView', function() {
	return {
		restrict : 'E',
		scope : {
			video : '=video',
			artist: '=artist',
			index : '=index'
		},
		templateUrl : 'Session/videoView.html',
		controller : function($scope, $rootScope, FbService) {
			$scope.broadcastArtist = function() {
				$rootScope.$broadcast("setActiveArtist", $scope.artist);
			};
			$scope.voteForVideo = function(){
				if(FbService.checkIsConnected()){
					$rootScope.$broadcast("voteForVideo", {index:$scope.index, id:$scope.video.id});
				}
				else{
					$rootScope.$broadcast("onMustLogin");
				}
			};
			$scope.getStarGlyphicon = function(){
				var starGlyph = "glyphicon glyphicon-star";
				
				try{
					if($scope.video.vote){
						return starGlyph;
					}
				} catch(e){
					return starGlyph + "-empty";
				}
				return starGlyph + "-empty";
			};
			window.onStateChange = function(event){
				alert("playerStateChange");
			}
		}
	};
});

sessionCV.directive('sessionsView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Session/sessionView.html',
		controller : 'SessionCtrl'
	};
});

//Handles the interaction between Session View and SessionService
sessionCV.controller('SessionCtrl', [ '$log', '$scope', '$rootScope', 'SessionService', 'ArtistsService', 'HelperFactory', 'Webservice', 'HomeLoginService', 'FbService', '$routeParams',
		function($log, $scope, $rootScope, SessionService, ArtistsService, HelperFactory, Webservice, HomeLoginService, FbService, $routeParams) {
			$log.info(angular.toJson($routeParams));
			
			$scope.artistsHash = ArtistsService.artistsHash;
			$scope.countdown;
			$scope.countdownToNext;
			$scope.indexPairs;
			$scope.videos;

			$scope.videoIframes;
			$scope.activeArtist;
			$scope.currentVoteIndex;

			$scope.winningVideoIndex = null;
			//activeArtistTrg - is true if clicked anything that would keep
			//the activeArtist Modal showing
			var activeArtistTrg = false;
			
			/*functions used in template*/
			$scope.setActiveArtist = function(artist){
				$scope.activeArtist = artist;
				SessionService.broadcastActiveArtistPastEntries(artist.id);
				activeArtistTrg = true;
			};
			$scope.clearActiveArtist = function(){
				if(!activeArtistTrg){
					$scope.activeArtist = null;
				}
				activeArtistTrg = false;
			};
			$scope.activeArtistPanelClick = function(){
				activeArtistTrg = true;
			};
			$scope.showActiveArtist = function(){
				return !HelperFactory.isNullOrUndefined($scope.activeArtist);
			};
			$scope.isCurrentVoteIndex = function(i){
				return $scope.currentVoteIndex === i;
			};
			
			/*event handlers*/
			$rootScope.$on("setSessionCtrl", function(event) {
				setSessionCtrl();
			});
			$rootScope.$on("setArtists", function(event) {
				$scope.artistsHash = ArtistsService.artistsHash;
			});
			$rootScope.$on("setActiveArtist", function(event, data) {
				$scope.setActiveArtist(data);
			});
			$rootScope.$on("setActiveArtisPastEntries", function(event, data){
				$scope.activeArtist.pastEntries = data;
			});
			$rootScope.$on("voteForVideo", function(event, value){
				//turn other video.vote off
				try{
					$scope.videos[$scope.currentVoteIndex].vote = false;
				} catch(e){}
				//addvote in db
				addVote(value);	
			});
			$rootScope.$on("HomeLoginService.setToken", function(event, value){
				if(HelperFactory.isNullOrUndefined(HomeLoginService.getToken())){
					if($scope.currentVoteIndex > 0){
						$scope.videos[$scope.currentVoteIndex].vote = false;
						$scope.currentVoteIndex = null;
					}
				}
				else{
					setSavedUserVote();
				}
			});
			$rootScope.$on("Countdown.Ended", function(event, value){
				$log.info("Countdown.Ended");
				$scope.sessionOver = true;
				SessionService.getWinningVideo()
				.then(function(servResponse, status){
					if(servResponse.ok){
						//setnewcountdown
						//setwinningvideoview
						setWinningVideoIndexById(servResponse.data);
					}
					else{

					}
				});
			});


			var addVote = function(value){
				SessionService.addVote(value.id)
				.then(function(servResponse, status){
					if(servResponse.ok){
						//set currentVoteIndex and video.vote on
						$scope.currentVoteIndex         = value.index;
						$scope.videos[value.index].vote = true;
						setVotesToVideos();
					}
					else{

					}
				});	
			};
			
			var setVotesToVideos = function(){
				 Webservice.getVotes(SessionService.session.id, HomeLoginService.getToken())
				 .success(function(data, status){
				  		//set video vote counts
				  		for(var index in $scope.videos){
				  			$scope.videos[index].votes = HelperFactory.defaultVal(data[$scope.videos[index].id], 0);
				  		}
				 })
				 .error(function(data,status){
				  		Webservice.handleHttpError(data,status);
				 });	 
			};

			var setSavedUserVote = function(){
				 Webservice.getUsersVote(SessionService.session.id, HomeLoginService.getToken())
				 .success(function(data, status){
			  		//set video vote counts
			  		if(!HelperFactory.isNullOrUndefined(data)){
			  			for(var i in $scope.videos){
			  				if($scope.videos[i].id == data){
			  					$scope.videos[i].vote   = true;
			  					$scope.currentVoteIndex = i;
			  				}
			  			}
			  		}
				  })
				  .error(function(data,status){
				  		Webservice.handleHttpError(data,status);
				  });	 
			};

			var setWinningVideoIndexById = function(id){
				for(var i in $scope.videos){
					if($scope.videos[i].id == id){
						$scope.winningVideoIndex = i;
					}
				}
			};
			
			var setSessionCtrl = function() {
				$scope.countdown  = SessionService.countdown;
				$scope.videos     = SessionService.videos;
				$scope.indexPairs = getIndexPairsArray(SessionService.videos.length);
				setVotesToVideos();
			};

			var getIndexPairsArray = function(arrayLength) {
				var newArray = [];
				for (var i = 0, j = 1; j < arrayLength; i+=2, j+=2) {
					newArray.push([i,j]);
				}
				if (arrayLength % 2 != 0) {
					newArray.push([arrayLength - 1, false]);
				}
				return newArray;
			};
			
			SessionService.setSessionCountdownVideos($routeParams.sessionId);
}]);

sessionCV.controller('SessionChoiceCtrl', ['$scope', 'SessionService',
	function($scope, SessionService){
		$scope.allSessions = [];

		SessionService.getSetAllSessions()
		.then(function(servResponse, status){
			if(servResponse.ok){
				$scope.allSessions = servResponse.data;
			}
		});

	}
]);

sessionCV.directive('videoHover', [ 'SessionService', '$rootScope', '$log',
	function(SessionService, $rootScope, $log) {
		return {
			restrict : 'A',
			scope : {
				it : '@'
			},
			link : function(scope, element) {
				element.on('mouseenter', function() {
					element.removeClass('video-hovering-false');
					element.addClass('video-hovering');
				});
				element.on('mouseleave', function() {
					element.removeClass('video-hovering');
					element.addClass('video-hovering-false');
				});
			}
		};
	}
]);