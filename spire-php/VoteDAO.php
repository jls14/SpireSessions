<?php
//ArtistDAO.php
class VoteDAO
{
	private static $table_name = 'spire.VOTES';

	public static function getSessionVideoVoteCounts(/*int*/ $session_id)
	{
		$orig_id    = $session_id;
		$session_id = MyUtil::parseInt($session_id);
		if($session_id == null){
			return MyUtil::fnOk(false, "Must be a valid session id: $orig_id", null);
		}

		$stm = "SELECT video_id, COUNT(*) as count FROM ".self::$table_name." "
			  ."WHERE session_id = $session_id "
			  ."GROUP BY video_id ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$resultHash = [];
		while($row = pg_fetch_assoc($result)){
			$resultHash[$row['video_id']] = $row['count']; 
		}

		return MyUtil::fnOk(true, "", $resultHash);
	}

	public static function getSessionUserVote(/*int*/$session_id, /*int*/$user_id)
	{
		$orig_id    = $session_id;
		$session_id = MyUtil::parseInt($session_id);
		if($session_id == null){
			return MyUtil::fnOk(false, "Must be a valid session id: $orig_id", null);
		}
		$orig_user_id    = $user_id;
		$user_id = MyUtil::parseInt($user_id);
		if($user_id == null){
			return MyUtil::fnOk(false, "Must be a valid user id: $user_id", null);
		}

		$stm = "SELECT video_id FROM ".self::$table_name." "
			  ."WHERE session_id = $session_id "
			  ." AND user_id = $user_id "
			  ." AND insert_dt = CURRENT_DATE ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$row = pg_fetch_assoc($result);

		$vid_id = $row['video_id'];
		if(!($vid_id>0)){
			$vid_id=null;
		}

		return MyUtil::fnOk(true, "", $vid_id);
	}

	public static function updateVote(/*int*/ $session_id,
									  /*int*/ $user_id,
									  /*int*/ $video_id)
	{
		//id must be an integer
		$orig_sess_id  = $session_id;
		$orig_user_id  = $user_id;
		$orig_video_id = $video_id;
		//get parsed integers
		$session_id = MyUtil::parseInt($session_id);
		$video_id   = MyUtil::parseInt($video_id);
		$user_id    = MyUtil::parseInt($user_id);
		//check
		if($session_id == null || $video_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be valid ids - session:$orig_sess_id, video:$orig_video_id, user:$orig_user_id", null);
		}	

		$stm = "UPDATE ".self::$table_name." SET "
			  ."video_id      = nullIf(".MyUtil::nvl($video_id,-1).", -1), "
			  ."updated_ts    = NOW() "
			  ."WHERE session_id = $session_id " 
			  ." AND user_id   = $user_id "
			  ." AND insert_dt = CURRENT_DATE ";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, pg_error(), null);
		}

		return  MyUtil::fnOk(true, "", $result);
	}

	public static function insertVote(/*int*/ $session_id,
									  /*int*/ $user_id,
									  /*int*/ $video_id)
	{
		//id must be an integer
		$orig_sess_id  = $session_id;
		$orig_user_id  = $user_id;
		$orig_video_id = $video_id;
		//get parsed integers
		$session_id = MyUtil::parseInt($session_id);
		$video_id   = MyUtil::parseInt($video_id);
		$user_id    = MyUtil::parseInt($user_id);
		//check
		if($session_id == null || $video_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be valid ids - session:$orig_sess_id, video:$orig_video_id, user:$orig_user_id", null);
		}


		$stm = "INSERT INTO ".self::$table_name."(session_id, user_id, video_id, updated_ts) VALUES ("
			  ."nullIf(".MyUtil::nvl($session_id,-1).", -1), "
			  ."nullIf(".MyUtil::nvl($user_id,-1).", -1), "
			  ."nullIf(".MyUtil::nvl($video_id,-1).", -1), "
			  ."CURRENT_TIMESTAMP "
			  .")";

		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, pg_error(), null);
		}

		return  MyUtil::fnOk(true, "", $result);
	}

	public static function insertOrUpdateVote(/*int*/ $session_id,
									  /*int*/ $user_id,
									  /*int*/ $video_id)
	{
		//id must be an integer
		$orig_sess_id  = $session_id;
		$orig_user_id  = $user_id;
		$orig_video_id = $video_id;
		//get parsed integers
		$session_id = MyUtil::parseInt($session_id);
		$video_id   = MyUtil::parseInt($video_id);
		$user_id    = MyUtil::parseInt($user_id);
		//check
		if($session_id == null || $video_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be valid ids - session:$orig_sess_id, video:$orig_video_id, user:$orig_user_id", null);
		}
		
		$hasVotedResult = self::hasUserVoted($user_id, $session_id);
		if($hasVotedResult['ok'])
		{
			if($hasVotedResult['result']){
				return self::updateVote($session_id, $user_id, $video_id);
			}
			return self::insertVote($session_id, $user_id, $video_id);
		}
		return MyUtil::fnOk(false, "SQL Error", null);
	}

	public static function deleteVote(/*int*/ $session_id,
									  /*int*/ $user_id)
	{
		//id must be an integer
		$orig_sess_id  = $session_id;
		$orig_user_id  = $user_id;
		//get parsed integers
		$session_id = MyUtil::parseInt($session_id);
		$user_id    = MyUtil::parseInt($user_id);
		//check
		if($session_id == null || $user_id == null){
			return MyUtil::fnOk(false, "Must be valid ids - session:$orig_sess_id, user:$orig_user_id", null);
		}		

		$stm = "DELETE FROM ".self::$table_name." WHERE session_id = $session_id AND user_id = $user_id";
		
		$result = ConnDB::query_db($stm);

		if(!$result){
			return MyUtil::fnOk(false, pg_error(), null);
		}

		return MyUtil::fnOk(true, "", $result);		
	}

	private static function hasUserVoted($user, $session)
	{
		$stm = "SELECT COUNT(*) as is_vote FROM ".self::$table_name." WHERE SESSION_ID = $session AND USER_ID = $user AND INSERT_DT = CURRENT_DATE ";
		$result = ConnDB::query_db($stm);
		if(!$result){
			return MyUtil::fnOk(false, "SQL Error", null);
		}

		$row = pg_fetch_assoc($result);
		return MyUtil::fnOk(true, "", $row['is_vote']==1);
	}
}
?>