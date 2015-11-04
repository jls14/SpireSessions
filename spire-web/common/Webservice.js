var webserv = angular.module('WebserviceModule', ['ngFileUpload']);

webserv.factory('Webservice', ['$http', 'Upload', function($http, Upload){
	
	var apiUrl = "http://spiresessions.com/api/v1.0/";

	return {addVote:function(videoId, sessionId, token){
				return $http.post(apiUrl+'?vote', {video_id:videoId, session_id:sessionId}, new Config(token));
			},
			getVotes:function(sessionId, token){
				return $http.get(apiUrl+'?vote=&videovotes=&session_id='+sessionId, new Config(token));
			},
			getUsersVote:function(sessionId, token){
				return $http.get(apiUrl+'?vote=&uservote=&session_id='+sessionId, new Config(token));
			},
			getLoginToken:function(username, password){
				return $http.post(apiUrl + "?login=&auth=", {usernameOrEmail:username, password:password});
			},
			getLoginTokenFb:function(fbAuthResp, sessionId){
				return $http.post(apiUrl + "?login=&fbauth=", {fbAuthResp:fbAuthResp, sessionId:sessionId});
			},
			getUserFromToken:function(token){
				return $http.post(apiUrl + "?login=&check=", {token:token});
			},
			handleHttpError:function(data, status){
				alert(status + "-" + angular.toJson(data));
			},
			getVideos:function(){
				return $http.get(apiUrl + "?video");
			},
			createVideo:function(video, token){
				return $http.post(apiUrl + "?video", video, new Config(token));
			},
			updateVideo:function(video, token){
				return $http.put(apiUrl + "?video", video, new Config(token));
			},
			deleteVideo:function(id, token){
				return $http.delete(apiUrl + "?video", new Config(token));
			},
			getSessions:function(sessionId, active){
				return $http.get(apiUrl + '?session=' + sessionId + '&activeOnly=' + active);
			},
			createSession:function(session, token){
				return $http.post(apiUrl + "?session", session, new Config(token));
			},
			updateSession:function(session, token){
				return $http.put(apiUrl + "?session", session, new Config(token));
			},
			deleteSession:function(id, token){
				return $http.delete(apiUrl + "?session=" + id, new Config(token));
			},
			getArtists:function(active){
				return $http.get(apiUrl + '?artist&activeOnly='+active);
			},
			createArtist:function(artist, token){
				return $http.post(apiUrl + "?artist", artist, new Config(token));
			},
			updateArtist:function(artist, token){
				return $http.put(apiUrl + "?artist", artist, new Config(token));
			},
			deleteArtist:function(id, token){
				return $http.delete(apiUrl + "?artist=" + id, new Config(token));
			},
			getVideos:function(artistId){
				return $http(new HttpConfig(apiUrl + "", "GET", {video:"",artistId:artistId}));
			},
			createVideo:function(video, token){
				return $http.post(apiUrl + "?video", video, new Config(token));
			},
			updateVideo:function(video, token){
				return $http.put(apiUrl + "?video", video, new Config(token));
			},
			deleteVideo:function(id, token){
				return $http.delete(apiUrl + "?video=" + id, new Config(token));
			},
			signupArtist:function(newArtist, token){
				return $http.post(apiUrl + "?artistsignup", newArtist, new Config(token));
			},
			getWinningVideoId:function(session, token){
				return $http.get(apiUrl + "?winningVideoBySession="+session, new Config(token));
			},
			getUser:function(userId){
				return $http.get(apiUrl + "?user=" + userId, new Config(token));
			},
			getUserArtist:function(token){
				return $http.get(apiUrl + "?userartist", new Config(token));
			},
			createVideoArtist:function(video, file, token){
				var conf = new Config(token);
				return Upload.upload({url: apiUrl + '?videoartist',
						               data:{fields: video, videoToUpload: file},
						              headers: conf.headers});
			}
	};
	
	function Config(token){
		this.headers = {};
		this.headers.token = token; 
	}

	function HttpConfig(url, method, params, data, token){
		this.url     = url;
		this.method  = method;
		this.data    = data;
		this.params  = {};
		this.headers = {};
		this.headers.token = token;

		if(angular.isObject(params) && params != undefined){
			this.params = params;
		}
		this.params.noCache = new Date(); 
	}
}]);