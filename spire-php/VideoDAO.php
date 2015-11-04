<?php
//ArtistDAO.php
class VideoDAO
{
	private static $table_name = 'spire.VIDEOS';

	public static function getVideos(/*int*/$id, /*int*/$artist_id){
		if(!MyUtil::isInteger($id) || $id < 0){
			$id = 0;
		}
		if(!MyUtil::isInteger($artist_id) || $artist_id < 0){
			$artist_id = 0;
		}

		$stm = " SELECT id, artist_id, session_id, name, description, 
						youtube_id, iframe_url, votes, upload_status_nbr, 
						video_status_nbr, regexp_replace(file, '/home1/enderrac/public_html/spiresessions/', 'http://www.spiresessions.com/') as file, is_active FROM ".self::$table_name
			  ." WHERE (id = $id OR 0 = $id) "
			  ."  AND (artist_id = $artist_id OR 0 = $artist_id)";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$retArray = [];
		while($row = pg_fetch_assoc($result)){
			array_push($retArray, $row);
		}
				
		if(sizeof($retArray) > 0){
			return MyUtil::fnOk(true, "Videos Found", $retArray);
		}
		else{
			return MyUtil::fnOk(false, "No Results", $retArray);
		}
	}

	public static function updateVideo(/*int*/    $id,
									   /*int*/    $artist_id,
									   /*int*/	  $session_id,
									   /*string*/ $name,
									   /*string*/ $description,
									   /*string*/ $youtube_id,
									   /*string*/ $iframe_url,
									   /*int*/	  $votes,
									   /*int*/	  $upload_status_nbr,
									   /*int*/	  $video_status_nbr,
									   /*string*/ $file)
	{
		//id must be an integer
		$orig_id = $id;
		$id = MyUtil::parseInt($id);
		if($id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}	
		$orig_artist_id = $artist_id;
		$artist_id 		= MyUtil::parseInt($artist_id);
		if($artist_id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_artist_id", null);
		}
		$orig_session_id = $session_id;
		$session_id      = MyUtil::parseInt($session_id);
		if($session_id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_session_id", null);
		}
		if($file == null){
			$file = 'NULL';
		}

		$stm = "UPDATE ".self::$table_name." SET "
			  ."	artist_id		  = nullIf('".MyUtil::nvl($artist_id,-1)."', -1), "
			  ."	session_id		  = nullIf('".MyUtil::nvl($session_id,-1)."', -1), "
			  ."	name 	 		  = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ."	description 	  = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($description,""))."', ''), "
			  ."	youtube_id 		  = nullIf('".MyUtil::nvl($youtube_id,"")."', ''), "
			  ."	iframe_url		  = nullIf('".MyUtil::nvl($iframe_url,"")."', ''), "
			  ."	votes             = nullIf('".MyUtil::nvl($votes,0)."', -1), "
			  ."	upload_status_nbr = nullIf('".MyUtil::nvl($upload_status_nbr,0)."', -1), "
			  ."	video_status_nbr  = nullIf('".MyUtil::nvl($video_status_nbr,0)."', -1), "
			  ."	updated_ts  	  = NOW(), "
			  ."	updated_by        = '".$_SESSION['user']."', "
			  ."	file              = nullIf('".MyUtil::nvl($file,"")."', '') " 
			  ."WHERE id = $id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Session Updated", $result);
	}

	public static function insertVideo(/*int*/    $artist_id,
										/*int*/	  $session_id,
										/*string*/$name,
										/*string*/$description,
										/*string*/$youtube_id,
										/*string*/$iframe_url,
										/*int*/	  $votes,
										/*int*/	  $upload_status_nbr,
										/*int*/	  $video_status_nbr,
										/*String*/$file,
										/*bool*/  $is_active)
	{
		if(!is_string($name) || strlen($name) <= 0){
			return MyUtil::fnOk(false, "Name must be at least 1 character", null);
		}
		$orig_artist_id = $artist_id;
		$artist_id 		= MyUtil::parseInt($artist_id);
		if($artist_id == null){
			return MyUtil::fnOk(false, "Must be a valid artist id: $orig_artist_id", null);
		}
		$orig_session_id = $session_id;
		$session_id      = MyUtil::parseInt($session_id);
		if($session_id == null){
			return MyUtil::fnOk(false, "Must be a valid session id: $orig_session_id", null);
		}

		$stm = "INSERT INTO ".self::$table_name."(artist_id,session_id,name,description,youtube_id,iframe_url,".
												"votes,upload_status_nbr,video_status_nbr,updated_ts,updated_by,file,is_active) VALUES ( "
			  ."nullIf('".MyUtil::nvl($artist_id,-1)."', -1), "
			  ."nullIf('".MyUtil::nvl($session_id,-1)."', -1), "
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($description,""))."', ''), "
			  ."nullIf('".MyUtil::nvl($youtube_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($iframe_url,"")."', ''), "
			  ."nullIf('".MyUtil::nvl(MyUtil::null_if($votes, ''),0)."', -1), "
			  ."nullIf('".MyUtil::nvl($upload_status_nbr,0)."', -1), "
			  ."nullIf('".MyUtil::nvl($video_status_nbr,0)."', -1), "
			  ."CURRENT_TIMESTAMP, "
			  ."'".$_SESSION['user']."', "
			  ."nullIf('".MyUtil::nvl($file, "")."', ''), "
			  ."'".MyUtil::nvl($is_active, "false")."' "
			  .")";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Inserted Video", $result);
	}

	public static function deleteVideo(/*int*/$id)
	{
		//id must be an integer
		$orig_id = $id;
		$id = MyUtil::parseInt($id);
		if($id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}		

		$stm = "DELETE FROM ".self::$table_name." WHERE id = $id";
		
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "Deleted Video", $result);		
	}
}
?>