<?php
//ArtistDAO.php
class ArtistDAO
{
	private static $table_name = 'spire.ARTISTS';

	public static function getArtists(/*int*/$id, /*bool*/$active_only){
		if(!is_integer($id) || $id < 0){
			$id = 0;
		}
		
		if($active_only){
			$active_only = 'ACTIVE';
		}
		else{
			$active_only = 'ALL';
		}
		
		$stm = " SELECT * FROM ".self::$table_name
			  ." WHERE (id = $id OR 0 = $id) "
			  ."  AND (is_active OR '$active_only' = 'ALL')";

		$result = ConnDB::query_db($stm);
		
		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$retArray = [];  
		while($row = pg_fetch_assoc($result)){
			array_push($retArray, $row);
		}
				
		if(sizeof($retArray) > 0){
			return MyUtil::fnOk(true, "Artists Found", $retArray);
		}
		else{
			return MyUtil::fnOk(false, "No Results", $retArray);
		}
	}

	public static function updateArtist(/*bool*/  $is_admin,
										/*int*/   $id,
										/*string*/$name,
										/*string*/$bio,
										/*string*/$twitter_id,
										/*string*/$facebook_id,
										/*string*/$google_id,
										/*string*/$url,
										/*string*/$instagram_id,
										/*string*/$tumblr_id,
										/*string*/$img_url,
										/*string*/$img_file_path,
										/*string*/$area,
										/*string*/$is_active)
	{
		//id must be an integer
		$orig_id = $id;
		$id = MyUtil::parseInt($id);
		if($id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}	

		$stm = "UPDATE ".self::$table_name." SET "
			  ."name 		  = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ."bio 		  = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($bio,""))."', ''), "
			  ."twitter_id 	  = nullIf('".MyUtil::nvl($twitter_id,"")."', ''), "
			  ."facebook_id   = nullIf('".MyUtil::nvl($facebook_id,"")."', ''), "
			  ."google_id 	  = nullIf('".MyUtil::nvl($google_id,"")."', ''), "
			  ."url 		  = nullIf('".MyUtil::nvl($url,"")."', ''), "
			  ."instagram_id  = nullIf('".MyUtil::nvl($instagram_id,"")."', ''), "
			  ."tumblr_id 	  = nullIf('".MyUtil::nvl($tumblr_id,"")."', ''), "
			  ."img_url 	  = nullIf('".MyUtil::nvl($img_url,"")."', ''), "
			  ."img_file_path = nullIf('".MyUtil::nvl($img_file_path,"")."', ''), "
			  ."updated_ts    = CURRENT_TIMESTAMP, "
			  ."area 		  = nullIf('".MyUtil::nvl($area, '')."',''), ";
		if($is_admin){
			$stm .= "is_active = ('$is_active' = '1'), ";
		}
			  
		$stm .="updated_by    = ".$_SESSION['user']." " 
			  ."WHERE id = $id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Artist Updated!", $result);
	}

	public static function insertArtist(/*string*/$name,
										/*string*/$bio,
										/*string*/$twitter_id,
										/*string*/$facebook_id,
										/*string*/$google_id,
										/*string*/$url,
										/*string*/$instagram_id,
										/*string*/$tumblr_id,
										/*string*/$img_url,
										/*string*/$img_file_path,
										/*string*/$area,
										/*bool*/  $is_active)
	{
		if(!is_string($name) || strlen($name) <= 0){
			return MyUtil::fnOk(false, "Name must be at least 1 character", null);
		}	

		$stm = "INSERT INTO ".self::$table_name."(is_active,name,bio,twitter_id,facebook_id,google_id,url,".
												"instagram_id,tumblr_id,img_url,img_file_path,updated_ts,updated_by, area) VALUES ("
			  ."('$is_active' = '1'), "
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($bio,""))."', ''), "
			  ."nullIf('".MyUtil::nvl($twitter_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($facebook_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($google_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($url,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($instagram_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($tumblr_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($img_url,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($img_file_path,"")."', ''), "
			  ."CURRENT_TIMESTAMP, "
			  ."'".$_SESSION['user']."', "
			  ."nullIf('".MyUtil::nvl($area, '')."','') "
			  .")";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Artist Inserted", $result);
	}

	public static function getCreatedArtistId($name)
	{
		$stm = "SELECT id as artist_id "
			  ."FROM ".self::$table_name." "
			  ."WHERE name = '$name' "
			  ." AND to_char(CURRENT_DATE, 'YYYY-MM-DD') = to_char(UPDATED_TS, 'YYYY-MM-DD') "
			  ." AND updated_by = ".$_SESSION['user'];

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error-".$stm, null);
		}

		$row = pg_fetch_assoc($result);

		return MyUtil::fnOk(true, "Last Artist Found", $row['artist_id']);
	}

	public static function deleteArtist(/*int*/$id)
	{
		//id must be an integer
		$orig_id = $id;
		$id = MyUtil::parseInt($id);

		if($id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}	
		
		$fnOk = self::deleteAllUserToArtistLinks($id);

		if($fnOk['ok']){
			$stm = "DELETE FROM ".self::$table_name." WHERE id = $id";
		
			$result = ConnDB::query_db($stm);

			if(!$result){
				return MyUtil::fnOk(false, "SQL Error", null);
			}

			return MyUtil::fnOk(true, "Artist Deleted", $result);		
		}
		else{
			return MyUtil::fnOk(false, $fnOk['reason'], null);
		}
	}


	public static function createUserToArtistLink(/*int*/$artist_id, /*int*/$user_id){
		//id must be an integer
		$orig_artist_id = $artist_id;
		$artist_id = MyUtil::parseInt($artist_id);

		$orig_user_id = $user_id;
		$user_id = MyUtil::parseInt($user_id);
		
		if($artist_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be a valid artist and user id: $orig_artist_id, $orig_user_id", null);
		}

		$stm = "INSERT INTO spire.USER_TO_ARTIST(USER_ID, ARTIST_ID, UPDATED_BY, UPDATED_TS) "
			  ." VALUES ($user_id, $artist_id, ".$_SESSION['user'].", CURRENT_TIMESTAMP)";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "Created User-Artist Link", $result);
	}

	private static function deleteAllUserToArtistLinks(/*int*/$artist_id)
	{
		$orig_artist_id = $artist_id;
		$artist_id = MyUtil::parseInt($artist_id);
		
		if($artist_id == null){
			return MyUtil::fnOk(false, "Must be a valid artist: $orig_artist_id", null);
		}

		$stm = "DELETE FROM spire.USER_TO_ARTIST "
			  ." WHERE ARTIST_ID = $artist_id ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "All Artist-User links deleted", $result);
	}

	public static function deleteUserToArtistLink(/*int*/$artist_id, /*int*/$user_id)
	{
		//id must be an integer
		$orig_artist_id = $artist_id;
		$artist_id = MyUtil::parseInt($artist_id);

		$orig_user_id = $user_id;
		$user_id = MyUtil::parseInt($user_id);
		
		if($artist_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be a valid artist and user id: $orig_artist_id, $orig_user_id", null);
		}

		$stm = "DELETE FROM spire.USER_TO_ARTIST "
			  ." WHERE USER_ID = $user_id "
			  ."  AND ARTIST_ID = $artist_id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "Artist-User Link Deleted", $result);
	}

	public static function getArtistFromUser(/*int*/$orig_id)
	{
		//id must be an integer
		$id = MyUtil::parseInt($orig_id);
		if(!$id){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}	

		$stm = " SELECT art.* "
			  ." FROM spire.user_to_artist ua "
			  ."    JOIN spire.artists art ON ua.artist_id = art.id "
			  ." WHERE ua.user_id = $id ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$retArray = [];  
		while($row = pg_fetch_assoc($result)){
			array_push($retArray, $row);
		}

		return MyUtil::fnOk(true, "Found Artist Id", $retArray);
	}
}

?>