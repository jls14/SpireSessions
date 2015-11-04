var sessionAdmin = angular.module('SessionAdminCV', ['WebserviceModule', 'SpireFactoryModule']);

sessionAdmin.controller('SessionAdminCtrl', ['$scope', '$rootScope','Webservice', 'SpireFactory', 'HelperFactory', 'LoginService',
                                             	function($scope, $rootScope, Webservice, SpireFactory, HelperFactory, LoginService){
	
	$scope.opts          = {};
	$scope.sessions      = [];
	$scope.createSession = SpireFactory.getNewSession();
	$scope.sessionToEdit = $scope.createSession;
	$scope.isListLoading = false;
	
	$scope.setSessions = function(){
		$scope.isListLoading = true;
		Webservice.getSessions()
		.success(function(data, status){
			$scope.createSession = SpireFactory.getNewSession();
			$scope.isListLoading = false;
			$scope.sessions = SpireFactory.getSessionArray(data);
		})
		.error(function(data, status){
			$scope.isListLoading = false;
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.isNewSession = function(){
		return HelperFactory.isNullOrUndefined($scope.sessionToEdit.id);
	};
	
	$scope.isActiveClass = function(id){
		if(id == $scope.sessionToEdit.id)
			return "admin-edit-active";
		return "";
	};
	
	$scope.updateSession = function(){
		$scope.sessionToEdit.setUnixTs();
		var promise;
		if($scope.isNewSession()){
			promise = Webservice.createSession($scope.sessionToEdit, LoginService.getToken());
		}
		else{
			promise = Webservice.updateSession($scope.sessionToEdit, LoginService.getToken());
		}
		
		promise
		.success(function(data, status){
			alert(data);
			$scope.setSessions();
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.deleteSession = function(){
		Webservice.deleteSession($scope.sessionToEdit.id, LoginService.getToken())
		.success(function(data, status){
			alert(data);
			$scope.setSessions();
			$scope.setSessionToEdit($scope.createSession);
		})
		.error(function(data, status){
			Webservice.handleHttpError(data, status);
		});
	};
	
	$scope.fields = {         id: SpireFactory.getField("Id", "p"), 
					        name: SpireFactory.getField("Name", "input", "text"), 
					 description: SpireFactory.getField("Description", "input","text"),
					   is_active: SpireFactory.getField("Active", "input", "checkbox"),
					        area: SpireFactory.getField("Area", "input", "text"),
					    start_dt: SpireFactory.getField("Start Date", "input", "date"),
					      end_dt: SpireFactory.getField("End Date", "input", "date"),
				  		 img_url: SpireFactory.getField("Session Img", "input", "url")};
	$scope.fieldsOrder = ['id',  'is_active', 'name', 'description',
	                      	'area', 'start_dt', 'end_dt', 'img_url'];
					 
	$scope.setSessionToEdit = function(sess){
		console.log(angular.toJson(sess));
		$scope.sessionToEdit = sess;
	};
	
	$scope.showView = function(){
		return LoginService.checkIsLoggedIn();
	};

	if(!LoginService.redirectIfNotLoggedIn()){
		$scope.setSessions();
	}
}]);
