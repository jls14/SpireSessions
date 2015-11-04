var artistsCV = angular.module('ArtistsCV', [ 'ArtistsM' ]);

artistsCV.directive('artistsView', function() {
	return {
		restrict : 'E',
		templateUrl : 'Artists/artistsView.html',
		controller : 'ArtistsCtrl'
	};
});
// handlesArtistsView
artistsCV.controller('ArtistsCtrl', [ '$scope', '$rootScope', 'ArtistsService', function($scope, $rootScope, ArtistsService) {
	$scope.artists = ArtistsService.artistsArray;

	$rootScope.$on("setArtists", function(event) {
		$scope.artists = ArtistsService.artistsArray;
	});

}]);
