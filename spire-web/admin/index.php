<!DOCTYPE html>
<html ng-app="spire">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>SpireAdmin</title>
    
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="../common/bootstrap/css/bootstrap.css" />
    
    <script src="../common/angular/angular.min.js"></script>
    <script src="../common/angular/angular-route.js"></script>
    <script src="../common/angular/angular-sanitize.js"></script>
    <script src="../common/angular/angular-animate.js"></script>
    <script src="../common/bootstrap/ui-bootstrap-tpls-0.12.1.min.js"></script>
    <script src="../common/SpireFactories.js"></script>
    <script src="../common/Webservice.js"></script>
    <script src="../common/sha512.js"></script>

    <!--script src="../common/ng-file-upload/ng-file-upload-shim.js"></script>
    <script src="../common/ng-file-upload/ng-file-upload.js"></script-->
    <script type="text/javascript" src="https://angular-file-upload.appspot.com/js/ng-file-upload-shim.js"></script>
    <script type="text/javascript" src="https://angular-file-upload.appspot.com/js/ng-file-upload.js"></script>


    <script src="Login/LoginCV.js"></script>
    <script src="Login/LoginM.js"></script>
    <script src="Navbar/NavbarCV.js"></script>
    <script src="Session/SessionAdminCV.js"></script>
    <script src="Artist/ArtistAdminCV.js"></script>
    <script src="Video/VideoAdminCV.js"></script>
    <script src="app.js"></script>
  </head>
  <body id="body" ng-controller="MainCtrl">
    <navbar-view></navbar-view>
    <!--  a href="#/">Sessions</a><a href="#artists">Artists</a-->
    <div id="spire-body" class="center-block">
      <div ng-view class="animate-view"></div>
    </div><!--app-body-->
  </body>
</html>