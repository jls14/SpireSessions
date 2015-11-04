<!DOCTYPE html>
<html ng-app="spire">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php 
        $arr = explode('_', $_GET['fb_share']);
        $caption = [];
        preg_match("/(.*?)~(.*)/", $arr[1], $caption);
        $session = [];
        preg_match("/(.*?)~(.*)/", $arr[2], $session);

        $fb_share = [];
        $fb_share['caption']   = "Check Out SpireSessions!";
        $fb_share[$caption[1]] = $caption[2];
        $fb_share[$session[1]] = $session[2];

        $currentUrl = "http://www.spiresessions.com/home/?".$_SERVER['QUERY_STRING']."#/";
        echo   "<meta property=\"og:url\"          content=\"$currentUrl\"/>
                <meta property=\"og:type\"         content=\"website\"/>
                <meta property=\"og:title\"        content=\"SpireSessions\"/>
                <meta property=\"og:description\"  content=\"".$fb_share["caption"]."\"/>
                <meta property=\"og:image\"        content=\"http://www.spiresessions.com/common/images/spiral-2.png\"/>";
    ?>        

    <script>
        /*var searchStr = location.search;
        if(/fb\_share/.test(location.search)){
            var session = searchStr.replace('.*_session~');
            window.location = "http://spiresessions.com/home/#/session/"+session;
        }*/
    </script>

    <title>SpireSessions</title>
    
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="../common/bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" href="../common/css/spire-style.css" />
    
    <script src="../common/youtube.js"></script>
    <script src="../common/angular/angular.min.js"></script>
    <script src="../common/angular/angular-route.js"></script>
    <script src="../common/angular/angular-sanitize.js"></script>
    <script src="../common/angular/angular-animate.js"></script>
    <script src="//angular-ui.github.io/bootstrap/ui-bootstrap-tpls-0.13.4.js"></script>

    <script src="../common/FbModule.js"></script>
    <script src="../common/SpireFactories.js"></script>
    <script src="../common/Webservice.js"></script>
    <!--script src="../common/ng-file-upload/ng-file-upload-shim.js"></script>
    <script src="../common/ng-file-upload/ng-file-upload.js"></script-->
    <script type="text/javascript" src="https://angular-file-upload.appspot.com/js/ng-file-upload-shim.js"></script>
    <script type="text/javascript" src="https://angular-file-upload.appspot.com/js/ng-file-upload.js"></script>

    
    <script src="Artists/ArtistsCV.js"></script>
    <script src="Artists/ArtistsM.js"></script>
    <script src="ArtistsEdit/ArtistsEditM.js"></script>
    <script src="ArtistsEdit/ArtistsEditCV.js"></script>
    <script src="Session/SessionCV.js"></script>
    <script src="Session/SessionM.js"></script>
    <script src="Login/HomeLoginM.js"></script>
    <script src="Navbar/NavbarCV.js"></script>
    <script src="app.js"></script>
  </head>
  <body id="body" ng-controller="MainCtrl">
    <navbar-view></navbar-view>
    <!--  a href="#/">Sessions</a><a href="#artists">Artists</a-->
    <div id="spire-body" class="center-block">
      <div ng-view class="animate-view"></div>
    </div><!--app-body-->
    <div id="fb-root"></div>
  </body>
</html>
