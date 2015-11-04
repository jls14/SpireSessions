var spireFact = angular.module('SpireFactoryModule', []);

//Creates all objects needed in SessionService
spireFact.factory('SpireFactory', ['$interval','$log','$sce', 'HelperFactory', '$rootScope', 
                                   	function($interval, $log, $sce, HelperFactory, $rootScope){
	var youTubeApiReady = false;
	
	window.onYouTubeIframeAPIReady = function(){
		youTubeApiReady = true;
	};
	
	return {
		getVideoArray:function(array, sessionId, activeFlag, artistId){
			var videoArray = []
			for(var i in array){
				var vid = array[i];
				var video = new Video(vid.id, vid.artist_id, vid.session_id, vid.iframe_url, 
						vid.is_active, 0, vid.video_status_nbr, vid.upload_status_nbr, 
						vid.youtube_id, vid.name, vid.file);
				
				var sesFilter = true;
				var actFilter = true;
				var artFilter = true;
				
				if(HelperFactory.defaultVal(sessionId, 0) > 0){
					sesFilter = (video.session_id === sessionId);
				}
				if(!HelperFactory.isNullOrUndefined(activeFlag)){
					actFilter = (video.is_active == activeFlag);
				}
				if(angular.isNumber(artistId)){
					artFilter = (video.artist_id === artistId);
				}
				
				if(sesFilter && actFilter && artFilter){
					videoArray.push(video);
				}
			}
			return videoArray;
		},
		getArtistArray:function(array){
			var artistArray = []
			for(var i in array){
				var art = array[i];
				artistArray.push(new Artist(art.id, art.name, art.url, art.facebook_id,
											art.twitter_id, art.img_file_path, art.bio, art.area, 
											art.google_id, art.tumblr_id, art.instagram_id,
											art.img_url, art.is_active));
			}
			return artistArray;
		},
		getArtistHash:function(array){
			var artistHash = {};
			for(var i in array){
				var art = array[i];
				artistHash[art.id] = new Artist(art.id, art.name, art.url, art.facebook_id,
												art.twitter_id, art.img_file_path, art.bio, art.area, 
												art.google_id, art.tumblr_id, art.instagram_id,
												art.img_url, art.is_active);
			}
			return artistHash;
		},
		/*if activeFlagOn returns array of 1
		 *  (assuming only one session is active)*/
		getSessionArray:function(array, activeFlag){
			var sessionArray = [];
			for(var i in array){
				var ses = array[i];
				var session = new Session(ses.id, ses.start_ts, 
											ses.end_ts, ses.is_active, ses.area, 
											ses.name, ses.description, ses.winner_video_id, ses.img_url);
				
				var actFilter = true;
				
				if(activeFlag){
					actFilter = session.is_active;
				}
				if(actFilter){
					sessionArray.push(session);
				}
			}
			return sessionArray;
		},
	    getNewVideo:function(id, artistId, sessionId, link, isActive, votes, videoStatus, uploadStatus, yt_id, name, file){
	    	return new Video(id, artistId, sessionId, link, isActive, votes, videoStatus, uploadStatus, yt_id, name, file);
	    },
	    getBlankVideo:function(){
	    	return new Video();
	    },
	    getNewArtist:function(id, name, web, fb, twt, pic, bio, area, google, tumblr, instagram, img_url, is_active){
	    	return new Artist(id, name, web, fb, twt, pic, bio, area, google, tumblr, instagram, img_url, is_active);
	    },
	    getNewSession:function(id, startDt, endDt, active, area, name, description, winVidId, imgUrl){
	    	return new Session(id, startDt, endDt, active, area, name, description, winVidId, imgUrl);
	    },
	    getNewCountdown:function(dline){
	    	return new Countdown(dline);
	    },
	    getNavLink:function(href, title, isActive){
	    	return new NavLink(href, title, isActive);
	    },
	    getTabLink:function(href, title, isActive, templateUrl, ctrl){
	    	return new TabLink(href, title, isActive, templateUrl, ctrl);
	    },
	    getField:function(label, tag, type, optionObjects){
	    	return new Field(label, tag, type, optionObjects);
	    }
  };
  
  function Field(label, tag, type, options){
	  this.label   = label;
	  this.tag     = tag;
	  this.type    = type;
	  this.options = options;
  }
  
  function NavLink(href, title, isActive){
	 this.href  = $sce.trustAsResourceUrl(href);
	 this.title = title;
	 this.isActive = isActive;
  }

  function TabLink(href, title, isActive, templateUrl, controller){
	 this.href        = $sce.trustAsResourceUrl(href);
	 this.title       = title;
	 this.isActive    = isActive;
	 this.templateUrl = templateUrl;
	 this.controller  = controller;
  }
  
  function Video(id, /*req*/artistId, /*req*/sessionId, /*req*/link, 
		  			isActive, votes, videoStatus, uploadStatus, youtube_id, name, file){
    var vid = this;
    
    this.id 		       = HelperFactory.requiredVal(id, "a video id is required");
    this.name  			   = HelperFactory.requiredVal(name, "a name is required for a video");
	this.artist_id         = HelperFactory.requiredVal(artistId, "artist_id is required for Video");
    this.session_id        = HelperFactory.requiredVal(sessionId, "session_id is required for Video");
    this.iframe_url	       = HelperFactory.requiredVal($sce.trustAsResourceUrl(link), "videoLink is required for Video");
    this.is_active	       = HelperFactory.requiredVal(HelperFactory.isTrue(isActive), "is_active is required for Video");
    this.votes	           = HelperFactory.defaultVal(votes, 0);
    this.video_status_nbr  = HelperFactory.requiredVal(videoStatus, "video_status is required for Video");
    this.upload_status_nbr = HelperFactory.requiredVal(uploadStatus, "upload_status is required for Video");
    this.youtube_id 	   = HelperFactory.defaultVal(youtube_id, null);
    this.ytPlayer 		   = new YouTubePlayer(this.youtube_id);
    this.file 			   = file;
    
    this.prepareForUpload = function(){
    	this.iframe_url = this.iframe_url.toString();
    	this.file = null;
    };

    this.getFbShareSrc = function(artistName){
    	var caption  = "?fb_share=_caption~Vote for "+this.name+"! It's the new video by "+artistName+"_session~"+this.session_id;
    	var shareUrl = "http://www.spiresessions.com/home/"+caption;
    	shareUrl = encodeURI(shareUrl);
    	console.log(shareUrl);
    	
    	return $sce.trustAsResourceUrl("//www.facebook.com/plugins/share_button.php?href=" + shareUrl +
    									"&show_faces=false&layout=button&width=50");
    };
  }
  
  function Artist(/*req*/id, /*req*/name, web, fb, twt, pic, bio, area, google, tumblr, instagram, img_url, is_active){
    this.id   			= HelperFactory.requiredVal(id, "id is required for Artist");
	this.name 			= HelperFactory.requiredVal(name, "Name is reqired for Artist");
    this.url 			= $sce.trustAsResourceUrl(HelperFactory.defaultVal(web, ""));
    this.facebook_id   	= HelperFactory.defaultVal(fb, "");
    this.twitter_id  	= HelperFactory.defaultVal(twt, "");
    this.google_id  	= HelperFactory.defaultVal(google, "");
    this.tumblr_id  	= HelperFactory.defaultVal(tumblr, "");
    this.instagram_id  	= HelperFactory.defaultVal(instagram, "");
    this.img_file_path  = HelperFactory.defaultVal(pic, "");
    this.bio  			= HelperFactory.defaultVal(bio, "");
    this.area 			= HelperFactory.defaultVal(area, "");
    this.img_url 		= $sce.trustAsResourceUrl(HelperFactory.defaultVal(img_url, "http://www.spiresessions.com/common/images/artist_default.jpg"));
    this.is_active      = HelperFactory.requiredVal(HelperFactory.isTrue(is_active), "is_active is required for Session");
    
    this.getTwtFollowSrc = function(){
    	return $sce.trustAsResourceUrl("//platform.twitter.com/widgets/" +
    									"follow_button.html?screen_name=" + this.twitter_id + 
    									"&show_count=false&show_screen_name=false");
    };
    this.getFbLikeSrc = function(){
    	return $sce.trustAsResourceUrl("//www.facebook.com/plugins/like.php?href=https://www.facebook.com/"
    									+ this.facebook_id + 
    									"&show_faces=false&layout=button&width=50");
    };
    this.getFbShareSrc = function(){
    	
    	return $sce.trustAsResourceUrl("//www.facebook.com/plugins/share_button.php?href=" + 
    									"http%3A%2F%2Fspiresessions.com%2Fhome%2F%3Fcaption%3D"+this.name+"%23%2F"+
    									"&show_faces=false&layout=button&width=50");
    };
    
    this.prepareForUpload = function(){
    	this.img_url = this.img_url.toString();
    	this.url 	 = this.url.toString();
    };
  }
  
  function YouTubePlayer(id){
	  if(youTubeApiReady){
		  return new YT.Player('yt-' + id, {height:'160', width:'90', videoId: id,
								events: {
								          onReady: function(){
								        	  	$rootScope.$broadcast("ytReady", vid.id)},
								          },
								          onStateChange: function(){
								        	  $rootScope.$broadcast("ytStChg", vid.id)
								          }
	  							});
	  }
	  else{
		  return null;
	  }
  }
  
  function Session(/*req*/id, /*req*/startDt, /*req*/endDt, /*req*/active, area, name, description, winVidId, imgUrl){
	  this.id				= HelperFactory.requiredVal(id, "id is required for Session");
	  this.name				= HelperFactory.defaultVal(name, "");
	  this.description		= HelperFactory.defaultVal(description, "");
	  this.start_dt 		= HelperFactory.requiredVal(new Date(startDt), "start_ts is required for Session");
	  this.end_dt			= HelperFactory.requiredVal(new Date(endDt), "end_ts is required for Session");
	  this.is_active	  	= HelperFactory.requiredVal(HelperFactory.isTrue(active), "is_active is required for Session");
	  this.area		        = HelperFactory.defaultVal(area, "");
	  this.winner_video_id  = HelperFactory.defaultVal(winVidId, 0);
	  this.img_url          = HelperFactory.defaultVal(imgUrl, "http://www.spiresessions.com/common/images/session_default.jpg");
	  
	  this.start_ts = null;
	  this.end_ts   = null;
	  
	  this.setUnixTs = function(){
		  this.start_ts = HelperFactory.getUTCDateString(this.start_dt);
		  this.end_ts   = HelperFactory.getUTCDateString(this.end_dt);
	  };
 
	  this.setUnixTs();
  }
  
  function Countdown(dline){
    var obj 	 = this;
    var deadline = dline;
    var interval = null;

    this.days  = new CountdownType("DAYS",  0);
    this.hours = new CountdownType("HOURS", 0);
    this.mins  = new CountdownType("MINS",  0);
    
    this.colon = false;

    this.toggleColon = function(){
    	this.colon = !this.colon;
    };
    
    this.keysOrder = ["days", "hours", "mins"];
    
    if(getTimeLeftMs <= 0){  
      return this;
    }

    this.setCountdown = function(){
    	var timeLeftMs  = getTimeLeftMs();
      	var timeLeftObj = getTimeLeftObj(timeLeftMs);
      
      	this.days.setTime(timeLeftObj.days);
      	this.hours.setTime(timeLeftObj.hours);
      	this.mins.setTime(timeLeftObj.mins);
      	this.toggleColon();
      	if(timeLeftMs <= 0){  
    	  $interval.cancel(interval);
    	  $rootScope.$broadcast("Countdown.Ended");
      	}
    };
    
    this.begin = function(delay){
    	var delayInSecs = delay;
      	interval = $interval(function(){obj.setCountdown()}, delayInSecs * 1000);
    };
    
    var getTimeLeftMs = function(){
    	var now = new Date();
      	var ms  = deadline - now;
      	return ms;
    };
    
    var getTimeLeftObj = function(milliSecs){
      
      var timeLeftObj = {days: 0, 
                        hours: 0, 
                         mins: 0};
      
      if(milliSecs > 0){
      	  var days = milliSecs/(1000 * 60 * 60 * 24);
	      timeLeftObj.days  = Math.floor(days);
	      var hours = (days - timeLeftObj.days) * 24;
	      timeLeftObj.hours = Math.floor(hours);
	      var mins  = (hours - timeLeftObj.hours) * 60;
	      timeLeftObj.mins  = Math.floor(mins);
      }

      return timeLeftObj;
    };
    
    //helperClass
    function CountdownType(lbl, left){
      this.label = lbl;
      this.time  = left;
      
      this.setTime = function(timeLeft){
        if(timeLeft < 0){
          this.time = 0
        }
        else{
          this.time = timeLeft;
        }
      };
    }
  }
}]);

spireFact.factory('HelperFactory', ['$log', '$sce', function($log, $sce){
	var isNullOrUndefined = function(val){
		return angular.isUndefined(val) || val === null;
	}
	
	function MyDate(date){
		this.year    = date.getFullYear();
		this.month	 = date.getMonth();
		this.hours   = date.getHours();
		this.minutes = date.getMinutes();
		this.seconds = date.getSeconds();
	}
	
	return {
			defaultVal:function(val, defVal){
				if(!isNullOrUndefined(val)){
					return val;
				}
				return defVal;
			},
			isNullOrUndefined:function(val){
				return isNullOrUndefined(val);
			},
			requiredVal:function(val, throwMsg){
				if(isNullOrUndefined(val)){
					$log.error(throwMsg);
					return null;
				}
				else{
					return val;
				}
			},
			getDateFromString:function(str){
				return new Date(str);
			},
			getUTCDateString:function(date){
				if(isNullOrUndefined(date) || !angular.isObject(date)){
					$log.error(throwMsg);
					return null;
				}
				else{
					return date.getFullYear()+"-"+(date.getMonth()+1)+"-"+date.getDate();
				}
			},
			sanitizeUrl:function(url){
				return $sce.trustAsResourceUrl(url);
			},
			isTrue:function(jsonBool){
				var string = jsonBool + "";
				string = string.toUpperCase();
				return string == 'T' || string == '1' || string == 'TRUE';
			},
			servResponse:function(ok, data){
				return {ok:ok, data:data};
			}
	};
}])
.directive('inputFieldsPanel', function(){
	return {
		restrict: 'E',
		template:'<div>'
				+'	<div class="input-group" ng-repeat="field in inputFields">'
				+'		<span class="input-group-addon">{{::field.label}}</span>'
				+'		<input ng-if="field.isInputTag() && field.isCheckboxType()" class="input-field" type="{{::field.type}}" ng-model="field.value"></input>'
				+'		<input ng-if="field.isInputTag() && !field.isCheckboxType()" class="input-field form-control" type="{{::field.type}}" ng-model="field.value" placeholder="{{::field.placeholder}}"></input>'
				+'		<select ng-if="field.isSelectTag()" class="input-field form-control" ng-model="field.value" ng-options="opt.label for opt in field.options"></select>'
			    +'	</div>'
				+'</div>'				   
	};
});