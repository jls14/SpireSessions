<?php
//ArtistDAO.php
class SessionDAO
{
	private static $table_name = 'spire.SESSIONS';

	public static function getSessions(/*int*/$id, /*bool*/$active_only){
		if(!is_integer($id) || $id < 0){
			$id = 0;
		}
		if($active_only && $active_only != 'undefined'){
			$active_only = 'ACTIVE';
		}
		else{
			$active_only = 'ALL';
		}

		$stm = " SELECT id, name, description, "
			  ."		to_char(start_ts, 'yyyy-mm-dd') as start_ts, "
			  ."		to_char(end_ts, 'yyyy-mm-dd' ) as end_ts, "
			  ."		winner_video_id, area,  is_active, "
			  ."		to_char(updated_ts, 'YYYY-MM-DD HH24:MI:SS') as updated_ts, " 
			  ."		updated_by,	img_url "
			  ." FROM ".self::$table_name." "
			  ." WHERE id = $id OR 0 = $id "
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
			return MyUtil::fnOk(true, "Sessions Found", $retArray);
		}
		else{
			return MyUtil::fnOk(false, "No Results", $retArray);
		}		
	}

	public static function getWinningVideoId(/*int*/$id){
		$logger = new FileWriter('win_id_log', 'a');
		if(!is_integer($id) || $id <= 0){
			$fnOk = MyUtil::fnOk(false, "Invalid Session ID: $id".!is_integer($id)."-".($id <= 0), null);
			$logger->writeLog($fnOk['reason']);
			return $fnOk;
		}
		$stm = "SELECT COALESCE(winner_video_id, 0) as winner_video_id FROM ".self::$table_name." WHERE id = $id";

		$logger->writeLog($stm);
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$row = pg_fetch_assoc($result);
		
		if($row['winner_video_id'] > 0){
			return MyUtil::fnOk(true, "Video Id Found", $row['winner_video_id']);
		}
		else{
			$stm = " SELECT COALESCE(id, 0) as winner_video_id "
				  ." FROM spire.videos "
				  ." WHERE session_id = $id "
				  ."  AND votes = (SELECT MAX(votes) FROM spire.videos WHERE session_id = $id)";
			$logger->writeLog($stm);
			$result = ConnDB::query_db($stm);

			if(!$result){
				return MyUtil::fnOk(false, "SQL Error", null);
			}

			$row = pg_fetch_assoc($result);
			if($row['winner_video_id'] > 0){
				return MyUtil::fnOk(true, "Video Id Found", $row['winner_video_id']);
			}
			else{
				return MyUtil::fnOk(false, "Video Id Not Found", null);
			}
		}
	}

	public static function updateSession(/*int*/   $id,
										 /*string*/$name,
										 /*string*/$description,
										 /*string*/$start_ts,
										 /*string*/$end_ts,
										 /*int*/   $winner_video_id,
										 /*int*/   $area,
										 /*bool*/  $is_active,
										 /*string*/$img_url)
	{
		//id must be an integer
		$orig_id = $id;
		$id 	 = MyUtil::parseInt($id);
		if($id == null){
			return MyUtil::fnOk(false, "Must be a valid id: $orig_id", null);
		}
		else if(!is_string($name) || strlen($name) <= 0){
			return MyUtil::fnOk(false, "Name must be at least 1 character", null);
		}
		else if(!is_string($start_ts) || !MyUtil::isDateFormat($start_ts)){
			return MyUtil::fnOk(false, "start_ts must be in yyyy-mm-dd format: $start_ts", null);
		}
		else if(!is_string($end_ts) || !MyUtil::isDateFormat($end_ts)){
			return MyUtil::fnOk(false, "end_ts must be in yyyy-mm-dd format: $end_ts", null);
		}

		$stm = "UPDATE ".self::$table_name." SET "
			  ." name 		     = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ." description     = nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($description,""))."', ''), "
			  ." start_ts 	     = to_timestamp('$start_ts','yyyy-mm-dd'), "
			  ." end_ts          = to_timestamp('$end_ts','yyyy-mm-dd'), "
			  ." is_active       = ('$is_active' = '1'),"
			  ." winner_video_id = nullIf(".MyUtil::nvl($winner_video_id, -1).",-1), "
			  ." area            = nullIf('".MyUtil::nvl($area,'')."',''), " 
			  ." img_url         = '$img_url', "
			  ." updated_ts      = CURRENT_TIMESTAMP, "
			  ." updated_by      = '".$_SESSION['user']."' " 
			  ."WHERE id = $id";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Session Updated", $result);
	}

	public static function insertSession(/*string*/$name,
										 /*string*/$description,
										 /*int*/   $start_ts,
										 /*int*/   $end_ts,
										 /*int*/   $winner_video_id,
										 /*int*/   $area,
										 /*bool*/  $is_active,
										 /*string*/$img_url)
	{
		if(!is_string($name) || strlen($name) <= 0){
			return MyUtil::fnOk(false, "Name must be at least 1 character", null);
		}
		else if(!is_string($start_ts) || !MyUtil::isDateFormat($start_ts)){
			return MyUtil::fnOk(false, "start_ts must be in yyyy-mm-dd format: $start_ts", null);
		}
		else if(!is_string($end_ts) || !MyUtil::isDateFormat($end_ts)){
			return MyUtil::fnOk(false, "end_ts must be in yyyy-mm-dd format: $end_ts", null);
		}

		$stm = "INSERT INTO ".self::$table_name."(name, description, start_ts, end_ts, area, updated_ts, updated_by, is_active, img_url) VALUES ("
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($name,""))."', ''), "
			  ."nullIf('".MyUtil::prepareSqlString(MyUtil::nvl($description,""))."', ''), "
			  ."to_timestamp('$start_ts','yyyy-mm-dd'), "
			  ."to_timestamp('$end_ts','yyyy-mm-dd'), "
			  ."nullIf('".MyUtil::nvl($area, '')."',''), "
			  ."CURRENT_TIMESTAMP, "
			  ."'".$_SESSION['user']."', "
			  ."('$is_active' = '1'), "
			  ."'$img_url'"
			  .")";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		return  MyUtil::fnOk(true, "Session Inserted", $result);
	}

	public static function deleteSession(/*int*/$id)
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

		return MyUtil::fnOk(true, "Session Deleted", $result);		
	}
}
?>