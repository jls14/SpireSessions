<?php
//api/index.php
define('FACEBOOK_SDK_V4_SRC_DIR', '/home1/enderrac/SpirePHP/facebook-php-sdk-v4-4.0-dev/src/Facebook/');
require '/home1/enderrac/SpirePHP/facebook-php-sdk-v4-4.0-dev/autoload.php';
require('/home1/enderrac/SpirePHP/JsonResponse.php');
require('/home1/enderrac/SpirePHP/SpirePHP.php');
require('/home1/enderrac/SpirePHP/User.php');

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;

FacebookSession::setDefaultApplication('879340512104772', '44e7ebc4f1bff480297ce6878f02ff40');

$logger = new FileWriter('spire_api_log', 'a');

$request_data  = json_decode(file_get_contents("php://input"));

$logger->writeLog("\n#####NEW REQUEST#####");
$logger->writeLog("Request Type: " . $_SERVER['REQUEST_METHOD']);
$logger->writeLog("_GET   = "   . json_encode($_GET));
$logger->writeLog("_FILES = " . json_encode($_FILES));
$logger->writeLog("_POST  = " . json_encode($_POST));
$logger->writeLog("request_data = " . json_encode($request_data));
$headers = apache_request_headers();
foreach ($headers as $header => $value) {
    $logger->writeLog("$header: $value");
}

$TOKEN = $headers['Token'];
$logger->writeLog("\$TOKEN: $TOKEN");

$TOKEN_DATA    = null;
$tokenDataHash = LoginDao::getTokenData($TOKEN);
if($tokenDataHash['ok']){
	$TOKEN_DATA = $tokenDataHash['result'];
	
	$_SESSION['user']      = $TOKEN_DATA['user_id'];
	$_SESSION['user_type'] = $TOKEN_DATA['user_type_nbr'];
	
	$logger->writeLog("isAdmin:".User::isAdmin($_SESSION['user_type']));
	$logger->writeLog("User = ".$_SESSION['user']);
	$logger->writeLog("User Type = ".$_SESSION['user_type']);
}

if($_SERVER['REQUEST_METHOD'] === "OPTIONS")
{
	JsonResponse::sendResponse(204, "");
}
else
{
	if(isset($_GET['login']) 
			&& $_SERVER['REQUEST_METHOD'] === "POST")
	{
		if(isset($_GET['auth']))
		{
			$fnHash = LoginDAO::getAndSaveToken($request_data->usernameOrEmail, 
												$request_data->password);
			if($fnHash['ok'])
			{
				JsonResponse::sendResponse(200, $fnHash['result']);
			}
			else
			{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif(isset($_GET['fbauth'])){
			$logger->writeLog("User ID = ".$request_data->fbAuthResp->userID);
			$userFnHash = UserDAO::getUserId("FACEBOOK_ID", $request_data->fbAuthResp->userID);
			$user_id = 0;
			if($userFnHash['ok'])
			{
				if($userFnHash['result'] > 0)
				{
					$user_id = $userFnHash['result'];
				}
				else
				{
					$session = new FacebookSession($request_data->fbAuthResp->accessToken);

					try {
					  $session->validate();
					} catch (FacebookRequestException $ex) {
					  // Session not valid, Graph API returned an exception with the reason.
					  echo $ex->getMessage();
					} catch (\Exception $ex) {
					  // Graph API returned info, but it may mismatch the current app or have expired.
					  echo $ex->getMessage();
					}
				
					$fb_profile = (new FacebookRequest($session, 'GET', '/'.$request_data->fbAuthResp->userID
    								 ))->execute()->getGraphObject(GraphUser::className());
					$fb_pic = (new FacebookRequest($session, 'GET', '/'.$request_data->fbAuthResp->userID.'/picture'
    								 ))->execute()->getGraphObject();
					
					$insertHash = UserDAO::insertUser(/*string*/$fb_profile->getProperty('email'),
													/*string*/'',
													/*string*/$fb_profile->getProperty('first_name'),
													/*string*/$fb_profile->getProperty('last_name'),
													/*string*/'',
													/*string*/$fb_profile->getProperty('id'),
													/*string*/'',
													/*string*/'',
													/*string*/'',
													/*string*/'',
													/*string*/$fb_pic->data[0]->url,
													/*string*/'');
					if($insertHash['ok'])
					{
						$userFnHash2 = UserDAO::getUserId("FACEBOOK_ID", $request_data->fbAuthResp->userID);
						if($userFnHash2['ok'])
						{
							$user_id = $userFnHash2['result'];
						}
					}
				}

				if($user_id > 0)
				{
					$TOKENHash = LoginDAO::getAndSaveTokenFB($user_id, $request_data->sessionId);
					if($TOKENHash['ok'])
					{
						$userHash = UserDAO::getUsers($user_id);
						if($userHash['ok']){
							$resultHash = [];
							$resultHash['token'] = $TOKENHash['result'];
							$resultHash['user']  = $userHash['result'][0];
							JsonResponse::sendResponse(200, $resultHash);
						}
						else{
							JsonResponse::sendResponse(400, $TOKENHash['reason']);
						}
					}
					else{
						JsonResponse::sendResponse(400, $TOKENHash['reason']);
					}
				}
				else
				{
					JsonResponse::sendResponse(400, "Could not find or create a user id");
				}
			}
			else
			{
				JsonResponse::sendResponse(400, $userFnHash['reason']);
			}
		}
		elseif(isset($_GET['check']))
		{
			$fnHash = LoginDAO::getUserFromToken($request_data->token);

			if($fnHash['ok'])
			{
				JsonResponse::sendResponse(200, $fnHash['result']);
			}
			else
			{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		else
		{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['artist']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET")
		{
			$artist_id   = $_GET['artist'];
			$active_only = $_GET['activeOnly'];
			$fnHash      = ArtistDAO::getArtists($artist_id, $active_only);

			$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
			
			if($fnHash['ok'])
			{
				JsonResponse::sendResponse(200, $fnHash['result']);
			}
			else
			{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "POST" 
					&& User::isAdmin($_SESSION['user_type'])) 
		{	
			$logger->writeLog("Inserting: " . $request_data->name);
			$fnHash = ArtistDAO::insertArtist($request_data->name,
											$request_data->bio,
											$request_data->twitter_id,
											$request_data->facebook_id,
											$request_data->google_id,
											$request_data->url,
											$request_data->instagram_id,
											$request_data->tumblr_id,
											$request_data->img_url,
											$request_data->img_file_path,
											$request_data->area,
											$request_data->is_active);

			if($fnHash['ok'])
			{   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else
			{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "PUT" 
				&& (User::isAdmin($_SESSION['user_type']) 
					|| User::isArtist($_SESSION['user_type'])))
		{
			$logger->writeLog("Updating: " . $request_data->name);
			$fnHash = ArtistDAO::updateArtist(User::isAdmin($_SESSION['user_type']),
											  $request_data->id,
											  $request_data->name,
											  $request_data->bio,
											  $request_data->twitter_id,
											  $request_data->facebook_id,
											  $request_data->google_id,
											  $request_data->url,
											  $request_data->instagram_id,
											  $request_data->tumblr_id,
											  $request_data->img_url,
											  $request_data->img_file_path,
											  $request_data->area,
											  $request_data->is_active);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "DELETE" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Deleting: " . $_GET['artist']);
			$fnHash = ArtistDAO::deleteArtist($_GET['artist']);
			$logger->writeLog("fnHash: " . json_encode($fnHash));

			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Artist Request");
		}
	}
	elseif(isset($_GET['video']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			$video_id  = $_GET['video'];
			$artist_id = $_GET['artistId'];
			$fnHash    = VideoDAO::getVideos($video_id, $artist_id);

			$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
			
			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['result']);
			}			
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "POST" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Inserting: " . $request_data->name);
			$fnHash = VideoDAO::insertVideo($request_data->artist_id,
											$request_data->session_id,
											$request_data->name,
											$request_data->description,
											$request_data->youtube_id,
											$request_data->iframe_url,
											$request_data->votes,
											$request_data->upload_status_nbr,
											$request_data->video_status_nbr);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "PUT" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Updating: " . $request_data->name);
			$fnHash = VideoDAO::updateVideo($request_data->id,
											  $request_data->artist_id,
											  $request_data->session_id,
											  $request_data->name,
											  $request_data->description,
											  $request_data->youtube_id,
											  $request_data->iframe_url,
											  $request_data->votes,
											  $request_data->upload_status_nbr,
											  $request_data->video_status_nbr);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "DELETE" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Deleting: " . $_GET['video']);
			$fnHash = VideoDAO::deleteVideo($_GET['video']);

			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['session']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			$session_id  = $_GET['session'];
			$active_only = $_GET['activeOnly'];

			$fnHash = SessionDAO::getSessions($session_id, $active_only);

			$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
			
			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['result']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "POST" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Inserting: " . $request_data->name);
			$fnHash = SessionDAO::insertSession($request_data->name,
												$request_data->description,
												$request_data->start_ts,
												$request_data->end_ts,
												$request_data->winner_video_id,
												$request_data->area,
												$request_data->is_active,
												$request_data->img_url);

			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "PUT" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Updating: " . $request_data->name);
			$fnHash = SessionDAO::updateSession($request_data->id,
											  $request_data->name,
											  $request_data->description,
											  $request_data->start_ts,
											  $request_data->end_ts,
											  $request_data->winner_video_id,
											  $request_data->area,
											  $request_data->is_active,
											  $request_data->img_url);

			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "DELETE" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Deleting: " . $_GET['session']);
			$fnHash = SessionDAO::deleteSession($_GET['session']);

			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		//invalid get req
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['user']) && (User::isAdmin($_SESSION['user_type']) || $_SESSION['user'] == $_GET['user']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			$user_id = $_GET['user'];
			$fnHash  = UserDAO::getUsers($user_id);

			$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
			
			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['result']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "POST" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Inserting: " . $request_data->email);
			$fnHash = UserDAO::insertUser($request_data->email,
										  $request_data->password,
										  $request_data->first_name,
										  $request_data->last_name,
										  $request_data->phone_nbr,
										  $request_data->facebook_id,
										  $request_data->twitter_id,
										  $request_data->google_id,
										  $request_data->tumblr_id,
										  $request_data->instagram_id,
										  $request_data->img_url,
										  $request_data->img_file_path);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "PUT" && User::isAdmin($_SESSION['user_type'])){
			$logger->writeLog("Updating: " . $request_data->id);
			$fnHash = UserDAO::updateUser($request_data->id,
											$request_data->email,
											$request_data->first_name,
											$request_data->last_name,
											$request_data->phone_nbr,
											$request_data->facebook_id,
											$request_data->twitter_id,
											$request_data->google_id,
											$request_data->tumblr_id,
											$request_data->instagram_id,
											$request_data->img_url,
											$request_data->img_file_path);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "DELETE" && User::isAdmin($_SESSION['user_type'])){
			$user_id = $_GET['user'];
			$fnHash  = UserDAO::deleteUser($user_id);

			$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
			
			if($fnHash['ok']){
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		//invalid get req
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['vote']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			if(isset($_GET['uservote'])){

				$fnHash  = VoteDAO::getSessionUserVote($_GET['session_id'], $_SESSION['user']);

				$logger->writeLog("fnHash['ok'] = " . json_encode($fnHash));
				
				if($fnHash['ok']){
					JsonResponse::sendResponse(200, $fnHash['result']);
				}
				else{
					JsonResponse::sendResponse(400, $fnHash['reason']);
				}
			}
			elseif(isset($_GET['videovotes'])){
				$fnHash = VoteDAO::getSessionVideoVoteCounts($_GET['session_id']);

				$logger->writeLog("fnHash['ok'] = " . $fnHash['ok']);
				
				if($fnHash['ok']){
					JsonResponse::sendResponse(200, $fnHash['result']);
				}
				else{
					JsonResponse::sendResponse(400, $fnHash['reason']);
				}
			}
			else{
				JsonResponse::sendResponse(404, "Invalid Spire API Request");
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] === "POST"){
			$logger->writeLog("Inserting: ".$TOKEN_DATA['session_id']."-".$_SESSION['user']."-".$request_data->video_id);
			$fnHash = VoteDAO::insertOrUpdateVote($request_data->session_id,
										  		  $_SESSION['user'],
										  		  $request_data->video_id);

			if($fnHash['ok']){   
				JsonResponse::sendResponse(200, $fnHash['reason']);
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		//invalid get req
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['artistsignup']))
	{
		if($_SERVER['REQUEST_METHOD'] === "POST" && isset($_SESSION['user'])){
			$logger->writeLog("Updating: " . $request_data->name);
			$fnHash = ArtistDAO::insertArtist($request_data->name,
											  $request_data->bio,
											  $request_data->twitter_id,
											  $request_data->facebook_id,
											  $request_data->google_id,
											  $request_data->url,
											  $request_data->instagram_id,
											  $request_data->tumblr_id,
											  $request_data->img_url,
											  $request_data->img_file_path,
											  $request_data->area,
											  0);

			if($fnHash['ok']){   
				//get created artist
				$fnHash2 = ArtistDAO::getCreatedArtistId($request_data->name);				
				if($fnHash2['ok']){
					//link user to artist
					$fnHash3 = ArtistDAO::createUserToArtistLink($fnHash2['result'], $_SESSION['user']);
					//send email to requesting user
					//send email to admin
					if($fnHash3['ok']){
						$userTypeHash = UserDAO::setUserAsArtistType($_SESSION['user']);
						if($userTypeHash['ok']){
							$userFnHash = UserDAO::getUsers($_SESSION['user']);
							if($userFnHash['ok']){
								$toUser = mail($userFnHash['result'][0]['email'], 
												'New SpireArtist Request', 
												'Thank you for your request! We will be contacting you soon.');
								$toAdmin = mail('admin@spiresessions.com', 
												'New SpireArtist Request', 
												'Request from user '.$userFnHash['result'][0]['email'].' for new artist '.$fnHash2['result'].'-'.$request_data->name);
								if($toUser && $toAdmin){
									JsonResponse::sendResponse(200, 'Email Sent to '.$userFnHash['result'][0]['email']);
								}
								else{
									if(!$toUser)
										JsonResponse::sendResponse(400, 'Email Not Sent to '.$userFnHash['result'][0]['email']);
									if(!$toAdmin)
										JsonResponse::sendResponse(400, 'Email Not Sent to admin');
								}
							}
							else{
								JsonResponse::sendResponse(400, $userFnHash['reason']);
							}
						}
						else{
							JsonResponse::sendResponse(400, $userTypeHash['reason']);
						}
					}
					else{
						JsonResponse::sendResponse(400, $fnHash3['reason'].'3');
					}
				}
				else{
					JsonResponse::sendResponse(400, $fnHash2['reason'].'2');
				}
			}
			else{
				JsonResponse::sendResponse(400, $fnHash['reason']);
			}
		}
		//invalid get req
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['winningVideoBySession']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			$session_id = MyUtil::parseInt($_GET['winningVideoBySession']);
			if($session_id != null){
				$fnOk = SessionDAO::getWinningVideoId($session_id);

				if($fnOk['ok']){
					$logger->writeLog("Getting winning vid for session: $session_id/" . $_GET['winningVideoBySession']);
					JsonResponse::sendResponse(200, $fnOk['result']);
				}
				else{
					$logger->writeLog("Failed Getting winning vid for session: $session_id/" . $_GET['winningVideoBySession']);
					JsonResponse::sendResponse(400, $fnOk['reason']);
				}
			}
			else{
				JsonResponse::sendResponse(400, "Invalid session_id syntax. must be int: ".$_GET['winningVideoBySession']);
			}
		}
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}		
	}
	elseif(isset($_GET['userartist']))
	{
		if($_SERVER['REQUEST_METHOD'] === "GET"){
			$user_id    = $_SESSION['user'];
			$logger->writeLog("User Id: $user_id");
			$artistHash = ArtistDAO::getArtistFromUser($user_id);

			if($artistHash['ok']){
				JsonResponse::sendResponse(200, $artistHash['result']);
			}
			else{
				JsonResponse::sendResponse(400, $artistHash['reason']);
			}
		}
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	elseif(isset($_GET['videoartist']))
	{
		if($_SERVER['REQUEST_METHOD'] == "POST")
		{
			//check if token user has artist rights
			$video       = $_POST['fields'];
			$target_dir  = "/home1/enderrac/public_html/spiresessions/video-uploads/";
			$target_file = $target_dir . basename($video['artist_id']."-".$video['name']."-".time()."-".$_FILES["videoToUpload"]["name"]);
			$fileType    = pathinfo($target_file,PATHINFO_EXTENSION);
			if(move_uploaded_file($_FILES["videoToUpload"]["tmp_name"], $target_file))
			{
				$fnHash = VideoDAO::insertVideo(MyUtil::get_hash_value($video, 'artist_id'),
												MyUtil::get_hash_value($video, 'session_id'),
												MyUtil::get_hash_value($video, 'name'),
												MyUtil::get_hash_value($video, 'description'),
												MyUtil::get_hash_value($video, 'youtube_id'),
												MyUtil::get_hash_value($video, 'iframe_url'),
												MyUtil::get_hash_value($video, 'votes'),
												MyUtil::get_hash_value($video, 'upload_status_nbr'),
												MyUtil::get_hash_value($video, 'video_status_nbr'),
												$target_file,
												MyUtil::get_hash_value($video, 'is_active'));
				if($fnHash['ok']){
					JsonResponse::sendResponse(200, "Successfully Created");
				}
				else{
					JsonResponse::sendResponse(500, $fnHash['reason']);
				}
				
			}
		}
		elseif($_SERVER['REQUEST_METHOD'] == "PUT")
		{

		}
		else{
			JsonResponse::sendResponse(404, "Invalid Spire API Request");
		}
	}
	else
	{
		JsonResponse::sendResponse(404, "Invalid Spire API Request");
	}
}

?>