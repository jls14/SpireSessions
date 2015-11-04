<?php
//ArtistDAO.php
class UserDAO
{
	private static $artist_type = 1;
	private static $voter_type  = 0;

	private static $table_name = 'spire.USERS';

	public static function getUsers(/*int*/$id){
		$id = MyUtil::parseInt($id);
		if(!$id){
			$id = 0;
		}
		$stm = "SELECT id, "  
				." creation_dt,"
				." email,"
				." facebook_id,"
				." first_name,"
				." google_id,"
				." img_file_path,"
				." img_url,"
				." instagram_id,"
				." last_name,"
				." phone_nbr,"
				." tumblr_id,"
				." twitter_id,"
				." updated_ts,"
				." user_type_nbr,"
				." username " 
			 ." FROM ".self::$table_name
			 ." WHERE id = $id OR 0 = $id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, pg_error(), null);
		}

		$retArray = [];
		while($row = pg_fetch_assoc($result)){
			array_push($retArray, $row);
		}

		if(sizeof($retArray) > 0){
			return MyUtil::fnOk(true, "Found Users", $retArray);
		}
		else{
			return MyUtil::fnOk(false, "No Results", $retArray);
		}
	}

	public static function getUserId($col, $val)
	{
		if($col != "FACEBOOK_ID" && $col != "USERNAME" && $col != "EMAIL")
		{
			return MyUtil::fnOk(false, "Incorrect Column: $col", null);
		}

		$stm    = "SELECT ID FROM ".self::$table_name." WHERE $col = '$val'";
		$result	= ConnDB::query_db($stm);
		if(!$result)
		{
			return MyUtil::fnOk(false, "SQL Error getting user id");
		}

		$row = pg_fetch_assoc($result);
		if(isset($row['id']) && $row['id'] > 0)
		{
			return MyUtil::fnOk(true, "ID Found", $row['id']);
		}
		return MyUtil::fnOk(true, "ID Not Found", 0);
	}

	public static function setUserAsArtistType($id)
	{
		$id = MyUtil::parseInt($id);
		if(!$id){
			return MyUtil::fnOk(false, "Must be a valid id: $id", null);
		}	

		$stm = "UPDATE ".self::$table_name." SET "
			  ." user_type_nbr = ".self::$artist_type
			  ."WHERE ID = $id";
			  
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "User Updated", $result);
	}

	public static function updateUser(/*int*/   $id,
										/*string*/$email,
										/*string*/$first_name,
										/*string*/$last_name,
										/*string*/$phone_nbr,
										/*string*/$facebook_id,
										/*string*/$twitter_id,
										/*string*/$google_id,
										/*string*/$tumblr_id,
										/*string*/$instagram_id,
										/*string*/$img_url,
										/*string*/$img_file_path)
	{
		//id must be an integer
		$id = MyUtil::parseInt($id);
		if(!$id){
			return MyUtil::fnOk(false, "Must be a valid id: $id", null);
		}	

		$stm = "UPDATE ".self::$table_name." SET "
			  ."email 		  = nullIf('".MyUtil::nvl($email,"")."', ''), "
			  ."first_name	  = nullIf('".MyUtil::nvl($first_name,"")."', ''), "
			  ."last_name 	  = nullIf('".MyUtil::nvl($last_name,"")."', ''), "
			  ."phone_nbr     = nullIf('".MyUtil::nvl($phone_nbr,"")."', ''), "
			  ."facebook_id	  = nullIf('".MyUtil::nvl($facebook_id,"")."', ''), "
			  ."twitter_id    = nullIf('".MyUtil::nvl($twitter_id,"")."', ''), "
			  ."instagram_id  = nullIf('".MyUtil::nvl($instagram_id,"")."', ''), "
			  ."tumblr_id 	  = nullIf('".MyUtil::nvl($tumblr_id,"")."', ''), "
			  ."img_url 	  = nullIf('".MyUtil::nvl($img_url,"")."', ''), "
			  ."img_file_path = nullIf('".MyUtil::nvl($img_file_path,"")."', ''), "
			  ."updated_ts    = NOW() "
			  ."WHERE id = $id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "User Updated", $result);
	}

	public static function insertUser(/*string*/$email,
										/*string*/$password,
										/*string*/$first_name,
										/*string*/$last_name,
										/*string*/$phone_nbr,
										/*string*/$facebook_id,
										/*string*/$twitter_id,
										/*string*/$google_id,
										/*string*/$tumblr_id,
										/*string*/$instagram_id,
										/*string*/$img_url,
										/*string*/$img_file_path)
	{
		if((!is_string($email) || strlen($email) <= 0)
				&& (!is_string($facebook_id) || strlen($facebook_id) <= 0)) {
			return MyUtil::fnOk(false, "email or facebook id must be valid - email:$email, fb:$facebook_ids", null);
		}

		$stm = "INSERT INTO ".self::$table_name."(email,password,first_name,last_name,phone_nbr,facebook_id,".
												"twitter_id,google_id,tumblr_id,instagram_id,img_url,img_file_path,updated_ts) VALUES ("
			  ."nullIf('".MyUtil::nvl($email,"")."', ''), "
			  ."nullIf('".MyUtil::nvl(MyUtil::myCrypt($password),"")."', ''), "
			  ."nullIf('".MyUtil::nvl($first_name,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($last_name,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($phone_nbr,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($facebook_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($twitter_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($google_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($tumblr_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($instagram_id,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($img_url,"")."', ''), "
			  ."nullIf('".MyUtil::nvl($img_file_path,"")."', ''), "
			  ."CURRENT_TIMESTAMP "
			  .")";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "User Inserted", $result);
	}

	public static function deleteUser(/*int*/$id)
	{
		//id must be an integer
		$id = MyUtil::parseInt($id);
		if(!$id){
			return MyUtil::fnOk(false, "Must be a valid id: $id", null);
		}		

		$stm = "DELETE FROM ".self::$table_name." WHERE id = $id";
		
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "Deleted User", $result);		
	}

	public static function getArtistIdFromUser(/*int*/$id)
	{
		//id must be an integer
		$id = MyUtil::parseInt($id);
		if(!$id){
			return MyUtil::fnOk(false, "Must be a valid id: $id", null);
		}	

		$stm = "SELECT artist_id from spire.user_to_artist where user_id = $id ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return MyUtil::fnOk(true, "Found Artist Id", $result['artist_id']);
	}
}
?>