var artistsM = angular.module('ArtistsM', ['WebserviceModule', 'SpireFactoryModule']);

artistsM.service("ArtistsService", ['Webservice','SpireFactory','$rootScope', 
                                    	function(Webservice, SpireFactory, $rootScope){
	var ArtistsService = this;
	
	this.artistsArray = [];
	this.artistsHash  = {};
	
	/*Set Artists stuff*/
	this.setArtistsArrayAndHash = function(){
		Webservice.getArtists(true)
		.success(function(data, status){
			ArtistsService.artistsArray = SpireFactory.getArtistArray(data);
			ArtistsService.artistsHash  = SpireFactory.getArtistHash(data);
			$rootScope.$broadcast("setArtists");
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	this.setArtistsArrayAndHash();
}]);